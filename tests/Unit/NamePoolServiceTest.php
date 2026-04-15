<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Profile\NamePoolService;
use Codeception\Test\Unit;

/**
 * Unit-тесты для NamePoolService.
 * Проверка уникальности имён и корректности работы пула.
 */
class NamePoolServiceTest extends Unit
{
    private static array $testMaleNames = [
        'Александр', 'Борис', 'Виктор', 'Геннадий', 'Дмитрий',
        'Евгений', 'Игорь', 'Иван', 'Кирилл', 'Максим',
    ];

    private static array $testFemaleNames = [
        'Анна', 'Белла', 'Виктория', 'Галина', 'Дарья',
        'Елена', 'Жанна', 'Зоя', 'Ирина', 'Карина',
    ];

    private function createService(): NamePoolService
    {
        return new NamePoolService(self::$testMaleNames, self::$testFemaleNames);
    }

    public function test_male_names_are_unique(): void
    {
        $service = $this->createService();
        $names = [];
        $poolSize = count(self::$testMaleNames);

        for ($i = 0; $i < $poolSize; $i++) {
            $name = $service->getUniqueMaleName();
            $this->assertNotContains($name, $names, "Имя '{$name}' повторяется");
            $names[] = $name;
        }

        $this->assertCount($poolSize, $names, 'Должны быть использованы все имена из пула');
    }

    public function test_female_names_are_unique(): void
    {
        $service = $this->createService();
        $names = [];
        $poolSize = count(self::$testFemaleNames);

        for ($i = 0; $i < $poolSize; $i++) {
            $name = $service->getUniqueFemaleName();
            $this->assertNotContains($name, $names, "Имя '{$name}' повторяется");
            $names[] = $name;
        }

        $this->assertCount($poolSize, $names, 'Должны быть использованы все имена из пула');
    }

    public function test_reset_allows_name_reuse(): void
    {
        $service = $this->createService();
        $firstName = $service->getUniqueMaleName();
        $service->reset();
        $secondName = $service->getUniqueMaleName();

        $this->assertNotEmpty($secondName, 'После сброса должно вернуться имя');
        $this->assertContains($secondName, self::$testMaleNames);
    }

    public function test_pool_resets_when_exhausted(): void
    {
        $service = $this->createService();
        $poolSize = count(self::$testMaleNames);

        for ($i = 0; $i < $poolSize; $i++) {
            $service->getUniqueMaleName();
        }

        $name = $service->getUniqueMaleName();
        $this->assertNotEmpty($name, 'После исчерпания пула должен произойти сброс');
        $this->assertContains($name, self::$testMaleNames);
    }
}
