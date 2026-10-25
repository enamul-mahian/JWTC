<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminUserPurgeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserActionsController extends Controller
{
    private function permitted(Request $request, User $target): User
    {
        $actor = $request->user();
        abort_unless($actor
            && in_array($actor->user_type, ['admin', 'super_admin'], true)
            && in_array($target->user_type, ['employer', 'job_seeker', 'staff'], true)
            && (int) $actor->id !== (int) $target->id, 403);
        return $actor;
    }

    public function show(Request $request, User $user): View
    {
        $this->permitted($request, $user);
        return view('admin.userManagement.account_actions', compact('user'));
    }

    public function setPassword(Request $request, User $user): RedirectResponse
    {
        $actor = $this->permitted($request, $user);
        $data = $request->validate([
            'admin_password' => ['required', 'string', 'current_password'],
            'password' => [
                'required', 'confirmed',
                Password::min(12)->mixedCase()->numbers()->symbols(),
            ],
        ]);

        DB::transaction(function () use ($user, $data) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($locked->user_type, ['employer', 'job_seeker', 'staff'], true), 403);
            $locked->forceFill([
                'password' => Hash::make($data['password']),
                'remember_token' => Str::random(60),
            ])->save();

            if (Schema::hasTable('personal_access_tokens')) {
                DB::table('personal_access_tokens')
                    ->where('tokenable_type', $locked->getMorphClass())
                    ->where('tokenable_id', $locked->id)->delete();
            }
            if (Schema::hasTable('password_reset_tokens')) {
                DB::table('password_reset_tokens')->where('email', $locked->email)->delete();
            }
            if (Schema::hasTable('email_otp_challenges')) {
                DB::table('email_otp_challenges')->where('user_id', $locked->id)->delete();
            }
        });

        Log::notice('admin.user.password_set', [
            'actor_id' => $actor->id, 'target_id' => $user->id,
        ]);

        return redirect()->route('admin.users.actions', $user->id)
            ->with('success', 'Password updated. Existing API tokens were revoked.');
    }

    public function destroy(Request $request, User $user, AdminUserPurgeService $purge): RedirectResponse
    {
        $actor = $this->permitted($request, $user);
        $request->validate([
            'admin_password' => ['required', 'string', 'current_password'],
            'confirm_email' => ['required', 'string', Rule::in([$user->email])],
        ]);
        abort_unless(hash_equals((string) $user->email, (string) $request->input('confirm_email')), 422);

        try {
            $fileCount = $purge->delete($user, $actor);
        } catch (\Throwable $exception) {
            Log::error('admin.user.purge_aborted', [
                'actor_id' => $actor->id, 'target_id' => $user->id,
                'error_type' => class_basename($exception),
            ]);
            return back()->withErrors([
                'delete' => 'Deletion could not complete safely. Account and files were not intentionally removed. Contact support to review.',
            ]);
        }

        return redirect()->route('user-management.index')
            ->with('success', 'Account disabled and deleted; '.$fileCount.' owned files removed. Job/application records were retained.');
    }
}
