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

        $locale = app()->getLocale();

        $message = $this->getLocalizedMessage($locale, $otp);

        $response = Http::asForm()->post('https://api.appsenders.com/api/create-message', [
            'appkey' => config('services.appsender.appkey'),
            'authkey' => config('services.appsender.authkey'),
            'to' => $cleanedNumber,
            'message' => $message,
        ]);

        if ($response->successful()) {
            return true;
        } else {
            Log::info('AppSender OTP Response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }
    }

    private function getLocalizedMessage(string $locale, string $otp): string
    {
        if ($locale === 'ar') {
            return "👋 هلا!\nرمز تسجيل الدخول في سلعتي هو: {$otp}\n\nادخل الرمز في التطبيق وراح تسجّل بسهولة!\n\n⏳ الرمز صالح لمدة 2 دقائق.\n\nما طلبت هذا الرمز؟ تجاهل الرسالة بكل بساطة.";
        }

        // Default to English
        return "Hey there!\nYour Sellity login code is: {$otp}\n\nJust pop it into the app and you’re in!\n\n⏳ This code will expire in 2 minutes.\n\nDidn’t request this? No worries — just ignore this message.";
    }
}
