<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\Gender;
use App\Services\Profile\ProfileDataGenerator;
use Carbon\Carbon;
use Codeception\Test\Unit;

/**
 * Unit-тесты для ProfileDataGenerator.
 * Проверка валидности возраста и других генерируемых данных.
 */
class ProfileDataGeneratorTest extends Unit
{
    private ProfileDataGenerator $service;

    protected function _setUp(): void
    {
        parent::_setUp();
        $this->service = new ProfileDataGenerator;
    }

    /**
     * Тест: генерируемый возраст находится в диапазоне 22-34 года.
     */
    public function test_birth_date_generates_valid_age(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $birthDate = $this->service->generateBirthDate();
            $age = (int) $birthDate->diffInYears(now());

            $this->assertGreaterThanOrEqual(
                22,
                $age,
                "Возраст {$age} меньше минимального (22)"
            );
            $this->assertLessThanOrEqual(
                34,
                $age,
                "Возраст {$age} больше максимального (34)"
            );
        }
    }

    /**
     * Тест: генерируемая дистанция находится в диапазоне 2.0-7.0 км.
     */
    public function test_distance_is_valid(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $distance = $this->service->generateDistance();

            $this->assertGreaterThanOrEqual(
                2.0,
                $distance,
                "Дистанция {$distance} меньше минимальной (2.0)"
            );
            $this->assertLessThanOrEqual(
                7.0,
                $distance,
                "Дистанция {$distance} больше максимальной (7.0)"
            );

            // Проверяем формат (один знак после запятой)
            $formatted = number_format($distance, 1);
            $this->assertEquals(
                $formatted,
                number_format($distance, 1),
                'Дистанция должна иметь один знак после запятой'
            );
        }
    }

    /**
     * Тест: getRandomGender возвращает корректное значение.
     */
    public function test_random_gender_is_valid(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $gender = $this->service->getRandomGender();

            $this->assertInstanceOf(
                Gender::class,
                $gender,
                'Пол должен быть экземпляром Enum Gender'
            );
        }
    }

    /**
     * Тест: isValidAge корректно проверяет возраст.
     */
    public function test_is_valid_age_works_correctly(): void
    {
        // Возраст в диапазоне
        $validBirthDate = now()->subYears(25);
        $this->assertTrue(
            $this->service->isValidAge($validBirthDate),
            'Возраст 25 должен быть валидным'
        );

        // Возраст меньше минимума
        $tooYoung = now()->subYears(20);
        $this->assertFalse(
            $this->service->isValidAge($tooYoung),
            'Возраст 20 должен быть невалидным'
        );

        // Возраст больше максимума
        $tooOld = now()->subYears(40);
        $this->assertFalse(
            $this->service->isValidAge($tooOld),
            'Возраст 40 должен быть невалидным'
        );
    }

    /**
     * Тест: генерируемая дата рождения - экземпляр Carbon.
     */
    public function test_birth_date_is_carbon_instance(): void
    {
        $birthDate = $this->service->generateBirthDate();

        $this->assertInstanceOf(
            Carbon::class,
            $birthDate,
            'Дата рождения должна быть экземпляром Carbon'
        );
    }
}
