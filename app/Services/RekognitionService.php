<?php

namespace App\Services;

use Aws\Rekognition\RekognitionClient;
use Illuminate\Support\Facades\Log;

class RekognitionService
{
    protected RekognitionClient $client;

    public function __construct()
    {
        $this->client = new RekognitionClient([
            'version' => 'latest',
            'region' => config('services.aws.rekognition.region'),
            'credentials' => [
                'key' => config('services.aws.rekognition.key'),
                'secret' => config('services.aws.rekognition.secret'),
            ],
        ]);
    }

    public function analyzeImage(string $imagePath): array
    {
        $imageBytes = file_get_contents($imagePath);

        $results = [
            'is_safe' => true,
            'contains_car' => false,
        ];

        try {
            $moderation = $this->client->detectModerationLabels([
                'Image' => ['Bytes' => $imageBytes],
                'MinConfidence' => 70,
            ]);

            if (!empty($moderation['ModerationLabels'])) {
                $results['is_safe'] = false;

                return $results;
            }

            $labels = $this->client->detectLabels([
                'Image' => ['Bytes' => $imageBytes],
                'MaxLabels' => 50,
                'MinConfidence' => 80,
            ]);

            $carKeywords = ['car', 'vehicle', 'automobile', 'sedan', 'sports car', 'truck'];

            foreach ($labels['Labels'] as $label) {
                if (in_array(strtolower($label['Name']), $carKeywords)) {
                    $results['contains_car'] = true;
                    break;
                }
            }
        } catch (\Exception $e) {
            Log::error("Rekognition analyzeImage failed: " . $e->getMessage());
        }

        return $results;
    }
}
