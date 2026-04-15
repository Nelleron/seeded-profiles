<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Image\ImageGenerationService;
use App\Services\Invitation\InvitationTemplateService;
use App\Services\Profile\BioPoolService;
use App\Services\Profile\NamePoolService;
use App\Services\Profile\ProfileDataGenerator;
use App\Services\Seeding\SeedingService;
use Illuminate\Support\ServiceProvider;

/**
 * Провайдер для регистрации сервисов сидирования.
 */
class SeedingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Регистрируем сервисы с данными из конфигурации
        $this->app->singleton(NamePoolService::class, fn () => NamePoolService::fromConfig());
        $this->app->singleton(BioPoolService::class, fn () => BioPoolService::fromConfig());
        $this->app->singleton(InvitationTemplateService::class, fn () => InvitationTemplateService::fromConfig());
        $this->app->singleton(ProfileDataGenerator::class);

        // SeedingService как фасад для координации
        $this->app->singleton(SeedingService::class, fn ($app) => new SeedingService(
            $app->make(NamePoolService::class),
            $app->make(BioPoolService::class),
            $app->make(InvitationTemplateService::class),
            $app->make(ProfileDataGenerator::class),
        ));

        // ImageGenerationService с параметрами из конфига
        $this->app->singleton(ImageGenerationService::class, fn () => new ImageGenerationService(
            apiKey: (string) config('services.huggingface.api_key'),
            model: (string) config('services.huggingface.model'),
            timeout: (int) config('services.huggingface.timeout'),
            imageWidth: (int) config('services.huggingface.image_width'),
            imageHeight: (int) config('services.huggingface.image_height'),
            inferenceSteps: (int) config('services.huggingface.inference_steps'),
            guidanceScale: (float) config('services.huggingface.guidance_scale'),
            negativePrompt: (string) config('services.huggingface.negative_prompt'),
        ));
    }

    public function boot(): void
    {
        //
    }
}
