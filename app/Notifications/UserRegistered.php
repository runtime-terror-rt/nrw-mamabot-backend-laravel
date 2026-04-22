<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRegistered extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)->subject('Welcome!')->line('Thanks for registering.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'User registered successfully',
            'title' => 'Welcome!',
            'type' => 'welcome',
        ];
    }
}
