<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-500">Каталог для аренды</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $car->brand }} {{ $car->model }} ({{ $car->year }})
                </h2>
            </div>
            <a href="{{ route('client.catalog.rentals') }}" class="px-3 py-2 rounded border text-sm">← Назад</a>
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
                            <div><span class="text-gray-500">Цена/день:</span> {{ number_format((float)$car->daily_price, 2, '.', ' ') }} ₽</div>
                            <div><span class="text-gray-500">Депозит:</span> {{ number_format((float)$car->deposit_amount, 2, '.', ' ') }} ₽</div>
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
                <div class="font-semibold mb-4">Забронировать автомобиль</div>
                <form method="post" action="{{ route('client.catalog.rentals.book', $car) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    @csrf
                    <div>
                        <label class="text-xs text-gray-500">Начало аренды</label>
                        <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" class="mt-1 w-full rounded border-gray-300" />
                        @error('starts_at') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Окончание аренды</label>
                        <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" class="mt-1 w-full rounded border-gray-300" />
                        @error('ends_at') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500">Комментарий к заявке</label>
                        <textarea name="notes" rows="3" class="mt-1 w-full rounded border-gray-300">{{ old('notes') }}</textarea>
                        @error('notes') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="md:col-span-2 bg-gray-50 rounded p-3">
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="checkbox" name="use_trusted_person" value="1" @checked(old('use_trusted_person')) class="rounded border-gray-300" />
                            Доверенное лицо
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3">
                            <div>
                                <label class="text-xs text-gray-500">ФИО</label>
                                <input type="text" name="trusted_person_name" value="{{ old('trusted_person_name') }}" class="mt-1 w-full rounded border-gray-300" />
                                @error('trusted_person_name') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Телефон</label>
                                <input type="text" name="trusted_person_phone" data-mask="phone" value="{{ old('trusted_person_phone') }}" class="mt-1 w-full rounded border-gray-300" />
                                @error('trusted_person_phone') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">№ водительского удостоверения</label>
                                <input type="text" name="trusted_person_license_number" data-mask="license" value="{{ old('trusted_person_license_number') }}" class="mt-1 w-full rounded border-gray-300" />
                                @error('trusted_person_license_number') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="px-4 py-2 rounded bg-gray-800 text-white text-sm">Отправить заявку</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
