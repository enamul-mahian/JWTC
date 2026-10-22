<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PruneEmailOtps extends Command
{
    protected $signature = 'email-otp:prune';
    protected $description = 'Delete expired password challenges and encrypted pending registrations';

    public function handle(): int
    {
        $total = 0;
        foreach (['email_otp_challenges', 'pending_registrations'] as $table) {
            if (Schema::hasTable($table)) {
                $total += DB::table($table)->where('expires_at', '<', now())->delete();
            }
        }
        $this->info("Expired OTP records cleared: {$total}");
        return self::SUCCESS;
    }
}
