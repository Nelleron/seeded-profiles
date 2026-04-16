<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Seeded\SeedProfileDestroyByCityRequest;
use App\Http\Requests\Seeded\SeedProfileIndexRequest;
use App\Http\Resources\Seeded\SeedProfileResource;
use App\Models\City;
use App\Models\Invitation;
use App\Models\User;
use App\Models\UserPhoto;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SeededController extends Controller
{
    public function index(SeedProfileIndexRequest $request): View
    {
        $query = UserProfile::with(['user', 'city', 'user.photos'])
            ->whereHas('user', fn ($q) => $q->where('is_seeded', true));

        if ($request->filled('city_id')) {
            $query->where('city_id', (int) $request->city_id);
        }

        $profiles = $query->orderByDesc('created_at')->get();
        $cities = City::whereHas('profiles', fn ($q) => $q->whereHas('user', fn ($u) => $u->where('is_seeded', true)))
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('seeded', compact('profiles', 'cities'));
    }

    public function indexApi(SeedProfileIndexRequest $request): AnonymousResourceCollection
    {
        $query = UserProfile::with(['user', 'city', 'user.photos'])
            ->whereHas('user', fn ($q) => $q->where('is_seeded', true));

        if ($request->filled('city_id')) {
            $query->where('city_id', (int) $request->city_id);
        }

        $profiles = $query->orderByDesc('created_at')->get();

        return SeedProfileResource::collection($profiles);
    }

    public function destroyAll(): JsonResponse|RedirectResponse
    {
        $seededUsers = User::where('is_seeded', true)
            ->with('photos')
            ->get();

        $count = $seededUsers->count();

        DB::transaction(function () use ($seededUsers) {
            $this->deletePhotosFromStorage($seededUsers);
            $this->deleteSeededRelations($seededUsers);

            User::where('is_seeded', true)->delete();
        });

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'deleted' => $count]);
        }

        return redirect()->back()->with('flash', [
            'type' => 'success',
            'message' => "Удалено {$count} профилей",
        ]);
    }

    public function destroyByCity(SeedProfileDestroyByCityRequest $request): JsonResponse|RedirectResponse
    {
        $cityId = (int) $request->city_id;
        $cityName = config("seeding.available_cities.{$cityId}", "Город {$cityId}");

        $seededUsers = User::where('is_seeded', true)
            ->whereHas('profile', fn ($q) => $q->where('city_id', $cityId))
            ->with('photos')
            ->get();

        $count = $seededUsers->count();

        DB::transaction(function () use ($seededUsers) {
            $this->deletePhotosFromStorage($seededUsers);
            $this->deleteSeededRelations($seededUsers);

            User::whereIn('id', $seededUsers->pluck('id'))->delete();
        });

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'deleted' => $count]);
        }

        return redirect()->back()->with('flash', [
            'type' => 'success',
            'message' => "Удалено {$count} профилей из {$cityName}",
        ]);
    }

    public function showAvatar(string $path): Response
    {
        $decodedPath = urldecode($path);

        if (! Storage::disk('s3')->exists($decodedPath)) {
            abort(404);
        }

        $content = Storage::disk('s3')->get($decodedPath);
        $mimeType = Storage::disk('s3')->mimeType($decodedPath);

        return response($content, 200, ['Content-Type' => $mimeType]);
    }

    private function deletePhotosFromStorage($users): void
    {
        foreach ($users as $user) {
            $userPhotos = $user->photos;
            foreach ($userPhotos as $photo) {
                if (! empty($photo->file_path)) {
                    Storage::disk('s3')->delete($photo->file_path);
                }
            }
        }
    }

    private function deleteSeededRelations($users): void
    {
        $userIds = $users->pluck('id');

        UserPhoto::whereIn('user_id', $userIds)->delete();
        UserProfile::whereIn('user_id', $userIds)->delete();
        Invitation::whereIn('user_id', $userIds)->delete();
    }
}
