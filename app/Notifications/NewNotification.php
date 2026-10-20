<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
        ->line($this->data['intro'])
        ->action($this->data['actionText'], $this->data['actionUrl'])
        ->line($this->data['outro']);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    

    public function toDatabase($notifiable)
    {
        return [
            'user_id' => $notifiable->id,
            'intro' => $this->data['intro'],
            'actionText' => $this->data['actionText'],
            'actionUrl' => $this->data['actionUrl'],
            'outro' => $this->data['outro']
        ];
    }
}