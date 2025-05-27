<?php

namespace App\Services;

use App\Contracts\OtpSenderStrategy;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AppSender implements OtpSenderStrategy
{
    public function sendOtp(string $phoneNumber, string $otp): bool
    {
        $cleanedNumber = ltrim($phoneNumber, '+');

        $response = Http::asForm()->post('https://api.appsenders.com/api/create-message', [
            'appkey' => config('services.appsender.appkey'),
            'authkey' => config('services.appsender.authkey'),
            'to' => $cleanedNumber,
            'message' => 'Hello dear from Sellity, your OTP is: ' . $otp,
        ]);

        if ($response->successful()) {
            Log::info('AppSender OTP Response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return true;
        } else {
            return false;
        }
    }
}
