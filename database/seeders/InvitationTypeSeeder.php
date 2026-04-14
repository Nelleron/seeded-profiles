<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\InvitationType;
use Illuminate\Database\Seeder;

class InvitationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['slug' => 'coffee', 'name' => 'Кофе / чай', 'emoji' => '☕', 'sort_order' => 10],
            ['slug' => 'bar', 'name' => 'Бар', 'emoji' => '🍺', 'sort_order' => 20],
            ['slug' => 'sport', 'name' => 'Спорт', 'emoji' => '⚽', 'sort_order' => 30],
            ['slug' => 'walk', 'name' => 'Прогулка', 'emoji' => '🚶', 'sort_order' => 40],
            ['slug' => 'culture', 'name' => 'Культура', 'emoji' => '🎭', 'sort_order' => 50],
        ];
        foreach ($types as $type) {
            InvitationType::updateOrCreate(['slug' => $type['slug']], $type);
        }
    }
}
