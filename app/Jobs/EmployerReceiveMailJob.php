<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Mail\EmployerReceiveMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class EmployerReceiveMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $user;
    public $jobSeeker;

    /**
     * Create a new job instance.
     */
    public function __construct($user, $jobSeeker)
    {
        $this->user = $user;
        $this->jobSeeker = $jobSeeker;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->user->email)->send(new EmployerReceiveMail($this->jobSeeker,  $this->user));
    }
}
