<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class NotificationUser extends Notification
{
    use Queueable;

    private $email;
    public $email_model;

    /**
     * Create a new notification instance.
     */
    public function __construct($email, $email_model)
    {
        $this->$email = $email;
        $this->email_model = (array) $email_model;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject($this->email_model['subject'])
                    ->line($this->email_model['title'])
                    ->line($this->email_model['link']);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
