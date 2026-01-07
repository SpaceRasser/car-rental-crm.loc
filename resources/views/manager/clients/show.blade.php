<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-500">Клиент</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ trim($client->last_name.' '.$client->first_name.' '.$client->middle_name) }}
                </h2>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('manager.clients.index') }}" class="px-3 py-2 rounded border text-sm">← Назад</a>
                <a href="{{ route('manager.clients.edit', $client) }}" class="px-3 py-2 rounded bg-gray-800 text-white text-sm">
                    Изменить
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">

            <div class="bg-white rounded shadow p-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">Телефон:</span> {{ $client->phone ?? '—' }}</div>
                    <div><span class="text-gray-500">Email:</span> {{ $client->email ?? '—' }}</div>

                    <div><span class="text-gray-500">Дата рождения:</span> {{ $client->birth_date?->format('d.m.Y') ?? '—' }}</div>
                    <div><span class="text-gray-500">Проверен:</span> {{ $client->is_verified ? 'Да' : 'Нет' }}</div>

                    <div><span class="text-gray-500">Права:</span> {{ $client->driver_license_number ?? '—' }}</div>
                    <div><span class="text-gray-500">Надёжность:</span> {{ $client->reliability_status }}</div>

                    <div><span class="text-gray-500">Выданы:</span> {{ $client->driver_license_issued_at?->format('d.m.Y') ?? '—' }}</div>
                    <div><span class="text-gray-500">Действуют до:</span> {{ $client->driver_license_expires_at?->format('d.m.Y') ?? '—' }}</div>
                </div>

                @if($client->notes)
                <div class="mt-4">
                    <div class="text-xs text-gray-500">Заметки</div>
                    <div class="text-sm mt-1 whitespace-pre-line">{{ $client->notes }}</div>
                </div>
                @endif
            </div>

            <div class="bg-white rounded shadow p-5">
                <div class="font-semibold mb-3">Доверенное лицо и автомобили</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Доверенное лицо</div>
                        <div><span class="text-gray-500">ФИО:</span> {{ $client->trusted_person_name ?? '—' }}</div>
                        <div><span class="text-gray-500">Телефон:</span> {{ $client->trusted_person_phone ?? '—' }}</div>
                        <div><span class="text-gray-500">В/У:</span> {{ $client->trusted_person_license_number ?? '—' }}</div>
                    </div>
                    <div>
                        @php
                            $clientCars = $client->carAssignments->where('relation_type', 'client');
                            $trustedCars = $client->carAssignments->where('relation_type', 'trusted');
                        @endphp
                        <div class="text-xs text-gray-500 mb-1">Автомобили клиента</div>
                        @if($clientCars->isEmpty())
                            <div class="text-gray-500">Не указаны</div>
                        @else
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($clientCars as $assignment)
                                    <li>{{ $assignment->car?->brand }} {{ $assignment->car?->model }} • {{ $assignment->car?->plate_number ?? '—' }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="text-xs text-gray-500 mt-3 mb-1">Авто доверенного лица</div>
                        @if($trustedCars->isEmpty())
                            <div class="text-gray-500">Не указаны</div>
                        @else
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($trustedCars as $assignment)
                                    <li>{{ $assignment->car?->brand }} {{ $assignment->car?->model }} • {{ $assignment->car?->plate_number ?? '—' }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white rounded shadow p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="font-semibold">История аренд (последние 10)</div>

                    <a href="{{ route('manager.rentals.create', ['client_id' => $client->id]) }}"
                       class="px-3 py-2 rounded bg-gray-800 text-white text-sm">
                        + Создать аренду для клиента
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="text-left px-4 py-3">ID</th>
                            <th class="text-left px-4 py-3">Авто</th>
                            <th class="text-left px-4 py-3">Период</th>
                            <th class="text-left px-4 py-3">Статус</th>
                            <th class="text-left px-4 py-3">Сумма</th>
                            <th class="text-left px-4 py-3">Оплата</th>
                            <th class="text-left px-4 py-3">Действия</th>
                        </tr>
                        </thead>

                        <tbody class="divide-y">
                        @forelse($client->rentals as $r)
                        @php
                        $paid = $r->payments->where('status', 'paid')->sum('amount');
                        $total = (float)($r->total_price ?? 0);
                        $remain = max(0, $total - (float)$paid);
                        @endphp

                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">#{{ $r->id }}</td>

                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $r->car?->brand }} {{ $r->car?->model }}</div>
                                <div class="text-xs text-gray-500 font-mono">{{ $r->car?->plate_number ?? '—' }}</div>
                            </td>

                            <td class="px-4 py-3">
                                <div>{{ optional($r->starts_at)->format('d.m.Y H:i') ?? '—' }}</div>
                                <div class="text-xs text-gray-500">→ {{ optional($r->ends_at)->format('d.m.Y H:i') ?? '—' }}</div>
                            </td>

                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs border">{{ $r->status }}</span>
                            </td>

                            <td class="px-4 py-3">
                                {{ number_format((float)($r->total_price ?? 0), 2, '.', ' ') }} ₽
                            </td>

                            <td class="px-4 py-3 text-xs">
                                <div>Оплачено: <b>{{ number_format((float)$paid, 2, '.', ' ') }} ₽</b></div>
                                <div class="text-gray-500">Остаток: {{ number_format((float)$remain, 2, '.', ' ') }} ₽</div>
                            </td>

                            <td class="px-4 py-3">
                                <a href="{{ route('manager.rentals.show', $r) }}"
                                   class="px-3 py-1.5 rounded bg-gray-800 text-white text-xs">
                                    Подробнее
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                У клиента пока нет аренд
                            </td>
                        </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @php
            $tdStatus = [
            'new' => 'Ожидает подтверждения',
            'confirmed' => 'Подтверждён',
            'completed' => 'Завершён',
            'no_show' => 'Не пришёл',
            'cancelled' => 'Отменён',
            ];
            @endphp

            <div class="bg-white rounded shadow p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="font-semibold">История тест-драйвов (последние 10)</div>

                    <a href="{{ route('manager.test-drives.create', ['client_id' => $client->id]) }}"
                       class="px-3 py-2 rounded bg-gray-800 text-white text-sm">
                        + Создать тест-драйв для клиента
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="text-left px-4 py-3">ID</th>
                            <th class="text-left px-4 py-3">Дата/время</th>
                            <th class="text-left px-4 py-3">Авто</th>
                            <th class="text-left px-4 py-3">Статус</th>
                            <th class="text-left px-4 py-3">Менеджер</th>
                            <th class="text-left px-4 py-3">Действия</th>
                        </tr>
                        </thead>

                        <tbody class="divide-y">
                        @forelse($client->testDrives as $td)
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">#{{ $td->id }}</td>

                            <td class="px-4 py-3">
                                <div>{{ optional($td->scheduled_at)->format('d.m.Y H:i') ?? '—' }}</div>
                                <div class="text-xs text-gray-500">{{ $td->duration_minutes }} мин.</div>
                            </td>

                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $td->car?->brand }} {{ $td->car?->model }}</div>
                                <div class="text-xs text-gray-500 font-mono">{{ $td->car?->plate_number ?? '—' }}</div>
                            </td>

                            <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded text-xs border">
                            {{ $tdStatus[$td->status] ?? $td->status }}
                        </span>
                            </td>

                            <td class="px-4 py-3">
                                {{ $td->manager?->name ?? '—' }}
                            </td>

                            <td class="px-4 py-3">
                                <a href="{{ route('manager.test-drives.show', $td) }}"
                                   class="px-3 py-1.5 rounded bg-gray-800 text-white text-xs">
                                    Подробнее
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                У клиента пока нет тест-драйвов
                            </td>
                        </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
