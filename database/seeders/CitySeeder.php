<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            ['name' => 'Дананг', 'country' => 'Вьетнам'],
            ['name' => 'Нячанг', 'country' => 'Вьетнам'],
            ['name' => 'Хошимин', 'country' => 'Вьетнам'],
            ['name' => 'Батуми', 'country' => 'Грузия'],
            ['name' => 'Тбилиси', 'country' => 'Грузия'],
            ['name' => 'Астана', 'country' => 'Казахстан'],
            ['name' => 'Алматы', 'country' => 'Казахстан'],
            ['name' => 'Бангкок', 'country' => 'Таиланд'],
            ['name' => 'Паттайа', 'country' => 'Таиланд'],
            ['name' => 'Анталия', 'country' => 'Турция'],
            ['name' => 'Алания', 'country' => 'Турция'],
            ['name' => 'Мерсин', 'country' => 'Турция'],
            ['name' => 'Ереван', 'country' => 'Армения'],
            ['name' => 'Лимассол', 'country' => 'Кипр'],
            ['name' => 'Ларнака', 'country' => 'Кипр'],
            ['name' => 'Пафос', 'country' => 'Кипр'],
            ['name' => 'Бали', 'country' => 'Индонезия'],
            ['name' => 'Подгорица', 'country' => 'Черногория'],
            ['name' => 'Будва', 'country' => 'Черногория'],
            ['name' => 'Белград', 'country' => 'Сербия'],
            ['name' => 'Нови Сад', 'country' => 'Сербия'],
            ['name' => 'Шанхай', 'country' => 'Китай'],
            ['name' => 'Пекин', 'country' => 'Китай'],
            ['name' => 'Гуанчжоу', 'country' => 'Китай'],
            ['name' => 'Варшава', 'country' => 'Польша'],
            ['name' => 'Краков', 'country' => 'Польша'],
            ['name' => 'Вроцлав', 'country' => 'Польша'],
            ['name' => 'Барселона', 'country' => 'Испания'],
            ['name' => 'Мадрид', 'country' => 'Испания'],
            ['name' => 'Валенсия', 'country' => 'Испания'],
            ['name' => 'Гоа', 'country' => 'Индия'],
            ['name' => 'Дели', 'country' => 'Индия'],
            ['name' => 'Мумбаи', 'country' => 'Индия'],
            ['name' => 'Манила', 'country' => 'Филиппины'],
            ['name' => 'Себу', 'country' => 'Филиппины'],
            ['name' => 'Давао', 'country' => 'Филиппины'],
        ];
        foreach ($cities as $city) {
            City::updateOrCreate(['name' => $city['name']], $city);
        }
    }
}
