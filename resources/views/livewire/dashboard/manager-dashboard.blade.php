<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded shadow">
                <div class="text-sm text-gray-500">Новые заявки</div>
                <div class="text-2xl font-semibold">{{ $stats['new_rentals'] }}</div>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <div class="text-sm text-gray-500">Активные аренды</div>
                <div class="text-2xl font-semibold">{{ $stats['active_rentals'] }}</div>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <div class="text-sm text-gray-500">Просрочки</div>
                <div class="text-2xl font-semibold">{{ $stats['overdue'] }}</div>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <div class="text-sm text-gray-500">Тест-драйвы сегодня</div>
                <div class="text-2xl font-semibold">{{ $stats['td_today'] }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="bg-white rounded shadow">
                <div class="p-4 border-b font-semibold">Выдачи сегодня</div>
                <div class="p-4 space-y-3">
                    @forelse($pickupsToday as $r)
                    <div class="text-sm">
                        <div class="font-medium">{{ $r->car?->brand }} {{ $r->car?->model }}</div>
                        <div class="text-xs text-gray-500">
                            {{ $r->client?->full_name ?? '—' }} • {{ optional($r->starts_at)->format('d.m H:i') }} • {{ $r->status }}
                        </div>
                    </div>
                    @empty
                    <div class="text-sm text-gray-500">Нет выдач</div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded shadow">
                <div class="p-4 border-b font-semibold">Возвраты сегодня</div>
                <div class="p-4 space-y-3">
                    @forelse($returnsToday as $r)
                    <div class="text-sm">
                        <div class="font-medium">{{ $r->car?->brand }} {{ $r->car?->model }}</div>
                        <div class="text-xs text-gray-500">
                            {{ $r->client?->full_name ?? '—' }} • {{ optional($r->ends_at)->format('d.m H:i') }} • {{ $r->status }}
                        </div>
                    </div>
                    @empty
                    <div class="text-sm text-gray-500">Нет возвратов</div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded shadow">
                <div class="p-4 border-b font-semibold">Тест-драйвы (7 дней)</div>
                <div class="p-4 space-y-3">
                    @forelse($testDrivesSoon as $t)
                    <div class="text-sm">
                        <div class="font-medium">{{ $t->car?->brand }} {{ $t->car?->model }}</div>
                        <div class="text-xs text-gray-500">
                            {{ $t->client?->full_name ?? '—' }} • {{ optional($t->scheduled_at)->format('d.m H:i') }} • {{ $t->status }}
                        </div>
                    </div>
                    @empty
                    <div class="text-sm text-gray-500">Нет записей</div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>
