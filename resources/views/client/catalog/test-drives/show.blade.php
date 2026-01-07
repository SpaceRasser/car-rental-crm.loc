<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-500">Каталог для тест-драйва</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $car->brand }} {{ $car->model }} ({{ $car->year }})
                </h2>
            </div>
            <a href="{{ route('client.catalog.test-drives') }}" class="px-3 py-2 rounded border text-sm">← Назад</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('booking_success'))
                <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded text-sm">
                    {{ session('booking_success') }}
                </div>
            @endif

            @if (session('booking_error'))
                <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded text-sm">
                    {{ session('booking_error') }}
                </div>
            @endif

            <div class="bg-white rounded shadow p-5 space-y-4">
                @php
                    $mainPhoto = $car->mainPhoto ?? $car->photos->first();
                @endphp
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="space-y-3">
                        <div class="aspect-[4/3] bg-gray-100 rounded overflow-hidden">
                            @if($mainPhoto)
                                <img src="{{ asset('storage/'.$mainPhoto->path) }}" alt="{{ $car->brand }} {{ $car->model }}" class="w-full h-full object-cover" />
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">Нет фото</div>
                            @endif
                        </div>
                        @if($car->photos->isNotEmpty())
                            <div class="grid grid-cols-3 gap-2">
                                @foreach($car->photos as $photo)
                                    <div class="aspect-[4/3] bg-gray-100 rounded overflow-hidden">
                                        <img src="{{ asset('storage/'.$photo->path) }}" alt="" class="w-full h-full object-cover" />
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="space-y-4 text-sm">
                        <div class="grid grid-cols-1 gap-2">
                            <div><span class="text-gray-500">Топливо:</span> {{ $car->fuel_type ?? '—' }}</div>
                            <div><span class="text-gray-500">КПП:</span> {{ $car->transmission ?? '—' }}</div>
                            <div><span class="text-gray-500">Цвет:</span> {{ $car->color ?? '—' }}</div>
                            <div><span class="text-gray-500">Пробег:</span> {{ number_format((int)$car->mileage_km) }} км</div>
                        </div>

                        @if($car->description)
                            <div>
                                <div class="text-xs text-gray-500">Описание</div>
                                <div class="text-sm mt-1 whitespace-pre-line">{{ $car->description }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white rounded shadow p-5">
                <div class="font-semibold mb-4">Записаться на тест-драйв</div>
                <form method="post" action="{{ route('client.catalog.test-drives.book', $car) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    @csrf
                    <div>
                        <label class="text-xs text-gray-500">Дата и время</label>
                        <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="mt-1 w-full rounded border-gray-300" />
                        @error('scheduled_at') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Длительность</label>
                        <select name="duration_minutes" class="mt-1 w-full rounded border-gray-300">
                            @foreach([30, 45, 60, 90, 120] as $minutes)
                                <option value="{{ $minutes }}" @selected(old('duration_minutes', 30) == $minutes)>{{ $minutes }} мин</option>
                            @endforeach
                        </select>
                        @error('duration_minutes') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500">Комментарий к заявке</label>
                        <textarea name="notes" rows="3" class="mt-1 w-full rounded border-gray-300">{{ old('notes') }}</textarea>
                        @error('notes') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="px-4 py-2 rounded bg-gray-800 text-white text-sm">Отправить заявку</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
