<?php

declare(strict_types=1);

namespace App\Http\Resources\Seeded;

use App\Models\City;
use App\Models\User;
use App\Models\UserPhoto;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property UserProfile $resource
 * @property-read User|null $user
 * @property-read City|null $city
 */
class SeedProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $profile = $this->resource;

        return [
            'id' => $profile->id,
            'user_id' => $profile->user_id,
            'name' => $profile->name,
            'description' => $profile->description,
            'birth_date' => $profile->birth_date?->toDateString(),
            'gender' => $profile->gender->value,
            'city' => $this->whenLoaded('city', function () use ($profile): array {
                assert($profile->city instanceof City);

                return [
                    'id' => $profile->city->id,
                    'name' => $profile->city->name,
                ];
            }),
            'photos' => ($profile->relationLoaded('user') && $profile->user?->relationLoaded('photos'))
                ? (function () use ($profile) {
                    assert($profile->user instanceof User);
                    /** @var Collection<int, UserPhoto> $photos */
                    $photos = $profile->user->photos;

                    return $photos->map(fn (UserPhoto $photo) => [
                        'id' => $photo->id,
                        'url' => url('/storage/avatars/'.urlencode($photo->file_path)),
                    ]);
                })()
                : null,
            'created_at' => $profile->created_at?->toIso8601String(),
        ];
    }
}
