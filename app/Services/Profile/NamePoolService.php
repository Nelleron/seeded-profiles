<?php

declare(strict_types=1);

namespace App\Services\Profile;

/**
 * Управление пулом имён для сидирования.
 * Гарантирует уникальность имён в рамках одного запуска.
 */
class NamePoolService
{
    /** @var array<int, string> */
    private array $usedMaleNames = [];

    /** @var array<int, string> */
    private array $usedFemaleNames = [];

    /**
     * @param  array<int, string>  $maleNames  Пул мужских имён
     * @param  array<int, string>  $femaleNames  Пул женских имён
     */
    public function __construct(
        private readonly array $maleNames,
        private readonly array $femaleNames,
    ) {}

    /**
     * Создать сервис с данными из конфигурации.
     */
    public static function fromConfig(): self
    {
        return new self(
            maleNames: config('seeding.male_names'),
            femaleNames: config('seeding.female_names'),
        );
    }

    /**
     * Получить уникальное мужское имя.
     */
    public function getUniqueMaleName(): string
    {
        return $this->getUniqueName($this->maleNames, $this->usedMaleNames);
    }

    /**
     * Получить уникальное женское имя.
     */
    public function getUniqueFemaleName(): string
    {
        return $this->getUniqueName($this->femaleNames, $this->usedFemaleNames);
    }

    /**
     * Сбросить состояние пула имён.
     */
    public function reset(): void
    {
        $this->usedMaleNames = [];
        $this->usedFemaleNames = [];
    }

    /**
     * @param  array<int, string>  $names  Пул имён
     * @param  array<int, string>  $usedNames  Массив использованных имён (по ссылке)
     */
    private function getUniqueName(array $names, array &$usedNames): string
    {
        $availableNames = array_diff($names, $usedNames);

        if (empty($availableNames)) {
            $usedNames = [];
            $availableNames = $names;
        }

        $name = $availableNames[array_rand($availableNames)];
        $usedNames[] = $name;

        return $name;
    }
}
