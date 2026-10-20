<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;

class PostDownloadTask implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $time;

    public function __construct($time)
    {
        $this->time = $time;
    }

    public function handle()
    {
        // Perform tasks after file download
        $folderPath = public_path($this->time);
        if (File::exists($folderPath)) {
            File::deleteDirectory($folderPath);
        }
    }
}
