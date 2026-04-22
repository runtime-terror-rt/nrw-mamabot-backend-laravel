<?php

namespace App\Jobs;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendServiceRequestNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $tokens;
    protected string $title;
    protected string $body;
    protected array $data;

    /**
     * Create a new job instance.
     */
    public function __construct(array $tokens, string $title, string $body, array $data = [])
    {
        $this->tokens = $tokens;
        $this->title  = $title;
        $this->body   = $body;
        $this->data   = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $messaging = (new Factory())
                ->withServiceAccount(storage_path('app/firebase-service-account.json'))
                ->createMessaging();

            $message = CloudMessage::new()
                ->withNotification(Notification::create($this->title, $this->body))
                ->withData($this->data);

            $report = $messaging->sendMulticast($message, $this->tokens);

            Log::info('Firebase Notification Sent', [
                'success' => $report->successes()->count(),
                'failure' => $report->failures()->count(),
            ]);

        } catch (\Throwable $e) {
            Log::error('Firebase Notification Failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
