<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Каталог для аренды</h2>
            <div class="text-sm text-gray-500">Доступно: {{ $cars->total() }}</div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white rounded shadow p-4">
                <form method="get" action="{{ route('client.catalog.rentals') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3 text-sm">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Марка, модель, госномер"
                        class="md:col-span-2 rounded border-gray-300"
                    />

                    <select name="brand" class="rounded border-gray-300">
                        <option value="">Все марки</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand }}" @selected(request('brand') === $brand)>{{ $brand }}</option>
                        @endforeach
                    </select>

                    <select name="transmission" class="rounded border-gray-300">
                        <option value="">Все КПП</option>
                        @foreach($transmissions as $transmission)
                            <option value="{{ $transmission }}" @selected(request('transmission') === $transmission)>{{ $transmission }}</option>
                        @endforeach
                    </select>

                    <select name="fuel_type" class="rounded border-gray-300">
                        <option value="">Все типы топлива</option>
                        @foreach($fuelTypes as $fuelType)
                            <option value="{{ $fuelType }}" @selected(request('fuel_type') === $fuelType)>{{ $fuelType }}</option>
                        @endforeach
                    </select>

                    <input
                        type="number"
                        name="price_min"
                        value="{{ request('price_min') }}"
                        placeholder="Цена от"
                        class="rounded border-gray-300"
                        min="0"
                    />

                    <input
                        type="number"
                        name="price_max"
                        value="{{ request('price_max') }}"
                        placeholder="Цена до"
                        class="rounded border-gray-300"
                        min="0"
                    />

                    <div class="md:col-span-6 flex items-center justify-between gap-3">
                        <button type="submit" class="px-4 py-2 rounded bg-gray-800 text-white text-sm">Показать</button>
                        <a href="{{ route('client.catalog.rentals') }}" class="text-xs text-gray-500">Сбросить фильтры</a>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($cars as $car)
                    @php
                        $photoPath = $car->mainPhoto?->path;
                    @endphp
                    <div class="relative bg-white rounded shadow overflow-hidden group">
                        <a href="{{ route('client.catalog.rentals.show', $car) }}" class="absolute inset-0 z-0" aria-label="Открыть карточку авто"></a>
                        <div class="relative z-10">
                            <div class="aspect-[4/3] bg-gray-100 overflow-hidden">
                                @if($photoPath)
                                    <img src="{{ asset('storage/'.$photoPath) }}" alt="{{ $car->brand }} {{ $car->model }}" class="w-full h-full object-cover transition group-hover:scale-105" />
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">Нет фото</div>
                                @endif
                            </div>
                            <div class="p-4 space-y-2">
                                <div class="font-semibold text-gray-800">{{ $car->brand }} {{ $car->model }} ({{ $car->year }})</div>
                                <div class="text-xs text-gray-500">
                                    {{ $car->transmission ?? '—' }} • {{ $car->fuel_type ?? '—' }} • {{ number_format((int)$car->mileage_km) }} км
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="text-lg font-semibold">{{ number_format((float)$car->daily_price, 2, '.', ' ') }} ₽/сутки</div>
                                    <a href="{{ route('client.catalog.rentals.show', $car) }}" class="relative z-20 px-3 py-1.5 rounded bg-gray-800 text-white text-xs">
                                        Забронировать
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center text-gray-500 py-10">
                        Сейчас нет доступных автомобилей для аренды.
                    </div>
                @endforelse
            </div>

            <div>
                {{ $cars->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
