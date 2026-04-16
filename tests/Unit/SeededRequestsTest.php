<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Requests\Seeded\SeedProfileDestroyByCityRequest;
use App\Http\Requests\Seeded\SeedProfileIndexRequest;
use Tests\TestCase;

class SeededRequestsTest extends TestCase
{
    public function test_seed_profile_index_request_rules()
    {
        $request = new SeedProfileIndexRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('city_id', $rules);
        $this->assertContains('nullable', $rules['city_id']);
        $this->assertContains('integer', $rules['city_id']);
        $this->assertContains('min:1', $rules['city_id']);
    }

    public function test_seed_profile_index_request_authorize()
    {
        $request = new SeedProfileIndexRequest;
        $this->assertTrue($request->authorize());
    }

    public function test_seed_profile_destroy_by_city_request_rules()
    {
        $request = new SeedProfileDestroyByCityRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('city_id', $rules);
        $this->assertContains('required', $rules['city_id']);
        $this->assertContains('integer', $rules['city_id']);
        $this->assertContains('min:1', $rules['city_id']);
    }

    public function test_seed_profile_destroy_by_city_request_authorize()
    {
        $request = new SeedProfileDestroyByCityRequest;
        $this->assertTrue($request->authorize());
    }
}
