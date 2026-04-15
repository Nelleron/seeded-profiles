<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Invitation\InvitationTemplateService;
use Codeception\Test\Unit;

/**
 * Unit-тесты для сервисов сидирования.
 */
class SeedingServiceTest extends Unit
{
    public function test_get_type_slug_mapping(): void
    {
        $service = new InvitationTemplateService(
            templates: ['coffee' => ['t1'], 'bar' => ['t2'], 'sport' => ['t3'], 'walk' => ['t4'], 'culture' => ['t5']],
            locations: ['coffee' => ['l1'], 'bar' => ['l2'], 'sport' => ['l3'], 'walk' => ['l4'], 'culture' => ['l5']],
        );

        $expectedSlugs = [
            1 => 'coffee',
            2 => 'bar',
            3 => 'sport',
            4 => 'walk',
            5 => 'culture',
        ];

        foreach ($expectedSlugs as $typeId => $expectedSlug) {
            $slug = $service->getTypeSlug($typeId, 5);
            $this->assertEquals($expectedSlug, $slug);
        }
    }

    public function test_type_sequence_is_correct(): void
    {
        $service = new InvitationTemplateService(
            templates: ['coffee' => ['t1'], 'bar' => ['t2'], 'sport' => ['t3'], 'walk' => ['t4'], 'culture' => ['t5']],
            locations: [],
        );

        $expectedPattern = [1, 2, 3, 4, 5, 1, 2, 3, 4, 5, 1, 2, 3, 4, 5];

        foreach ($expectedPattern as $expected) {
            $typeId = $service->getNextTypeId(5);
            $this->assertEquals($expected, $typeId);
        }
    }
}
