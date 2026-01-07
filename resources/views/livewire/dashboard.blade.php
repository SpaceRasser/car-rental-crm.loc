<div class="py-6" wire:poll.30s>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="bg-white rounded shadow p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="text-lg font-semibold">Панель управления</div>
                    <div class="text-sm text-gray-500">Сводка по арендам и тест-драйвам</div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('manager.clients.create') }}" class="px-3 py-2 rounded border text-sm">+ Клиент</a>
                    <a href="{{ route('manager.cars.create') }}" class="px-3 py-2 rounded border text-sm">+ Авто</a>
                    <a href="{{ route('manager.rentals.create') }}" class="px-3 py-2 rounded bg-gray-800 text-white text-sm">+ Аренда</a>
                    <a href="{{ route('manager.test-drives.create') }}" class="px-3 py-2 rounded bg-gray-800 text-white text-sm">+ Тест-драйв</a>
                </div>
            </div>
        </div>

        {{-- KPI --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded shadow p-5">
                <div class="text-sm text-gray-500">Аренды</div>
                <div class="mt-2 space-y-1">
                    <div>Новые: <b>{{ $rentalsNew }}</b></div>
                    <div>Активные/подтверждённые: <b>{{ $rentalsActive }}</b></div>
                    <div>Просроченные: <b class="text-red-600">{{ $rentalsOverdue }}</b></div>
                </div>
            </div>

            <div class="bg-white rounded shadow p-5">
                <div class="text-sm text-gray-500">Финансы</div>
                <div class="mt-2">
                    <div class="text-2xl font-semibold">
                        {{ number_format((float)$unpaidTotal, 2, '.', ' ') }} ₽
                    </div>
                    <div class="text-xs text-gray-500">Неоплачено по активным арендам</div>
                </div>
            </div>

            <div class="bg-white rounded shadow p-5">
                <div class="text-sm text-gray-500">База</div>
                <div class="mt-2 space-y-1">
                    <div>Авто: <b>{{ $carsTotal }}</b> <span class="text-xs text-gray-500">(активных {{ $carsActive }})</span></div>
                    <div>Клиенты: <b>{{ $clientsTotal }}</b> <span class="text-xs text-gray-500">(новых за 7 дней {{ $clientsNewWeek }})</span></div>
                </div>
            </div>
        </div>

        {{-- Ближайшие тест-драйвы --}}
        <div class="bg-white rounded shadow p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="font-semibold">Ближайшие тест-драйвы (7 дней)</div>
                <a href="{{ route('manager.test-drives.index') }}" class="text-sm underline">Открыть список</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3">Дата</th>
                        <th class="text-left px-4 py-3">Клиент</th>
                        <th class="text-left px-4 py-3">Авто</th>
                        <th class="text-left px-4 py-3">Действие</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y">
                    @forelse($testDrivesUpcoming as $t)
                    <tr>
                        <td class="px-4 py-3">{{ optional($t->scheduled_at)->format('d.m.Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $t->client?->full_name ?? '—' }}</div>
                            <div class="text-xs text-gray-500">{{ $t->client?->phone ?? '—' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            {{ $t->car?->brand }} {{ $t->car?->model }}
                            <div class="text-xs text-gray-500 font-mono">{{ $t->car?->plate_number ?? '—' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('manager.test-drives.show', $t) }}" class="px-3 py-1.5 rounded bg-gray-800 text-white text-xs">
                                Подробнее
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                            Нет ближайших тест-драйвов
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Аренды заканчиваются --}}
        <div class="bg-white rounded shadow p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="font-semibold">Аренды, которые скоро заканчиваются (3 дня)</div>
                <a href="{{ route('manager.rentals.index') }}" class="text-sm underline">Открыть список</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3">ID</th>
                        <th class="text-left px-4 py-3">Клиент</th>
                        <th class="text-left px-4 py-3">Авто</th>
                        <th class="text-left px-4 py-3">Окончание</th>
                        <th class="text-left px-4 py-3">Действие</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y">
                    @forelse($rentalsEndingSoon as $r)
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs">#{{ $r->id }}</td>
                        <td class="px-4 py-3">{{ $r->client?->full_name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            {{ $r->car?->brand }} {{ $r->car?->model }}
                            <div class="text-xs text-gray-500 font-mono">{{ $r->car?->plate_number ?? '—' }}</div>
                        </td>
                        <td class="px-4 py-3">{{ optional($r->ends_at)->format('d.m.Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('manager.rentals.show', $r) }}" class="px-3 py-1.5 rounded bg-gray-800 text-white text-xs">
                                Подробнее
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                            Нет аренды, заканчивающейся в ближайшие 3 дня
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Топ неоплаченных --}}
        <div class="bg-white rounded shadow p-5">
            <div class="font-semibold mb-3">Топ неоплаченных аренд</div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3">ID</th>
                        <th class="text-left px-4 py-3">Клиент</th>
                        <th class="text-left px-4 py-3">Авто</th>
                        <th class="text-left px-4 py-3">Остаток</th>
                        <th class="text-left px-4 py-3">Действие</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y">
                    @forelse($unpaidTop as $r)
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs">#{{ $r->id }}</td>
                        <td class="px-4 py-3">{{ $r->client?->full_name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $r->car?->brand }} {{ $r->car?->model }}</td>
                        <td class="px-4 py-3">
                            <b>{{ number_format((float)($r->remaining ?? 0), 2, '.', ' ') }} ₽</b>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('manager.rentals.show', $r) }}" class="px-3 py-1.5 rounded bg-gray-800 text-white text-xs">
                                Открыть
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                            Все аренды оплачены 🎉
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
