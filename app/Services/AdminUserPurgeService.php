<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Remove the user's physical uploads without deleting shared assets or
 * preserving publicly accessible copies. User remains soft-deleted so job,
 * application, and financial histories are not cascade-destroyed.
 */
final class AdminUserPurgeService
{
    private const FILE_ROWS = [
        ['uploaded_cvs', 'file', 'assets/images/resume'],
        ['jobseeker_certificates', 'file', 'assets/images/certificate'],
        ['jobseeker_uploads', 'file', 'assets/images/document'],
        ['cover_letters', 'signature', 'assets/images/signature'],
        ['employer_information', 'photo', 'assets/images/profilephoto'],
        ['companies', 'photo', 'assets/images/profilephoto'],
    ];

    public function delete(User $target, User $actor): int
    {
        if ((int) $target->id === (int) $actor->id
            || in_array($target->user_type, ['admin', 'super_admin'], true)
            || $target->trashed()) {
            throw new RuntimeException('This account cannot be deleted.');
        }

        $staged = [];
        $quarantine = storage_path('app/admin-purge-'.Str::uuid());
        if (!mkdir($quarantine, 0700, true)) {
            throw new RuntimeException('Cannot create secure purge staging area.');
        }

        try {
            $count = DB::transaction(function () use ($target, $actor, &$staged, $quarantine) {
                $locked = User::whereKey($target->id)->lockForUpdate()->firstOrFail();
                if ($locked->trashed() || in_array($locked->user_type, ['admin', 'super_admin'], true)) {
                    throw new RuntimeException('This account cannot be deleted.');
                }

                $files = [];
                $photo = (string) ($locked->photo ?? '');
                $this->addLegacy($files, $photo, 'assets/images/userphoto', 'users', 'photo', $target->id);
                $this->addLegacy($files, $photo, 'assets/images/profilephoto', 'users', 'photo', $target->id);

                foreach (self::FILE_ROWS as [$table, $column, $folder]) {
                    if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) continue;
                    foreach (DB::table($table)->where('user_id', $target->id)->pluck($column) as $filename) {
                        $this->addLegacy($files, (string) $filename, $folder, $table, $column, $target->id);
                    }
                }

                // The private-document path always begins with this user's ID.
                if (Schema::hasTable('applicant_private_document_versions')) {
                    $versions = DB::table('applicant_private_document_versions')
                        ->where('user_id', $target->id)->get(['disk', 'path']);
                    foreach ($versions as $version) {
                        if ($version->disk !== 'local' ||
                            !is_string($version->path) ||
                            !preg_match('~^private-documents/'.preg_quote((string) $target->id, '~').'/[A-Za-z0-9._/-]+$~D', $version->path) ||
                            str_contains($version->path, '..')) {
                            throw new RuntimeException('Unsupported private document path; deletion stopped.');
                        }
                        $path = storage_path('app/'.$version->path);
                        $files[$path] = true;
                    }
                }

                foreach (array_keys($files) as $path) {
                    if (!file_exists($path)) continue;
                    if (is_link($path) || !is_file($path)) {
                        throw new RuntimeException('Unsafe file in account purge.');
                    }
                    $destination = $quarantine.'/'.count($staged);
                    if (!rename($path, $destination)) {
                        throw new RuntimeException('Could not securely stage account file.');
                    }
                    $staged[$path] = $destination;
                }

                // Delete only upload metadata; retain applications, jobs, and
                // support/financial records tied to the soft-deleted user ID.
                foreach (self::FILE_ROWS as [$table]) {
                    if (Schema::hasTable($table)) {
                        DB::table($table)->where('user_id', $target->id)->delete();
                    }
                }
                if (Schema::hasTable('applicant_private_documents')) {
                    $ids = DB::table('applicant_private_documents')->where('user_id', $target->id)->pluck('id');
                    if ($ids->isNotEmpty()) {
                        if (Schema::hasTable('applicant_private_document_events')) {
                            DB::table('applicant_private_document_events')->whereIn('document_id', $ids)->delete();
                        }
                        if (Schema::hasTable('applicant_private_document_versions')) {
                            DB::table('applicant_private_document_versions')->whereIn('document_id', $ids)->delete();
                        }
                        DB::table('applicant_private_documents')->whereIn('id', $ids)->delete();
                    }
                }
                if (Schema::hasTable('personal_access_tokens')) {
                    DB::table('personal_access_tokens')->where('tokenable_type', $target->getMorphClass())
                        ->where('tokenable_id', $target->id)->delete();
                }
                if (Schema::hasTable('password_reset_tokens')) {
                    DB::table('password_reset_tokens')->where('email', $target->email)->delete();
                }
                $locked->forceFill([
                    'status' => 0,
                    'remember_token' => Str::random(60),
                    'photo' => null,
                ])->save();
                $locked->delete();

                Log::notice('admin.user.deleted', [
                    'actor_id' => $actor->id,
                    'target_id' => $target->id,
                    'file_count' => count($staged),
                    'mode' => 'soft_delete_plus_owned_file_purge',
                ]);
                return count($staged);
            });

            foreach ($staged as $destination) {
                if (!@unlink($destination)) {
                    Log::error('admin.user.file_purge_cleanup_failed', [
                        'actor_id' => $actor->id, 'target_id' => $target->id,
                    ]);
                    // Files are already moved outside the public tree.
                }
            }
            @rmdir($quarantine);
            return $count;
        } catch (Throwable $exception) {
            foreach (array_reverse($staged, true) as $original => $destination) {
                if (is_file($destination) && !rename($destination, $original)) {
                    Log::critical('admin.user.purge_rollback_failed', [
                        'target_id' => $target->id,
                    ]);
                }
            }
            @rmdir($quarantine);
            throw $exception;
        }
    }

    private function addLegacy(
        array &$files,
        string $filename,
        string $folder,
        string $table,
        string $column,
        int $owner
    ): void {
        if ($filename === '') return;
        // Never accept paths, URLs, traversal, control chars, or dot files
        // from old unvalidated upload records.
        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{0,200}$/D', $filename)
            || str_contains($filename, '..')) {
            throw new RuntimeException('Unrecognized upload filename; deletion stopped.');
        }
        if (DB::table($table)->where($column, $filename)
            ->where('user_id', '!=', $owner)->exists()
            && $table !== 'users') {
            throw new RuntimeException('File is shared with another account; deletion stopped.');
        }
        if ($table === 'users' &&
            DB::table('users')->where('id', '!=', $owner)
                ->where('photo', $filename)->exists()) {
            throw new RuntimeException('Shared account photo; deletion stopped.');
        }

        // Older releases wrote relative to Laravel's project root; newer
        // deployments may place the same file in public/. Check both.
        foreach ([base_path($folder), public_path($folder)] as $directory) {
            $base = realpath($directory);
            if (!$base || !is_dir($base)) continue;
            $path = $base.DIRECTORY_SEPARATOR.$filename;
            $files[$path] = true;
        }
    }
}
