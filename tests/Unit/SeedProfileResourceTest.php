<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\Gender;
use App\Http\Resources\Seeded\SeedProfileResource;
use App\Models\User;
use App\Models\UserPhoto;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeedProfileResourceTest extends TestCase
{
    private UserProfile $profile;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('user_photos')->truncate();
        DB::table('invitations')->truncate();
        DB::table('user_profiles')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        Artisan::call('db:seed', ['--class' => 'CitySeeder']);

        $user = User::create([
            'registration_step' => 5,
            'is_seeded' => true,
        ]);

        $this->profile = UserProfile::create([
            'user_id' => $user->id,
            'name' => 'Иван',
            'description' => 'Тестовое описание',
            'birth_date' => now()->subYears(25),
            'gender' => Gender::Male,
            'city_id' => 1,
            'country_id' => 1,
            'approved_at' => now(),
        ]);

        UserPhoto::create([
            'user_id' => $user->id,
            'file_path' => 'users/1/photos/test.jpg',
            'position' => 0,
        ]);
    }

    public function test_resource_contains_required_fields()
    {
        $resource = new SeedProfileResource($this->profile);
        $array = $resource->resolve(Request::capture());

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('user_id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('birth_date', $array);
        $this->assertArrayHasKey('gender', $array);
    }

    public function test_resource_contains_city_when_loaded()
    {
        $this->profile->load('city');
        $resource = new SeedProfileResource($this->profile);
        $array = $resource->resolve(Request::capture());

        $this->assertArrayHasKey('city', $array);
    }

    public function test_resource_contains_photos_when_loaded()
    {
        $this->profile->load('user.photos');
        $resource = new SeedProfileResource($this->profile);
        $array = $resource->resolve(Request::capture());

        $this->assertArrayHasKey('photos', $array);
    }

    public function test_resource_gender_value()
    {
        $resource = new SeedProfileResource($this->profile);
        $array = $resource->resolve(Request::capture());

        $this->assertEquals('male', $array['gender']);
    }
}
