<?php

namespace App\Services;

use Aws\Rekognition\RekognitionClient;

class RekognitionService
{
    protected RekognitionClient $client;

    public function __construct()
    {
        $this->client = new RekognitionClient([
            'version' => 'latest',
            'region' => config('services.aws.region'),
            'credentials' => [
                'key' => config('services.aws.key'),
                'secret' => config('services.aws.secret'),
            ],
        ]);
    }

    public function containsCar(string $imagePath): bool
    {
        $imageBytes = file_get_contents($imagePath);

        $result = $this->client->detectLabels([
            'Image' => ['Bytes' => $imageBytes],
            'MaxLabels' => 50,
            'MinConfidence' => 80,
        ]);

        $carKeywords = ['car', 'vehicle', 'automobile', 'sedan', 'sports car', 'truck'];

        foreach ($result['Labels'] as $label) {
            if (in_array(strtolower($label['Name']), $carKeywords)
                && $label['Confidence'] >= 80) {
                return true;
            }
        }

        return false;
    }

    public function isSafe(string $imagePath): bool
    {
        $imageBytes = file_get_contents($imagePath);

        $result = $this->client->detectModerationLabels([
            'Image' => ['Bytes' => $imageBytes],
            'MinConfidence' => 70,
        ]);

        return empty($result['ModerationLabels']);
    }
}
