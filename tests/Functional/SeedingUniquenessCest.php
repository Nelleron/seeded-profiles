<?php

declare(strict_types=1);

namespace Tests\Functional;

use App\Models\Invitation;
use App\Models\UserProfile;
use App\Services\Image\ImageGenerationService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\Support\FunctionalTester;

class SeedingUniquenessCest
{
    public function _before(FunctionalTester $I)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Очищаем таблицы перед каждым тестом
        DB::table('user_photos')->truncate();
        DB::table('invitations')->truncate();
        DB::table('user_profiles')->truncate();
        DB::table('users')->truncate();

        // Подготовка данных через сидеры
        Artisan::call('db:seed', ['--class' => 'CitySeeder']);
        Artisan::call('db:seed', ['--class' => 'InvitationTypeSeeder']);

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

    public function testSeedingProducesUniqueDataFor20Profiles(FunctionalTester $I)
    {
        $I->wantTo('убедиться, что 20 сгенерированных профилей имеют уникальные имена, био и шаблоны');

        $count = 20;
        $cityId = 1; // Дананг

        // Запуск сидинга
        Artisan::call('seed:invitations', [
            '--city' => $cityId,
            '--count' => $count,
            '--no-interaction' => true,
        ]);

        $profiles = UserProfile::all();
        $invitations = Invitation::all();

        $I->assertCount($count, $profiles, 'Должно быть создано 20 профилей');

        // 1. Уникальность имен
        $names = $profiles->pluck('name')->toArray();
        $I->assertEquals(count($names), count(array_unique($names)), 'Все 20 имен должны быть уникальны');

        // 2. Уникальность описаний (био)
        $descriptions = $profiles->pluck('description')->toArray();
        $I->assertEquals(count($descriptions), count(array_unique($descriptions)), 'Все 20 описаний (био) должны быть уникальны');

        // 3. Уникальность шаблонов приглашений (текст + локация)
        $invitationTemplates = $invitations->map(fn ($inv) => $inv->description.'|'.$inv->location)->toArray();
        $I->assertEquals(
            count($invitationTemplates),
            count(array_unique($invitationTemplates)),
            'Все 20 шаблонов приглашений должны быть уникальны'
        );

        // 4. Цикличность типов (1,2,3,4,5...)
        $typeIds = $invitations->pluck('type_id')->toArray();
        $expectedTypes = [];
        for ($i = 0; $i < $count; $i++) {
            $expectedTypes[] = ($i % 5) + 1;
        }
        $I->assertEquals($expectedTypes, $typeIds, 'Типы приглашений должны назначаться по кругу 1-2-3-4-5');
    }
}
