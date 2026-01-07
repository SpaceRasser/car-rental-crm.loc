<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-500">Автомобиль</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $car->brand }} {{ $car->model }} ({{ $car->year }})
                </h2>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('manager.cars.index') }}" class="px-3 py-2 rounded border text-sm">
                    ← Назад
                </a>
                <a href="{{ route('manager.cars.edit', $car) }}" class="px-3 py-2 rounded bg-gray-800 text-white text-sm">
                    Изменить
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Основная информация --}}
            <div class="bg-white rounded shadow p-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">VIN:</span> <span class="font-mono">{{ $car->vin }}</span></div>
                    <div><span class="text-gray-500">Госномер:</span> <span class="font-mono">{{ $car->plate_number }}</span></div>

                    <div><span class="text-gray-500">Цвет:</span> {{ $car->color ?? '—' }}</div>
                    <div><span class="text-gray-500">Пробег:</span> {{ number_format((int)$car->mileage_km) }} км</div>

                    <div><span class="text-gray-500">Топливо:</span> {{ $car->fuel_type ?? '—' }}</div>
                    <div><span class="text-gray-500">КПП:</span> {{ $car->transmission ?? '—' }}</div>

                    <div><span class="text-gray-500">Цена/день:</span> {{ number_format((float)$car->daily_price, 2, '.', ' ') }} ₽</div>
                    <div><span class="text-gray-500">Депозит:</span> {{ number_format((float)$car->deposit_amount, 2, '.', ' ') }} ₽</div>

                    <div><span class="text-gray-500">Статус:</span> {{ $car->status }}</div>
                    <div><span class="text-gray-500">Активен:</span> {{ $car->is_active ? 'Да' : 'Нет' }}</div>
                </div>

                @if($car->description)
                <div class="mt-4">
                    <div class="text-xs text-gray-500">Описание</div>
                    <div class="text-sm mt-1 whitespace-pre-line">{{ $car->description }}</div>
                </div>
                @endif
            </div>

            <livewire:manager.cars.photos :carId="$car->id" />


        </div>
    </div>
</x-app-layout>
