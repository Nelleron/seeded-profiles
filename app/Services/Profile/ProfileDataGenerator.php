<?php

declare(strict_types=1);

namespace App\Services\Profile;

use App\Enums\Gender;
use Carbon\Carbon;

/**
 * Генератор данных профиля для сидирования.
 * Генерирует возраст, дистанцию и другие числовые данные.
 */
class ProfileDataGenerator
{
    private const MIN_AGE = 22;

    private const MAX_AGE = 34;

    private const MIN_DISTANCE = 2.0;

    private const MAX_DISTANCE = 7.0;

    /**
     * Сгенерировать случайную дату рождения для возраста 22-34 года.
     * Гарантирует, что возраст будет в диапазоне MIN_AGE..MAX_AGE.
     */
    public function generateBirthDate(): Carbon
    {
        // Границы: минимальный возраст = MAX_AGE, максимальный = MIN_AGE
        // Чтобы возраст был >= 22, дата рождения должна быть <= сегодня - 22 года
        // Чтобы возраст был <= 34, дата рождения должна быть >= сегодня - 34 года - 1 день

        $maxBirthDate = now()->subYears(self::MIN_AGE)->startOfDay();
        $minBirthDate = now()->subYears(self::MAX_AGE + 1)->addDay()->startOfDay();

        return Carbon::createFromTimestamp(
            random_int($minBirthDate->timestamp, $maxBirthDate->timestamp)
        );
    }

    /**
     * Сгенерировать случайную дистанцию от 2.0 до 7.0 км.
     */
    public function generateDistance(): float
    {
        return round(random_int((int) (self::MIN_DISTANCE * 10), (int) (self::MAX_DISTANCE * 10)) / 10, 1);
    }

    /**
     * Получить случайный пол.
     */
    public function getRandomGender(): Gender
    {
        return random_int(0, 1) === 0 ? Gender::Male : Gender::Female;
    }

    /**
     * Проверить валидность возраста для сидирования.
     *
     * @param  \Carbon\CarbonInterface  $birthDate  Дата рождения
     * @return bool true если возраст в диапазоне 22-34
     */
    public function isValidAge(\Carbon\CarbonInterface $birthDate): bool
    {
        $age = (int) $birthDate->diffInYears(now());

        return $age >= self::MIN_AGE && $age <= self::MAX_AGE;
    }
}
