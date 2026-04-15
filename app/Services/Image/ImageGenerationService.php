<?php

declare(strict_types=1);

namespace App\Services\Image;

use App\Enums\Gender;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Сервис генерации изображений через HuggingFace Inference API.
 */
class ImageGenerationService
{
    private const API_URL = 'https://router.huggingface.co/hf-inference/models/';

    /**
     * @param  string  $apiKey  API ключ HuggingFace
     * @param  string  $model  Модель для генерации
     * @param  int  $timeout  Таймаут запроса в секундах
     * @param  int  $imageWidth  Ширина изображения
     * @param  int  $imageHeight  Высота изображения
     * @param  int  $inferenceSteps  Количество шагов инференса
     * @param  float  $guidanceScale  Масштаб guidance
     * @param  string  $negativePrompt  Негативный промпт
     */
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
        private readonly int $timeout,
        private readonly int $imageWidth,
        private readonly int $imageHeight,
        private readonly int $inferenceSteps,
        private readonly float $guidanceScale,
        private readonly string $negativePrompt,
    ) {}

    /**
     * Сгенерировать и сохранить фото профиля.
     *
     * @param  int  $userId  ID пользователя
     * @param  Gender  $gender  Пол
     * @param  Carbon  $birthDate  Дата рождения
     * @return string|null Путь к файлу или null при ошибке
     */
    public function generateAndSaveProfilePhoto(
        int $userId,
        Gender $gender,
        Carbon $birthDate
    ): ?string {
        try {
            if (empty($this->apiKey)) {
                Log::warning('HuggingFace API key not configured', ['user_id' => $userId]);

                return null;
            }

            $age = (int) $birthDate->diffInYears(now());
            $prompt = $this->buildPrompt($gender, $age);

            Log::info('Starting photo generation', [
                'user_id' => $userId,
                'gender' => $gender->value,
                'age' => $age,
                'model' => $this->model,
            ]);

            $imageData = $this->generateImage($prompt);

            if (! $imageData) {
                Log::warning('No image data received from API', ['user_id' => $userId]);

                return null;
            }

            Log::info('Image data received, saving to MinIO', [
                'user_id' => $userId,
                'data_size' => strlen($imageData),
            ]);

            return $this->saveToMinIO($userId, $imageData);
        } catch (Exception $e) {
            Log::error('Error generating profile photo', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Проверить, настроен ли API ключ.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    /**
     * Построить промпт для генерации.
     */
    private function buildPrompt(Gender $gender, int $age): string
    {
        $genderText = $gender === Gender::Male ? 'man' : 'woman';
        $ageRange = $this->getAgeRange($age);

        return "Professional portrait photo of a {$ageRange} year old {$genderText}, "
            .'looking at camera, friendly expression, studio lighting, high quality, '
            .'realistic, no text, no watermark';
    }

    /**
     * Получить возрастной диапазон для промпта.
     */
    private function getAgeRange(int $age): string
    {
        if ($age < 25) {
            return '22-25';
        }
        if ($age < 30) {
            return '26-29';
        }

        return '30-34';
    }

    /**
     * Сгенерировать изображение через API.
     */
    private function generateImage(string $prompt): ?string
    {
        try {
            $endpoint = self::API_URL.$this->model;

            Log::info('Sending request to HuggingFace', [
                'endpoint' => $endpoint,
                'model' => $this->model,
                'prompt_length' => strlen($prompt),
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
                'x-use-cache' => '0',
            ])
                ->timeout($this->timeout)
                ->post($endpoint, [
                    'inputs' => $prompt,
                    'parameters' => [
                        'width' => $this->imageWidth,
                        'height' => $this->imageHeight,
                        'num_inference_steps' => $this->inferenceSteps,
                        'guidance_scale' => $this->guidanceScale,
                        'negative_prompt' => $this->negativePrompt,
                    ],
                ]);

            $status = $response->status();
            $body = $response->body();

            Log::info('HuggingFace API response', [
                'status' => $status,
                'body_length' => strlen($body),
                'body_preview' => substr($body, 0, 200),
            ]);

            if ($response->failed()) {
                Log::error('HuggingFace API request failed', [
                    'status' => $status,
                    'body' => $body,
                    'prompt' => $prompt,
                    'model' => $this->model,
                ]);

                // Модель загружается (503)
                if ($status === 503) {
                    $errorData = json_decode($body, true);
                    $estimatedTime = $errorData['estimated_time'] ?? 20;
                    Log::info('Model loading, waiting...', ['estimated_time' => $estimatedTime]);

                    sleep((int) min($estimatedTime, 30));

                    return $this->generateImage($prompt);
                }

                // Вывод ошибки в консоль для отладки
                fwrite(STDERR, "\n🔴 HuggingFace API Error (Status: {$status}):\n");
                fwrite(STDERR, 'Response: '.substr($body, 0, 300)."\n\n");
                fflush(STDERR);

                return null;
            }

            return $body;
        } catch (Exception $e) {
            Log::error('Image generation failed', [
                'error' => $e->getMessage(),
                'prompt' => $prompt,
                'model' => $this->model,
            ]);

            fwrite(STDERR, "\n🔴 Exception: ".$e->getMessage()."\n\n");
            fflush(STDERR);

            return null;
        }
    }

    /**
     * Сохранить изображение в MinIO.
     */
    private function saveToMinIO(int $userId, string $imageData): string
    {
        $filename = Str::uuid().'.jpg';
        $path = "users/{$userId}/photos/{$filename}";

        Storage::disk('s3')->put($path, $imageData, 'public');

        return $path;
    }
}
