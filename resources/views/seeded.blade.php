<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seed Invitations — Seeded Profiles</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Сидированные профили</h1>

        @if(session('flash'))
            <div class="mb-4 p-4 rounded {{ session('flash.type') === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ session('flash.message') }}
            </div>
        @endif

        {{-- Фильтрация и действия --}}
        <div class="bg-white rounded-lg shadow p-4 mb-6 flex flex-wrap items-center gap-4">
            <form method="GET" action="{{ route('seeded.index') }}" class="flex items-center gap-2">
                <label for="city_id" class="font-medium">Город:</label>
                <select name="city_id" id="city_id" class="border rounded px-3 py-1">
                    <option value="">Все города</option>
                    @foreach($cities as $id => $name)
                        <option value="{{ $id }}" {{ (request('city_id') == $id) ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="bg-blue-500 text-white px-4 py-1 rounded hover:bg-blue-600">Фильтровать</button>
            </form>

            <button
                form="deleteAllForm"
                type="submit"
                class="bg-red-500 text-white px-4 py-1 rounded hover:bg-red-600 ml-auto"
                onclick="return confirm('Удалить все сидированные данные?')"
            >
                Удалить всё
            </button>

            <form id="deleteByCityForm" method="POST" class="flex items-center gap-2">
                @csrf
                @method('DELETE')
                <select name="city_id" id="delete_city_id" class="border rounded px-3 py-1">
                    <option value="">Выберите город</option>
                    @foreach($cities as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
                <button
                    type="submit"
                    class="bg-orange-500 text-white px-4 py-1 rounded hover:bg-orange-600"
                >
                    Удалить по городу
                </button>
            </form>
        </div>

        {{-- Hidden forms для DELETE --}}
        <form id="deleteAllForm" method="POST" action="{{ route('seeded.destroyAll') }}">
            @csrf
            @method('DELETE')
        </form>

        {{-- Профили --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($profiles as $profile)
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    {{-- Фото --}}
                    <div class="aspect-square bg-gray-200 relative">
                        @if($profile->user->photos->isNotEmpty())
                            <img
                                src="{{ url('/storage/avatars/' . $profile->user->photos->first()->file_path) }}"
                                alt="{{ $profile->name }}"
                                class="w-full h-full object-cover"
                            >
                        @else
                            <div class="flex items-center justify-center w-full h-full text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        @endif
                    </div>

                    {{-- Информация --}}
                    <div class="p-4">
                        <h3 class="text-lg font-semibold">{{ $profile->name }}</h3>
                        <p class="text-gray-500 text-sm mb-1">
                            {{ $profile->gender->value === 'male' ? 'М' : 'Ж' }},
                            {{ $profile->birth_date?->age ?? '?' }} лет
                        </p>
                        <p class="text-gray-600 text-sm mb-2">
                            📍 {{ $profile->city->name ?? 'Не указан' }}
                        </p>
                        <p class="text-gray-700 text-sm line-clamp-3">{{ $profile->description }}</p>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 text-gray-500">
                    Профилей не найдено
                </div>
            @endforelse
        </div>
    </div>

    <script>
        document.getElementById('deleteByCityForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const cityId = document.getElementById('delete_city_id').value;
            if (!cityId) {
                alert('Выберите город');
                return;
            }
            if (!confirm('Удалить данные по выбранному городу?')) {
                return;
            }
            this.action = '/seeded/city/' + cityId;
            this.submit();
        });
    </script>
</body>
</html>
