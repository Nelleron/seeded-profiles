<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Invitation\InvitationTemplateService;
use Codeception\Test\Unit;

/**
 * Unit-тесты для InvitationTemplateService.
 */
class InvitationTemplateServiceTest extends Unit
{
    private static array $testTemplates = [
        'coffee' => ['Кофе 1', 'Кофе 2', 'Кофе 3'],
        'bar' => ['Бар 1', 'Бар 2', 'Бар 3'],
        'sport' => ['Спорт 1', 'Спорт 2', 'Спорт 3'],
        'walk' => ['Прогулка 1', 'Прогулка 2', 'Прогулка 3'],
        'culture' => ['Культура 1', 'Культура 2', 'Культура 3'],
    ];

    private static array $testLocations = [
        'coffee' => ['Кофейня 1', 'Кофейня 2'],
        'bar' => ['Бар 1', 'Бар 2'],
        'sport' => ['Спортзал 1', 'Спортзал 2'],
        'walk' => ['Парк 1', 'Парк 2'],
        'culture' => ['Музей 1', 'Музей 2'],
    ];

    private function createService(): InvitationTemplateService
    {
        return new InvitationTemplateService(self::$testTemplates, self::$testLocations);
    }

    public function test_invitation_types_are_sequential(): void
    {
        $service = $this->createService();
        $totalTypes = 5;
        $expectedSequence = [1, 2, 3, 4, 5, 1, 2, 3, 4, 5, 1];

        foreach ($expectedSequence as $expected) {
            $typeId = $service->getNextTypeId($totalTypes);
            $this->assertEquals($expected, $typeId);
        }
    }

    public function test_invitation_types_cycle(): void
    {
        $service = $this->createService();
        $totalTypes = 5;

        for ($i = 0; $i < 5; $i++) {
            $service->getNextTypeId($totalTypes);
        }

        $typeId = $service->getNextTypeId($totalTypes);
        $this->assertEquals(1, $typeId, 'После 5 должен идти 1');
    }

    public function test_get_type_slug_returns_correct_slug(): void
    {
        $service = $this->createService();
        $totalTypes = 5;
        $expectedSlugs = [
            1 => 'coffee',
            2 => 'bar',
            3 => 'sport',
            4 => 'walk',
            5 => 'culture',
        ];

        foreach ($expectedSlugs as $typeId => $expectedSlug) {
            $slug = $service->getTypeSlug($typeId, $totalTypes);
            $this->assertEquals($expectedSlug, $slug);
        }
    }

    public function test_reset_resets_type_index(): void
    {
        $service = $this->createService();
        $totalTypes = 5;

        $service->getNextTypeId($totalTypes);
        $service->getNextTypeId($totalTypes);
        $service->getNextTypeId($totalTypes);
        $service->reset();

        $typeId = $service->getNextTypeId($totalTypes);
        $this->assertEquals(1, $typeId, 'После сброса тип должен начинаться с 1');
    }

    public function test_get_unique_text_returns_unique_texts(): void
    {
        $service = $this->createService();
        $totalTypes = 5;
        $usedTexts = [];

        for ($i = 0; $i < 5; $i++) {
            $typeId = $service->getNextTypeId($totalTypes);
            $text = $service->getUniqueText($typeId, $totalTypes);

            $this->assertNotEmpty($text, 'Текст не должен быть пустым');
            $this->assertNotContains($text, $usedTexts);
            $usedTexts[] = $text;
        }

        $this->assertCount(5, $usedTexts, 'Должно быть 5 уникальных текстов');
    }

    public function test_get_random_location_returns_from_pool(): void
    {
        $service = $this->createService();
        $totalTypes = 5;

        for ($typeId = 1; $typeId <= 5; $typeId++) {
            $slug = $service->getTypeSlug($typeId, $totalTypes);
            $location = $service->getRandomLocation($typeId, $totalTypes);

            $this->assertNotEmpty($location, 'Локация не должна быть пустой');
            $this->assertContains($location, self::$testLocations[$slug]);
        }
    }
}
