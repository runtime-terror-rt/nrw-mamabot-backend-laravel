<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QaRegistrationNotification extends Notification
{
    use Queueable;

    // ১. প্রপার্টি ডিক্লেয়ার করতে হবে
    public $session; 

    public function __construct($session)
    {
        $this->session = $session;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Q&A Session Registration Confirmed')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have successfully registered for: ' . $this->session->topic)
            ->line('Doctor: ' . $this->session->doctor->name)
            ->line('Time: ' . $this->session->start_time->format('M d, Y h:i A'))
            ->line('Meeting Link: ' . $this->session->meeting_link)
            ->action('View Session Details', url('/dashboard'))
            ->line('Thank you for using our application!');
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => 'Registered for Q&A: ' . $this->session->topic,
            'session_id' => $this->session->id,
            'doctor_name' => $this->session->doctor->name,
            'time' => $this->session->start_time->toDateTimeString(),
        ];
    }
}