<?php

declare(strict_types=1);

namespace Tests\Functional;

use App\Http\Controllers\SeededController;
use App\Http\Requests\Seeded\SeedProfileDestroyByCityRequest;
use App\Models\Invitation;
use App\Models\User;
use App\Models\UserPhoto;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\Support\FunctionalTester;

class SeededControllerCest
{
    private int $cityId = 1;

    public function _before(FunctionalTester $I)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('user_photos')->truncate();
        DB::table('invitations')->truncate();
        DB::table('user_profiles')->truncate();
        DB::table('users')->truncate();

        Artisan::call('db:seed', ['--class' => 'CitySeeder']);
        Artisan::call('db:seed', ['--class' => 'InvitationTypeSeeder']);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function _after(FunctionalTester $I)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('user_photos')->truncate();
        DB::table('invitations')->truncate();
        DB::table('user_profiles')->truncate();
        DB::table('users')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        Mockery::close();
    }

    private function createSeededProfile(int $userId, int $cityId): User
    {
        $user = User::create([
            'registration_step' => 5,
            'is_seeded' => true,
        ]);

        UserProfile::create([
            'user_id' => $user->id,
            'name' => 'Test User',
            'description' => 'Test description',
            'birth_date' => now()->subYears(25),
            'gender' => 'male',
            'city_id' => $cityId,
            'country_id' => 1,
            'approved_at' => now(),
        ]);

        Invitation::create([
            'user_id' => $user->id,
            'type_id' => 1,
            'description' => 'Test invitation',
            'location' => 'Test location',
            'distance' => 10,
            'city_id' => $cityId,
            'approved_by' => $user->id,
        ]);

        return $user;
    }

    public function testIndexShowsSeededProfiles(FunctionalTester $I)
    {
        $I->wantTo('проверить что /seeded отображает сидированные профили');

        $this->createSeededProfile(1, $this->cityId);

        $I->amOnPage('/seeded');
        $I->seeResponseCodeIs(200);
        $I->see('Сидированные профили');
        $I->see('Test User');
    }

    public function testIndexFiltersByCity(FunctionalTester $I)
    {
        $I->wantTo('проверить фильтрацию по городу');

        $this->createSeededProfile(1, $this->cityId);
        $this->createSeededProfile(2, 2);

        $I->amOnPage('/seeded?city_id='.$this->cityId);
        $I->seeResponseCodeIs(200);
    }

    public function testDestroyAllDeletesSeededData(FunctionalTester $I)
    {
        $I->wantTo('проверить удаление всех сидированных данных');

        Storage::fake('s3');

        $user1 = $this->createSeededProfile(1, $this->cityId);
        $user2 = $this->createSeededProfile(2, $this->cityId);

        UserPhoto::create([
            'user_id' => $user1->id,
            'file_path' => 'users/1/photos/test.jpg',
            'position' => 0,
        ]);
        UserPhoto::create([
            'user_id' => $user2->id,
            'file_path' => 'users/2/photos/test.jpg',
            'position' => 0,
        ]);

        $I->assertEquals(2, User::where('is_seeded', true)->count());
        $I->assertEquals(2, UserPhoto::count());

        // Вызываем контроллер напрямую
        $controller = app(SeededController::class);
        $controller->destroyAll();

        $I->assertEquals(0, User::where('is_seeded', true)->count());
        $I->assertEquals(0, UserPhoto::count());
    }

    public function testDestroyByCityDeletesOnlyCityData(FunctionalTester $I)
    {
        $I->wantTo('проверить удаление данных по конкретному городу');

        Storage::fake('s3');

        $user1 = $this->createSeededProfile(1, $this->cityId);
        $user2 = $this->createSeededProfile(2, 2);

        UserPhoto::create([
            'user_id' => $user1->id,
            'file_path' => 'users/1/photos/test.jpg',
            'position' => 0,
        ]);
        UserPhoto::create([
            'user_id' => $user2->id,
            'file_path' => 'users/2/photos/test.jpg',
            'position' => 0,
        ]);

        $I->assertEquals(2, User::where('is_seeded', true)->count());

        // Вызываем контроллер напрямую
        $controller = app(SeededController::class);
        $request = SeedProfileDestroyByCityRequest::create('/api/seeded/city', 'DELETE', ['city_id' => $this->cityId]);
        $request->headers->set('Accept', 'application/json');
        $controller->destroyByCity($request);

        $I->assertEquals(1, User::where('is_seeded', true)->count());
        $I->assertEquals(1, UserPhoto::count());
    }

    public function testShowAvatarReturns404ForMissingFile(FunctionalTester $I)
    {
        $I->wantTo('проверить 404 для несуществующего аватара');

        Storage::fake('s3');

        $I->amOnPage('/storage/avatars/nonexistent/path.jpg');
        $I->seeResponseCodeIs(404);
    }

    public function testShowAvatarReturnsImage(FunctionalTester $I)
    {
        $I->wantTo('проверить отдачу аватара');

        Storage::fake('s3');
        $imageContent = 'fake-image-data';
        $path = 'users/1/photos/test.jpg';
        Storage::disk('s3')->put($path, $imageContent);

        $I->amOnPage('/storage/avatars/'.$path);
        $I->seeResponseCodeIs(200);
        $I->see($imageContent);
    }
}
