<?php

declare(strict_types=1);

namespace Tests\Functional;

use App\Models\User;
use App\Models\UserProfile;
use App\Services\Image\ImageGenerationService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\Support\FunctionalTester;

class SeedInvitationsCest
{
    private int $cityId = 1;

    public function _before(FunctionalTester $I)
    {
        // Отключаем FK для возможности ручного указания ID городов в тестах
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Используем сидеры для подготовки данных
        Artisan::call('db:seed', ['--class' => 'CitySeeder']);
        Artisan::call('db:seed', ['--class' => 'InvitationTypeSeeder']);

        // Мокаем AI сервис генерации фото
        $mock = Mockery::mock(ImageGenerationService::class);
        $mock->shouldReceive('generateAndSaveProfilePhoto')->andReturn('photos/test.jpg');
        $mock->shouldReceive('isConfigured')->andReturn(true);

        app()->instance(ImageGenerationService::class, $mock);
    }

    public function _after(FunctionalTester $I)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        Mockery::close();
    }

    public function testSeedInvitationsCommand(FunctionalTester $I)
    {
        $I->wantTo('проверить команду сидинга и создание записей в БД');

        $count = 3;

        // Запуск команды сидинга
        Artisan::call('seed:invitations', [
            '--city' => $this->cityId,
            '--count' => $count,
            '--no-interaction' => true,
        ]);

        // Проверка количества созданных сущностей
        $I->seeNumRecords($count, 'users');
        $I->seeNumRecords($count, 'user_profiles');
        $I->seeNumRecords($count, 'invitations');
        $I->seeNumRecords($count, 'user_photos');

        // Проверка корректности данных
        $I->seeRecord(User::class, [
            'is_seeded' => true,
            'registration_step' => 5,
        ]);

        $I->seeRecord(UserProfile::class, [
            'city_id' => $this->cityId,
            'country_id' => 1, // Дананг -> Вьетнам (ID 1)
        ]);

        $user = User::where('is_seeded', true)->first();
        $I->assertNotNull($user->profile, 'У пользователя должен быть профиль');
        $I->assertCount(1, $user->invitations, 'Должно быть создано приглашение');
    }

    public function testCommandFailsOnInvalidCity(FunctionalTester $I)
    {
        $I->wantTo('убедиться, что команда выдает ошибку при неверном ID города');

        $exitCode = Artisan::call('seed:invitations', [
            '--city' => 999,
            '--count' => 1,
            '--no-interaction' => true,
        ]);
        $I->assertEquals(1, $exitCode);
    }
}
