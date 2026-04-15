<?php

declare(strict_types=1);

namespace App\Services\Invitation;

/**
 * Сервис управления шаблонами приглашений для генерации данных.
 */
class InvitationTemplateService
{
    /** @var array<string, array<int, string>> */
    private array $usedTextsByType = [];

    /** @var array<string, array<int, string>> */
    private array $usedLocationsByType = [];

    private int $typeIndex = 0;

    /** @var array<int, string> */
    private const TYPE_SLUGS = ['coffee', 'bar', 'sport', 'walk', 'culture'];

    /**
     * @param  array<string, array<int, string>>  $templates  Шаблоны для типов
     * @param  array<string, array<int, string>>  $locations  Локации для типов
     */
    public function __construct(
        private readonly array $templates,
        private readonly array $locations,
    ) {}

    /**
     * Создание сервиса из конфигурации.
     */
    public static function fromConfig(): self
    {
        return new self(
            templates: config('seeding.invitation_templates'),
            locations: config('seeding.invitation_locations'),
        );
    }

    /**
     * Получить ID следующего типа приглашения (циклически).
     */
    public function getNextTypeId(int $totalTypes): int
    {
        $typeId = ($this->typeIndex % $totalTypes) + 1;
        $this->typeIndex++;

        return $typeId;
    }

    /**
     * Получить уникальный текст приглашения для указанного типа.
     */
    public function getUniqueText(int $typeId, int $totalTypes): string
    {
        $slug = $this->getTypeSlug($typeId, $totalTypes);
        $texts = $this->templates[$slug] ?? [];

        if (! isset($this->usedTextsByType[$slug])) {
            $this->usedTextsByType[$slug] = [];
        }

        $availableTexts = array_diff($texts, $this->usedTextsByType[$slug]);

        if (empty($availableTexts)) {
            $this->usedTextsByType[$slug] = [];
            $availableTexts = $texts;
        }

        $text = $availableTexts[array_rand($availableTexts)];
        $this->usedTextsByType[$slug][] = $text;

        return $text;
    }

    /**
     * Получить уникальную локацию для указанного типа приглашения.
     */
    public function getRandomLocation(int $typeId, int $totalTypes): string
    {
        $slug = $this->getTypeSlug($typeId, $totalTypes);
        $locations = $this->locations[$slug] ?? ['Городское место встречи'];

        if (! isset($this->usedLocationsByType[$slug])) {
            $this->usedLocationsByType[$slug] = [];
        }

        $availableLocations = array_diff($locations, $this->usedLocationsByType[$slug]);

        if (empty($availableLocations)) {
            $this->usedLocationsByType[$slug] = [];
            $availableLocations = $locations;
        }

        $location = $availableLocations[array_rand($availableLocations)];
        $this->usedLocationsByType[$slug][] = $location;

        return $location;
    }

    /**
     * Получить slug типа по его ID.
     */
    public function getTypeSlug(int $typeId, int $totalTypes): string
    {
        $position = ($typeId - 1) % count(self::TYPE_SLUGS);

        return self::TYPE_SLUGS[$position];
    }

    /**
     * Сбросить историю использованных шаблонов.
     */
    public function reset(): void
    {
        $this->usedTextsByType = [];
        $this->usedLocationsByType = [];
        $this->typeIndex = 0;
    }
}
