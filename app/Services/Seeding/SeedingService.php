<?php

declare(strict_types=1);

namespace App\Services\Seeding;

use App\Enums\Gender;
use App\Models\Invitation;
use App\Models\InvitationType;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\Invitation\InvitationTemplateService;
use App\Services\Profile\BioPoolService;
use App\Services\Profile\NamePoolService;
use App\Services\Profile\ProfileDataGenerator;
use Illuminate\Support\Facades\DB;

/**
 * Основной сервис для генерации фейковых данных.
 */
class SeedingService
{
    public function __construct(
        private NamePoolService $namePool,
        private BioPoolService $bioPool,
        private InvitationTemplateService $invitationTemplate,
        private ProfileDataGenerator $dataGenerator,
    ) {}

    /**
     * Создать полностью заполненный профиль с инвитейшеном.
     */
    public function createSeededProfile(int $cityId): array
    {
        $totalInvitationTypes = InvitationType::count();

        return DB::transaction(function () use ($cityId, $totalInvitationTypes) {
            $gender = $this->dataGenerator->getRandomGender();
            $name = $gender === Gender::Male
                ? $this->namePool->getUniqueMaleName()
                : $this->namePool->getUniqueFemaleName();

            $birthDate = $this->dataGenerator->generateBirthDate();
            $description = $this->bioPool->getUniqueDescription();

            $user = User::create([
                'registration_step' => 5,
                'is_seeded' => true,
            ]);

            $profile = UserProfile::create([
                'user_id' => $user->id,
                'name' => $name,
                'description' => $description,
                'birth_date' => $birthDate,
                'gender' => $gender,
                'city_id' => $cityId,
                'country_id' => $this->getCountryIdByCity($cityId),
                'approved_at' => now(),
            ]);

            $typeId = $this->invitationTemplate->getNextTypeId($totalInvitationTypes);
            $typeSlug = $this->invitationTemplate->getTypeSlug($typeId, $totalInvitationTypes);

            $invitation = Invitation::create([
                'user_id' => $user->id,
                'type_id' => $typeId,
                'description' => $this->invitationTemplate->getUniqueText($typeId, $totalInvitationTypes),
                'location' => $this->invitationTemplate->getRandomLocation($typeId, $totalInvitationTypes),
                'distance' => $this->dataGenerator->generateDistance(),
                'city_id' => $cityId,
                'approved_by' => $user->id,
            ]);

            return [
                'user' => $user,
                'profile' => $profile,
                'invitation' => $invitation,
                'data' => [
                    'user_id' => $user->id,
                    'name' => $name,
                    'city' => $this->getCityName($cityId),
                    'invitation_type' => ucfirst($typeSlug),
                    'gender' => $gender,
                    'birth_date' => $birthDate,
                ],
            ];
        });
    }

    public function reset(): void
    {
        $this->namePool->reset();
        $this->bioPool->reset();
        $this->invitationTemplate->reset();
    }

    public function getCityName(int $cityId): string
    {
        return config("seeding.available_cities.{$cityId}", 'Неизвестный город');
    }

    public function isValidCity(int $cityId): bool
    {
        return isset(config('seeding.available_cities')[$cityId]);
    }

    public function getAvailableCities(): array
    {
        return config('seeding.available_cities');
    }

    /**
     * Получить ID страны для города согласно ТЗ.
     */
    public function getCountryIdByCity(int $cityId): int
    {
        return match (true) {
            $cityId <= 3 => 1,  // Вьетнам
            $cityId <= 5 => 2,  // Грузия
            $cityId <= 7 => 3,  // Казахстан
            $cityId <= 9 => 4,  // Таиланд
            $cityId <= 12 => 5, // Турция
            $cityId === 13 => 6, // Армения
            $cityId <= 16 => 7, // Кипр
            $cityId === 17 => 8, // Индонезия (Бали)
            $cityId <= 19 => 9, // Черногория
            $cityId <= 21 => 10, // Сербия
            $cityId <= 24 => 11, // Китай
            $cityId <= 27 => 12, // Польша
            $cityId <= 30 => 13, // Испания
            $cityId <= 33 => 14, // Индия
            $cityId <= 36 => 15, // Филиппины
            default => 1,
        };
    }
}
