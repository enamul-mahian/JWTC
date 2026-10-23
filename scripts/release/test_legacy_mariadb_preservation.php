<?php
/**
 * Synthetic record-preservation gate for the disposable MariaDB 11.4 CI job.
 * NEVER execute against staging, production, or an existing application DB.
 * Only hashes of made-up CI rows are stored in the temporary runner.
 */
declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$mode = $argv[1] ?? '';
if (!in_array($mode, ['seed', 'verify'], true)
    || PHP_SAPI !== 'cli'
    || getenv('JWTC_LEGACY_IMPORT_FIXTURE') !== 'ephemeral-only'
    || getenv('APP_ENV') !== 'testing'
    || getenv('DB_DATABASE') !== 'jwtc_legacy_ci_only'
    || getenv('DB_HOST') !== '127.0.0.1'
    || getenv('DB_USERNAME') !== 'jwtc_ci') {
    fwrite(STDERR, "BLOCKED: isolated synthetic CI schema only.\n");
    exit(2);
}

require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$tables = [
    'job_categories', 'countries', 'company_sizes', 'industry_types',
    'users', 'companies', 'jobs', 'applications', 'unique_codes',
    'uploaded_cvs', 'jobseeker_uploads', 'jobseeker_certificates',
    'cover_letters', 'payment_credentials', 'application_settings',
];
$baselinePath = dirname(__DIR__, 2) . '/storage/app/jwtc-legacy-ci-row-hashes.json';

try {
    $db = DB::connection('mysql');
    if (($db->selectOne('SELECT DATABASE() AS name')->name ?? '') !== 'jwtc_legacy_ci_only') {
        throw new RuntimeException('Wrong database selected.');
    }

    if ($mode === 'seed') {
        foreach ($tables as $table) {
            if ($db->table($table)->count() !== 0) {
                throw new RuntimeException('CI legacy schema is not empty.');
            }
        }
        $db->transaction(static function () use ($db): void {
            $db->table('job_categories')->insert(['id' => 910001, 'name' => 'Synthetic category']);
            $db->table('countries')->insert(['id' => 910002, 'name' => 'Synthetic country', 'code' => 'ZZ']);
            $db->table('company_sizes')->insert(['id' => 910003, 'title' => 'Synthetic size']);
            $db->table('industry_types')->insert(['id' => 910004, 'name' => 'Synthetic industry']);
            $db->table('users')->insert([
                ['id' => 910005, 'name' => 'Synthetic employer', 'job_category_id' => 910001,
                    'email' => 'employer@example.invalid', 'user_type' => 'employer'],
                ['id' => 910006, 'name' => 'Synthetic applicant', 'job_category_id' => 910001,
                    'email' => 'applicant@example.invalid', 'user_type' => 'job_seeker'],
            ]);
            $db->table('companies')->insert([
                'id' => 910007, 'user_id' => 910005, 'company_sizes_id' => 910003,
                'industry_type_id' => 910004, 'country_id' => 910002,
                'company_name' => 'Synthetic Company',
            ]);
            $db->table('jobs')->insert([
                'id' => 910008, 'user_id' => 910005, 'job_category_id' => 910001,
                'company_id' => 910007, 'country_id' => 910002,
                'title' => 'Synthetic Job', 'slug' => 'synthetic-ci-only-job',
                'job_status' => 'Approved', 'salary_type' => 'monthly',
                'resume_receiving_email' => 'synthetic@example.invalid',
                'minimum_salary' => 100, 'maximum_salary' => 200,
                'job_responsibilities' => 'Fixture-only testing.', 'is_publish' => 1,
            ]);
            $db->table('applications')->insert([
                'id' => 910009, 'user_id' => 910006, 'employer_id' => 910007,
                'job_id' => 910008, 'status' => 1, 'date_applied' => '2026-10-22',
                'expected_salary' => 150.00,
            ]);
            $db->table('unique_codes')->insert([
                'id' => 910010, 'user_id' => 910006, 'code' => 'CI-SYNTHETIC-ONLY',
                'date' => '2026-10-22', 'is_used' => 0,
            ]);
            foreach (['uploaded_cvs', 'jobseeker_uploads', 'jobseeker_certificates'] as $index => $table) {
                $db->table($table)->insert([
                    'id' => 910011 + $index, 'user_id' => 910006,
                    'title' => 'Synthetic document',
                    'file' => 'synthetic-ci-only/nonexistent-file.pdf',
                ]);
            }
            $db->table('cover_letters')->insert([
                'id' => 910014, 'user_id' => 910006, 'title' => 'Synthetic letter',
                'content' => 'CI data only.',
            ]);
            $db->table('payment_credentials')->insert([
                'id' => 910015, 'name' => 'ci-fixture-only', 'value' => 'not-a-real-secret',
            ]);
            $db->table('application_settings')->insert([
                'id' => 910016, 'business_name' => 'Synthetic Business',
                'meta_title' => 'Synthetic SEO Title',
            ]);
        });
    }

    $prior = null;
    if ($mode !== 'seed') {
        if (!is_file($baselinePath)) throw new RuntimeException('No trusted pre-upgrade CI baseline.');
        $prior = json_decode((string) file_get_contents($baselinePath), true, 512, JSON_THROW_ON_ERROR);
    }
    $fingerprints = [];
    foreach ($tables as $table) {
        $columns = $prior[$table]['columns'] ?? $db->getSchemaBuilder()->getColumnListing($table);
        $query=$db->table($table)->select($columns)->orderBy('id');
        if ($table==='countries' && $prior) {
            $extra=$db->table($table)->whereNotIn('id',$prior[$table]['ids'])->get();
            if($extra->count()>4 || $extra->contains(fn($row)=>!in_array($row->name,['United Kingdom','Scotland','Ireland','UK Territories'],true)))throw new RuntimeException('Unexpected location seed addition.');
            $query->whereIn('id',$prior[$table]['ids']);
        }
        $rows = $query->get()->toArray();
        if (count($rows) === 0) {
            throw new RuntimeException('Missing synthetic legacy record in ' . $table);
        }
        $fingerprints[$table] = [
            'ids' => array_map(fn($row)=>(int)$row->id,$rows),
            'count' => count($rows),
            'columns' => $columns,
            'sha256' => hash('sha256', json_encode($rows, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES)),
        ];
    }

    if ($mode === 'seed') {
        if (file_put_contents($baselinePath, json_encode($fingerprints, JSON_THROW_ON_ERROR)) === false) {
            throw new RuntimeException('Could not write CI fingerprints.');
        }
        echo "PASS: Seeded fictional legacy applicant/job/CV/payment/SEO data in disposable CI.\n";
    } else {
        if (!is_file($baselinePath)) {
            throw new RuntimeException('No trusted pre-upgrade CI baseline.');
        }
        $prior = json_decode((string) file_get_contents($baselinePath), true, 512, JSON_THROW_ON_ERROR);
        if ($fingerprints !== $prior) {
            throw new RuntimeException('Legacy rows changed during additive migration.');
        }
        echo "PASS: All synthetic legacy row counts and SHA-256 fingerprints unchanged.\n";
    }
} catch (Throwable $error) {
    // Never expose actual database records or values in CI logs.
    fwrite(STDERR, 'BLOCKED: ' . $error->getMessage() . "\n");
    exit(1);
}
