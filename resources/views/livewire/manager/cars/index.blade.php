<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

        {{-- Фильтры --}}
        <div class="bg-white p-4 rounded shadow">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <div class="md:col-span-2">
                    <label class="text-xs text-gray-500">Поиск</label>
                    <input
                        type="text"
                        wire:model.live="q"
                        placeholder="марка / модель / VIN / госномер"
                        class="mt-1 w-full rounded border-gray-300"
                    />
                </div>

                <div>
                    <label class="text-xs text-gray-500">Статус</label>
                    <select wire:model.live="status" class="mt-1 w-full rounded border-gray-300">
                        <option value="">Все</option>
                        @foreach($statuses as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs text-gray-500">Марка</label>
                    <select wire:model.live="brand" class="mt-1 w-full rounded border-gray-300">
                        <option value="">Все</option>
                        @foreach($brands as $b)
                        <option value="{{ $b }}">{{ $b }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs text-gray-500">Год</label>
                    <select wire:model.live="year" class="mt-1 w-full rounded border-gray-300">
                        <option value="">Все</option>
                        @foreach($years as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-3 flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    Найдено: <b>{{ $cars->total() }}</b>
                </div>

                <div class="flex items-center gap-3">
                    <select wire:model.live="perPage" class="rounded border-gray-300 text-sm">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>

                    <button wire:click="clearFilters" class="px-3 py-2 rounded border text-sm">
                        Сбросить
                    </button>
                </div>
            </div>
        </div>

        {{-- Таблица --}}
        <div class="bg-white rounded shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3">Авто</th>
                        <th class="text-left px-4 py-3">VIN</th>
                        <th class="text-left px-4 py-3">Госномер</th>
                        <th class="text-left px-4 py-3">Год</th>
                        <th class="text-left px-4 py-3">Цена/день</th>
                        <th class="text-left px-4 py-3">Статус</th>
                        <th class="text-left px-4 py-3">Действия</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y">
                    @forelse($cars as $car)
                    <tr class="hover:bg-gray-50">

                        <td class="px-4 py-3">
                            <div class="font-medium">
                                {{ $car->brand }} {{ $car->model }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $car->fuel_type ?? '—' }}
                                • {{ $car->transmission ?? '—' }}
                                • {{ number_format((int)$car->mileage_km) }} км
                            </div>
                        </td>

                        <td class="px-4 py-3 font-mono text-xs">{{ $car->vin }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $car->plate_number }}</td>
                        <td class="px-4 py-3">{{ $car->year }}</td>
                        <td class="px-4 py-3">{{ number_format((float)$car->daily_price, 2, '.', ' ') }} ₽</td>
                        <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs border">
                                    {{ $statuses[$car->status] ?? $car->status }}
                                </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('manager.cars.edit', $car) }}"
                                   class="px-3 py-1.5 rounded border text-xs hover:bg-gray-50">
                                    Изменить
                                </a>

                                <a href="{{ route('manager.cars.show', $car) }}"
                                   class="px-3 py-1.5 rounded bg-gray-800 text-white text-xs hover:opacity-90">
                                    Подробнее
                                </a>
                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            Автомобили не найдены
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4">
                {{ $cars->links() }}
            </div>
        </div>

    </div>
</div>
