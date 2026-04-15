<?php

declare(strict_types=1);

namespace App\Services\Profile;

/**
 * Управление пулом описаний профиля (био) для сидирования.
 * Гарантирует уникальность био в рамках одного запуска.
 */
class BioPoolService
{
    /** @var array<int, string> */
    private array $usedDescriptions = [];

    /**
     * @param  array<int, string>  $descriptions  Пул описаний
     */
    public function __construct(
        private readonly array $descriptions,
    ) {}

    /**
     * Создать сервис с данными из конфигурации.
     */
    public static function fromConfig(): self
    {
        return new self(
            descriptions: config('seeding.profiles_description'),
        );
    }

    /**
     * Получить уникальное описание профиля.
     */
    public function getUniqueDescription(): string
    {
        $availableDescriptions = array_diff($this->descriptions, $this->usedDescriptions);

        if (empty($availableDescriptions)) {
            $this->usedDescriptions = [];
            $availableDescriptions = $this->descriptions;
        }

        $description = $availableDescriptions[array_rand($availableDescriptions)];
        $this->usedDescriptions[] = $description;

        return $description;
    }

    /**
     * Сбросить состояние пула описаний.
     */
    public function reset(): void
    {
        $this->usedDescriptions = [];
    }
}
