<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tokens;
    protected $title;
    protected $body;
    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct($tokens, $title, $body, $data = [])
    {
        $this->tokens = $tokens;
        $this->title  = $title;
        $this->body   = $body;
        $this->data   = $data;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        
        try {
            $messaging = (new Factory())
                ->withServiceAccount(storage_path('app/notification/firebase_credentials.json'))
                ->createMessaging();

            $message = CloudMessage::new()
                ->withNotification(Notification::create($this->title, $this->body))
                ->withData($this->data ?? []);

            $report = $messaging->sendMulticast($message, $this->tokens);

            \Log::info('Notification Sent', [
                'success' => $report->successes()->count(),
                'failure' => $report->failures()->count(),
            ]);
            foreach ($report->failures()->getItems() as $failure) {
            \Log::error('FCM Error', [
                'token' => $failure->target()->value(),
                'error' => $failure->error()->getMessage()
                ]);
            }
        } catch (\Throwable $e) {
            \Log::error('Notification Failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

}
