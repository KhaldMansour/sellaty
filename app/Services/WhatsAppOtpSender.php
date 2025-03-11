<?php

namespace App\Services;

use App\Contracts\OtpSenderStrategy;
use Illuminate\Support\Facades\Http;

class WhatsAppOtpSender implements OtpSenderStrategy
{
    // WhatsApp API URL (Meta's WhatsApp Business API endpoint)
    protected $whatsappApiUrl = 'https://graph.facebook.com/v15.0/your_whatsapp_phone_number_id/messages';

    // Bearer Token for authentication (use your actual token here)
    protected $bearerToken = 'your_whatsapp_bearer_token';

    /**
     * Send OTP using WhatsApp
     *
     * @param string $phoneNumber
     * @param string $otp
     * @return bool
     */
    public function sendOtp(string $phoneNumber, string $otp): bool
    {
        $accessToken = env('FACEBOOK_API_ACCESS_TOKEN');
        $phoneNumberId = env('PHONE_NUMBER_ID');
        $templateName = env('TEMPLATE_NAME');
        $templateLanguageCode = env('WHATSAPP_LANGUAGE_CODE');

        $url = 'https://graph.facebook.com/v12.0/' . $phoneNumberId . '/messages';
        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $phoneNumber,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $templateLanguageCode,
                ],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => $otp,
                            ],
                        ],
                    ],
                    [
                        'type' => 'button',
                        'sub_type' => 'url',
                        'index' => 0,
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => $otp,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])
        ->post($url, $data);

        if ($response->successful()) {
            return true;
        } else {
            return false;
        }
    }
}
