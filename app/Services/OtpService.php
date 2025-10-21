<?php

namespace App\Services;

use App\Contracts\OtpSenderStrategy;
use App\Models\Otp;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OtpService
{
    public const OTP_EXPIRY_TIME = 35;
    public const RESEND_TIME_LIMIT = 0;

    protected $otpSender;

    public function setOtpSender(OtpSenderStrategy $otpSender)
    {
        $this->otpSender = $otpSender;
    }

    public function generateOtp(string $phoneNumber): string
    {
        $otp = rand(100000, 999999);

        $expiresAt = Carbon::now()->addSeconds(self::OTP_EXPIRY_TIME);

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
        $cacheKey = "otp:$phoneNumber";
        $cachedOtp = Cache::get($cacheKey);

        if ($cachedOtp) {
            if ($cachedOtp == $otp) {
                Cache::forget($cacheKey);

                return true;
            }
        }

        $otpRecord = Otp::where('phone_number', $phoneNumber)->first();

        if ($otpRecord) {
            if ($otpRecord->otp != $otp) {
                throw new HttpException(422, __('messages.otp_invalid'));
            }

            if ($otpRecord->expires_at < Carbon::now()) {
                throw new HttpException(422, __('messages.otp_expired'));
            }

            Cache::forget($cacheKey);

            return true;
        }
        throw new HttpException(422, __('messages.otp_invalid'));
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
