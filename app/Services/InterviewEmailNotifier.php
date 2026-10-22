<?php

namespace App\Services;

use App\Models\Application;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class InterviewEmailNotifier
{
    /**
     * Email only an authenticated-site URL, never the meeting token/link.
     * Delivery errors cannot undo an already committed appointment.
     */
    public function notify(Application $application, object $interview, string $action, User $actor): void
    {
        $toApplicant = in_array($action, ['schedule', 'reschedule', 'cancel', 'complete'], true);
        $recipient = $toApplicant
            ? User::find($application->user_id)
            : User::find($interview->organizer_id);

        if (!$recipient || !$recipient->email || (int) $recipient->id === (int) $actor->id) {
            return;
        }

        $page = $toApplicant
            ? route('interviews.applicant.show', $application->id)
            : route('interviews.manage.show', $application->id);

        $subject = 'JWTC interview: ' . str_replace('_', ' ', $action);
        $body = "Your JWTC interview has a new update: " .
            ucfirst(str_replace('_', ' ', $action)) . ".\n\n" .
            "Please sign in to your JWTC account to view the details:\n" .
            $page . "\n\n" .
            "For your privacy, this email does not contain the private meeting link.\n" .
            "If you did not expect this notice, contact site support.";

        try {
            Mail::raw($body, static function ($message) use ($recipient, $subject) {
                $message->to($recipient->email)->subject($subject);
            });
        } catch (\Throwable $exception) {
            // Production SMTP settings are not yet verified. Never log the
            // meeting URL, participant document data or authentication tokens.
            report($exception);
        }
    }
}
