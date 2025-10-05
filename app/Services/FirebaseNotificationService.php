<?php

namespace App\Services;

use App\Models\User;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $firebaseConfig = [
            'type' => 'service_account',
            'project_id' => config('services.firebase.project_id'),
            'private_key_id' => config('services.firebase.private_key_id'),
            'private_key' => str_replace("\\n", "\n", config('services.firebase.private_key')),
            'client_email' => config('services.firebase.client_email'),
            'client_id' => config('services.firebase.client_id'),
            'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
            'token_uri' => 'https://oauth2.googleapis.com/token',
            'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
            'client_x509_cert_url' => config('services.firebase.client_x509_cert_url'),
            'universe_domain' => 'googleapis.com'
        ];

        $factory = (new Factory())->withServiceAccount($firebaseConfig);
        $this->messaging = $factory->createMessaging();
    }

    public function sendNotification(User $user, array $notification): bool
    {
        $fcmToken = $user->fcm_token;

        if (empty($fcmToken)) {
            logger()->warning("🚫 User {$user->id} has no FCM token.");

            return false;
        }

        $message = CloudMessage::withTarget('token', $fcmToken)
            ->withNotification(['title' => $notification['title'], 'body' => $notification['body']])
            ->withData($this->flattenData($notification['data']), );

        try {
            $this->messaging->send($message);

            $user->notifications()->create([
                'title' => $notification['title'],
                'body' => $notification['body'],
                'data' => $notification['data'],
            ]);

            return true;
        } catch (NotFound $e) {
            logger()->warning("⚠️ Invalid FCM token for user {$user->id}, removing token... Error: {$e->getMessage()}");
            $user->update(['fcm_token' => null]);

            return false;
        } catch (\Exception $e) {
            logger()->error("❌ FCM Notification Failed for user {$user->id}", [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function flattenData(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = json_encode($value);
            } else {
                $result[$key] = (string)$value;
            }
        }

        return $result;
    }
}
