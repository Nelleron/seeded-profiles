<?php

declare(strict_types=1);

namespace App\Console\Commands\Seeding;

use App\Models\UserPhoto;
use App\Services\Image\ImageGenerationService;
use App\Services\Seeding\SeedingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Artisan-команда для генерации фейковых профилей с AI-фото.
 */
class SeedInvitationsCommand extends Command
{
    protected $signature = 'seed:invitations {--city= : ID города из списка} {--count=10 : Количество профилей}';

    protected $description = 'Генерация фейковых профилей с использованием AI-фото и профильных данных';

    public function __construct(
        private SeedingService $seedingService,
    ) {
        parent::__construct();
    }

    private function getImageService(): ImageGenerationService
    {
        return app(ImageGenerationService::class);
    }

    public function handle(): int
    {
        $this->seedingService->reset();

        $cityId = $this->getValidatedCityId();
        if ($cityId === null) {
            return self::FAILURE;
        }

        $count = $this->getValidatedCount();
        if ($count === null) {
            return self::FAILURE;
        }

        $this->info("🚀 Генерация {$count} профилей для города: {$this->seedingService->getCityName($cityId)}");
        $this->newLine();

        $generated = 0;
        $withPhoto = 0;
        $failed = 0;

        for ($i = 0; $i < $count; $i++) {
            $result = $this->seedingService->createSeededProfile($cityId);
            $generated++;

            $this->displayProfileProgress($i + 1, $count, $result['data']);

            $photoResult = $this->generateAndSavePhoto(
                $result['data']['user_id'],
                $result['data']['gender'],
                $result['data']['birth_date']
            );

            if ($photoResult['success']) {
                $withPhoto++;
                $this->output->write("\033[1A\033[2K    Фото: ✅ Готово\n");
            } else {
                $failed++;
                $reason = $photoResult['error'] ?? 'Ошибка сервиса';
                $this->output->write("\033[1A\033[2K    Фото: ❌ {$reason}\n");
            }

            $this->info("  ✨ Профиль \"{$result['data']['name']}\" создан");
            $this->newLine();
        }

        $this->info("🏁 Итого создано: {$generated} профилей");
        $this->line("   С фото: <fg=green>{$withPhoto}</>");
        $this->line("   Без фото: <fg=yellow>{$failed}</>");

        return self::SUCCESS;
    }

    private function getValidatedCityId(): ?int
    {
        $cityId = $this->option('city');

        if ($cityId !== null && $this->seedingService->isValidCity((int) $cityId)) {
            return (int) $cityId;
        }

        $this->error('Город не указан или не найден. Доступные города:');
        $this->newLine();

        foreach ($this->seedingService->getAvailableCities() as $id => $name) {
            $this->line("{$id}: {$name}");
        }

        $this->newLine();
        $cityId = $this->ask('Введите ID города');

        if (! $this->seedingService->isValidCity((int) $cityId)) {
            $this->error('Неверный ID города');

            return null;
        }

        return (int) $cityId;
    }

    private function getValidatedCount(): ?int
    {
        $count = (int) $this->option('count');

        if ($count < 1 || $count > 1000) {
            $this->error('Количество должно быть от 1 до 1000');

            return null;
        }

        return $count;
    }

    private function displayProfileProgress(int $current, int $total, array $data): void
    {
        $this->output->write("  [{$current}/{$total}] Создание профиля:\n");
        $this->output->write("    ID: {$data['user_id']}\n");
        $this->output->write("    Имя: {$data['name']}\n");
        $this->output->write("    Город: {$data['city']}\n");
        $this->output->write("    Тип приглашения: {$data['invitation_type']}\n");
        $this->output->write("    Фото: ⏳ Генерация...\n");
    }

    private function generateAndSavePhoto(int $userId, $gender, $birthDate): array
    {
        try {
            $imageService = $this->getImageService();
            $photoPath = $imageService->generateAndSaveProfilePhoto($userId, $gender, $birthDate);

            if ($photoPath) {
                UserPhoto::create([
                    'user_id' => $userId,
                    'file_path' => $photoPath,
                    'position' => 0,
                ]);

                Log::info('Profile photo generated', ['user_id' => $userId, 'path' => $photoPath]);

                return ['success' => true, 'error' => null];
            }

            Log::warning('Profile photo not generated', ['user_id' => $userId]);

            return ['success' => false, 'error' => 'Нет данных от API'];
        } catch (\Exception $e) {
            Log::error('Photo generation failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
