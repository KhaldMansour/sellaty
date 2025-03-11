<?php

namespace App\Contracts;

interface OtpSenderStrategy
{
    public function sendOtp(string $phoneNumber, string $otp): bool;
}
