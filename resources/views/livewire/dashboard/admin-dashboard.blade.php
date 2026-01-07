<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        {{-- Карточки --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded shadow">
                <div class="text-sm text-gray-500">Авто всего</div>
                <div class="text-2xl font-semibold">{{ $stats['cars_total'] }}</div>
                <div class="mt-2 text-xs text-gray-500">
                    Доступно: <b>{{ $stats['cars_available'] }}</b> • В аренде: <b>{{ $stats['cars_rented'] }}</b> • Сервис: <b>{{ $stats['cars_maintenance'] }}</b>
                </div>
            </div>

            <div class="bg-white p-4 rounded shadow">
                <div class="text-sm text-gray-500">Аренды (активные)</div>
                <div class="text-2xl font-semibold">{{ $stats['rentals_active'] }}</div>
                <div class="mt-2 text-xs text-gray-500">
                    Новые: <b>{{ $stats['rentals_new'] }}</b> • Просрочки: <b>{{ $stats['rentals_overdue'] }}</b>
                </div>
            </div>

            <div class="bg-white p-4 rounded shadow">
                <div class="text-sm text-gray-500">Тест-драйвы сегодня</div>
                <div class="text-2xl font-semibold">{{ $stats['test_drives_today'] }}</div>
                <div class="mt-2 text-xs text-gray-500">Только новые/подтверждённые</div>
            </div>

            <div class="bg-white p-4 rounded shadow">
                <div class="text-sm text-gray-500">Доход за месяц (оплачено)</div>
                <div class="text-2xl font-semibold">{{ number_format($stats['income_month'], 2, '.', ' ') }} ₽</div>
                <div class="mt-2 text-xs text-gray-500">Только payments.status = paid</div>
            </div>
        </div>

        {{-- Ближайшие события --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="bg-white rounded shadow">
                <div class="p-4 border-b font-semibold">Ближайшие аренды</div>
                <div class="p-4 space-y-3">
                    @forelse($upcomingRentals as $r)
                    <div class="text-sm">
                        <div class="font-medium">
                            {{ $r->car?->brand }} {{ $r->car?->model }}
                            <span class="text-xs text-gray-500">• {{ $r->status }}</span>
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $r->client?->full_name ?? '—' }} • {{ optional($r->starts_at)->format('d.m H:i') }} → {{ optional($r->ends_at)->format('d.m H:i') }}
                        </div>
                    </div>
                    @empty
                    <div class="text-sm text-gray-500">Нет данных</div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded shadow">
                <div class="p-4 border-b font-semibold">Тест-драйвы (7 дней)</div>
                <div class="p-4 space-y-3">
                    @forelse($upcomingTestDrives as $t)
                    <div class="text-sm">
                        <div class="font-medium">
                            {{ $t->car?->brand }} {{ $t->car?->model }}
                            <span class="text-xs text-gray-500">• {{ $t->status }}</span>
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $t->client?->full_name ?? '—' }} • {{ optional($t->scheduled_at)->format('d.m H:i') }} ({{ $t->duration_minutes }} мин)
                        </div>
                    </div>
                    @empty
                    <div class="text-sm text-gray-500">Нет данных</div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded shadow">
                <div class="p-4 border-b font-semibold">Последние платежи</div>
                <div class="p-4 space-y-3">
                    @forelse($latestPayments as $p)
                    <div class="text-sm">
                        <div class="font-medium">
                            {{ number_format((float)$p->amount, 2, '.', ' ') }} {{ $p->currency ?? 'RUB' }}
                            <span class="text-xs text-gray-500">• {{ $p->status }} • {{ $p->provider }}</span>
                        </div>
                        <div class="text-xs text-gray-500 font-mono">
                            {{ $p->payment_reference }}
                        </div>
                    </div>
                    @empty
                    <div class="text-sm text-gray-500">Нет данных</div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>
