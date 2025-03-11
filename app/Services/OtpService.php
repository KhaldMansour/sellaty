<?php

namespace App\Services;

use App\Contracts\OtpSenderStrategy;
use App\Models\Otp;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class OtpService
{
    public const OTP_EXPIRY_TIME = 2;
    public const RESEND_TIME_LIMIT = 0;

    protected $otpSender;

    public function setOtpSender(OtpSenderStrategy $otpSender)
    {
        $this->otpSender = $otpSender;
    }

    public function generateOtp(string $phoneNumber): string
    {
        $otp = rand(100000, 999999);

        $expiresAt = Carbon::now()->addMinutes(self::OTP_EXPIRY_TIME);

        Cache::put("otp:$phoneNumber", $otp, $expiresAt);

        Otp::updateOrCreate(
            ['phone_number' => $phoneNumber],
            ['otp' => $otp, 'expires_at' => $expiresAt]
        );

        $this->otpSender->sendOtp($phoneNumber, $otp);

        return $otp;
    }

    public function validateOtp(string $phoneNumber, string $otp): bool
    {
        $cachedOtp = Cache::get("otp:$phoneNumber");

        if ($cachedOtp && $cachedOtp == $otp) {
            Cache::forget("otp:$phoneNumber");

            return true;
        }

        $otpRecord = Otp::where('phone_number', $phoneNumber)
                        ->where('expires_at', '>=', Carbon::now())
                        ->first();

        if ($otpRecord && $otpRecord->otp == $otp) {
            Cache::forget("otp:$phoneNumber");

            return true;
        }

        return false;
    }

    public function shouldSendOtp(string $phoneNumber): bool
    {
        $cachedOtp = Cache::get("otp:$phoneNumber");

        if ($cachedOtp) {
            return false;
        }

        $otpRecord = Otp::where('phone_number', $phoneNumber)->first();

        if ($otpRecord && $otpRecord->expires_at->diffInMinutes(Carbon::now()) < self::RESEND_TIME_LIMIT) {
            return false;
        }

        return true;
    }



    public function sendOtp(string $phoneNumber): bool
    {
        $cachedOtp = Cache::get("otp:$phoneNumber");

        if ($cachedOtp) {
            return false;
        } else {
            $otpRecord = Otp::where('phone_number', $phoneNumber)->first();

            if ($otpRecord && $otpRecord->expires_at->diffInMinutes(Carbon::now()) < self::RESEND_TIME_LIMIT) {
                return false;
            }
        }

        return $this->generateOtp($phoneNumber);
    }
}
