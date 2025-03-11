<?php

namespace App\Factories;

use App\Services\WhatsAppOtpSender;
use App\Contracts\OtpSenderStrategy;

class OtpSenderFactory
{
    /**
     * Create the appropriate OTP sender based on the given method.
     *
     * @param string $method
     * @return OtpSenderStrategy
     */
    public static function create(string $method): OtpSenderStrategy
    {
        switch ($method) {
            case 'whatsapp':
                return new WhatsAppOtpSender();
            default:
                throw new WhatsAppOtpSender();
        }
    }
}
