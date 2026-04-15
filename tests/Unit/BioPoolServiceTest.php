<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Profile\BioPoolService;
use Codeception\Test\Unit;

/**
 * Unit-тесты для BioPoolService.
 * Проверка уникальности описаний профиля.
 */
class BioPoolServiceTest extends Unit
{
    private static array $testDescriptions = [
        'IT специалист, люблю путешествовать.',
        'Дизайнер по профессии, занимаюсь йогой.',
        'Работаю в маркетинге, провожу время на природе.',
        'Фрилансер, увлекаюсь фотографией.',
        'HR-специалист, люблю спорт.',
    ];

    private function createService(): BioPoolService
    {
        return new BioPoolService(self::$testDescriptions);
    }

    public function test_descriptions_are_unique(): void
    {
        $service = $this->createService();
        $usedDescriptions = [];
        $poolSize = count(self::$testDescriptions);

        for ($i = 0; $i < $poolSize; $i++) {
            $description = $service->getUniqueDescription();
            $this->assertNotContains($description, $usedDescriptions, 'Описание повторяется');
            $usedDescriptions[] = $description;
        }

        $this->assertCount($poolSize, $usedDescriptions, 'Должны быть использованы все описания из пула');
    }

    public function test_description_is_not_empty(): void
    {
        $service = $this->createService();

        for ($i = 0; $i < count(self::$testDescriptions); $i++) {
            $description = $service->getUniqueDescription();
            $this->assertNotEmpty($description, 'Описание не должно быть пустым');
        }
    }

    public function test_pool_resets_when_exhausted(): void
    {
        $service = $this->createService();
        $poolSize = count(self::$testDescriptions);

        for ($i = 0; $i < $poolSize; $i++) {
            $service->getUniqueDescription();
        }

        $description = $service->getUniqueDescription();
        $this->assertNotEmpty($description, 'После исчерпания пула должен произойти сброс');
        $this->assertContains($description, self::$testDescriptions);
    }
}
