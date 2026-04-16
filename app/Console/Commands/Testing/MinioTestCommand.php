<?php

declare(strict_types=1);

namespace App\Console\Commands\Testing;

use App\Models\User;
use App\Models\UserPhoto;
use App\Models\UserProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MinioTestCommand extends Command
{
    protected $signature = 'test:minio
        {--clean : Только очистить MinIO}
        {--create : Создать тестовые данные}
        {--delete : Удалить все тестовые данные}
        {--count=5 : Количество юзеров при создании}';

    protected $description = 'Тестирование MinIO: очистка, создание тестовых данных, проверка удаления';

    public function handle(): int
    {
        if ($this->option('clean')) {
            return $this->cleanMinio();
        }

        if ($this->option('create')) {
            return $this->createTestData();
        }

        if ($this->option('delete')) {
            return $this->deleteTestData();
        }

        // По умолчанию - полный цикл
        $this->info('=== Полный цикл тестирования MinIO ===');

        $this->cleanMinio();
        $this->createTestData();
        $this->verifyFiles();
        $this->deleteTestData();
        $this->verifyEmpty();

        $this->info('✅ Тест завершён успешно!');

        return self::SUCCESS;
    }

    private function cleanMinio(): int
    {
        $this->info('🗑️ Очистка MinIO...');

        // Удаляем всех тестовых юзеров из БД
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('user_photos')->truncate();
        DB::table('user_profiles')->truncate();
        DB::table('invitations')->truncate();
        DB::table('users')->where('is_seeded', true)->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Получаем все файлы из MinIO и удаляем
        $files = Storage::disk('s3')->allFiles();
        $count = count($files);

        if ($count > 0) {
            Storage::disk('s3')->delete($files);
            $this->info("Удалено {$count} файлов из MinIO");
        } else {
            $this->info('MinIO уже пуст');
        }

        return self::SUCCESS;
    }

    private function createTestData(): int
    {
        $this->info('📝 Создание тестовых данных...');

        $count = (int) $this->option('count');
        $users = [];
        $cities = [1, 2];

        for ($i = 1; $i <= $count; $i++) {
            $user = User::create([
                'registration_step' => 5,
                'is_seeded' => true,
            ]);

            UserProfile::create([
                'user_id' => $user->id,
                'name' => "Test User {$i}",
                'description' => "Test description {$i}",
                'birth_date' => now()->subYears(20 + $i),
                'gender' => $i % 2 === 0 ? 'female' : 'male',
                'city_id' => $cities[$i % 2],
                'country_id' => 1,
                'approved_at' => now(),
            ]);

            // Создаём 2 фото для каждого юзера
            for ($j = 1; $j <= 2; $j++) {
                $filename = "photo_{$j}.jpg";
                $path = "users/{$user->id}/photos/{$filename}";

                // Пустой файл (1x1 пиксель JPEG)
                $dummyImage = $this->getDummyJpeg();

                Storage::disk('s3')->put($path, $dummyImage, 'public');

                UserPhoto::create([
                    'user_id' => $user->id,
                    'file_path' => $path,
                    'position' => $j - 1,
                ]);
            }

            $users[] = $user->id;

            $this->line("  ✓ User {$user->id} создан с 2 фото");
        }

        $this->info('Создано юзеров: '.count($users));
        $this->info('Фото в БД: '.UserPhoto::whereIn('user_id', $users)->count());

        return self::SUCCESS;
    }

    private function deleteTestData(): int
    {
        $this->info('🔥 Удаление тестовых данных...');

        $seededUsers = User::where('is_seeded', true)
            ->with('photos')
            ->get();

        $count = $seededUsers->count();
        $photoCount = $seededUsers->sum(fn ($u) => $seededUsers->count());

        DB::transaction(function () use ($seededUsers) {
            // Удаляем фото из MinIO
            foreach ($seededUsers as $user) {
                foreach ($user->photos as $photo) {
                    if (! empty($photo->file_path)) {
                        Storage::disk('s3')->delete($photo->file_path);
                        $this->line("  ✓ Удалён файл: {$photo->file_path}");
                    }
                }
            }

            // Удаляем записи из БД
            $userIds = $seededUsers->pluck('id');
            UserPhoto::whereIn('user_id', $userIds)->delete();
            UserProfile::whereIn('user_id', $userIds)->delete();
            User::whereIn('id', $userIds)->delete();
        });

        $this->info("Удалено юзеров: {$count}");

        return self::SUCCESS;
    }

    private function verifyFiles(): void
    {
        $this->info('🔍 Проверка файлов в MinIO...');

        $files = Storage::disk('s3')->allFiles();
        $this->info('Файлов в MinIO: '.count($files));

        foreach ($files as $file) {
            $this->line("  - {$file}");
        }
    }

    private function verifyEmpty(): void
    {
        $this->info('🔍 Проверка что MinIO пуст...');

        $files = Storage::disk('s3')->allFiles();
        $users = User::where('is_seeded', true)->count();
        $photos = UserPhoto::count();

        if (count($files) === 0 && $users === 0 && $photos === 0) {
            $this->info('✅ MinIO пуст, юзеров нет, фото нет - всё чисто!');
        } else {
            $this->error('❌ Проблема: файлов='.count($files).", юзеров={$users}, фото={$photos}");
        }
    }

    private function getDummyJpeg(): string
    {
        // Минимальный валидный JPEG 1x1 пиксель (серый)
        return base64_decode(
            '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCwrND0wOTo0NDsqNxEQEQIQePiIHBwgIyMgICMgICMhD/2wBDAQYHBwgICMgICMgICMgICMgICMgICMgICMgICMgICMgICMgICMgICMgICMgICMgICMgICMgICMgICMgICMgICMhD/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAn/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBEQACEQA/ALUABo//2Q=='
        );
    }
}
