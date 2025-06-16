<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $credentialsPath = storage_path(config('services.firebase.credentials'));

        $factory = (new Factory())->withServiceAccount(storage_path($credentialsPath));
        $this->messaging = $factory->createMessaging();
    }

    public function sendNotification(string $fcmToken, array $notification): bool
    {
        $notification = Notification::create($notification['title'], $notification['body']);

        $message = CloudMessage::withTarget('token', $fcmToken)
            ->withNotification($notification)
            ->withData($notification['data']);

        try {
            $this->messaging->send($message);

            return true;
        } catch (\Exception $e) {
            throw new HttpException(400, 'Failed to send message: ' . $e->getMessage());

            logger()->error('FCM Notification Failed', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
