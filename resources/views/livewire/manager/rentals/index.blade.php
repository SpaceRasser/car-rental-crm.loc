<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

        {{-- Фильтры --}}
        <div class="bg-white p-4 rounded shadow">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <div class="md:col-span-2">
                    <label class="text-xs text-gray-500">Поиск</label>
                    <input wire:model.live="q"
                           class="mt-1 w-full rounded border-gray-300"
                           placeholder="клиент / телефон / авто / VIN / госномер / ID" />
                </div>

                <div>
                    <label class="text-xs text-gray-500">Статус</label>
                    <select wire:model.live="status" class="mt-1 w-full rounded border-gray-300">
                        <option value="">Все</option>
                        @foreach($statuses as $k => $label)
                        <option value="{{ $k }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs text-gray-500">С</label>
                    <input type="date" wire:model.live="from" class="mt-1 w-full rounded border-gray-300" />
                </div>

                <div>
                    <label class="text-xs text-gray-500">По</label>
                    <input type="date" wire:model.live="to" class="mt-1 w-full rounded border-gray-300" />
                </div>

                <div class="flex items-end gap-2">
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

            <div class="mt-3 text-sm text-gray-500">
                Найдено: <b>{{ $rentals->total() }}</b>
            </div>
        </div>

        {{-- Таблица --}}
        <div class="bg-white rounded shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3">ID</th>
                        <th class="text-left px-4 py-3">Клиент</th>
                        <th class="text-left px-4 py-3">Авто</th>
                        <th class="text-left px-4 py-3">Период</th>
                        <th class="text-left px-4 py-3">Сумма</th>
                        <th class="text-left px-4 py-3">Статус</th>
                        <th class="text-left px-4 py-3">Действия</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y">
                    @forelse($rentals as $r)
                    @php
                        $group = $r->group_uuid ? ($groupTotals[$r->group_uuid] ?? null) : null;
                        $carLabel = trim(($r->car?->brand ?? '').' '.($r->car?->model ?? ''));
                        $carCount = $group['cars_count'] ?? 1;
                    @endphp
                    <tr wire:key="rental-{{ $r->id }}" class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">#{{ $r->id }}</td>

                        <td class="px-4 py-3">
                            <div class="font-medium">
                                {{ $r->client?->full_name ?? trim(($r->client?->last_name ?? '').' '.($r->client?->first_name ?? '')) ?: '—' }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $r->client?->phone ?? '—' }}
                            </div>
                        </td>

                        <td class="px-4 py-3">
                            <div class="font-medium">
                                {{ $carLabel ?: '—' }}
                            </div>
                            <div class="text-xs text-gray-500 font-mono">
                                {{ $r->car?->plate_number ?? '—' }}
                            </div>
                            @if($carCount > 1)
                                <div class="text-xs text-gray-500">+ ещё {{ $carCount - 1 }} авто</div>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <div class="text-sm">
                                {{ optional($r->starts_at)->format('d.m.Y H:i') ?? '—' }}
                            </div>
                            <div class="text-xs text-gray-500">
                                → {{ optional($r->ends_at)->format('d.m.Y H:i') ?? '—' }}
                            </div>
                        </td>

                        <td class="px-4 py-3">
                            @php
                            $total = (float) ($group['total'] ?? 0);
                            if (! $group) {
                                $rent = (float) ($r->grand_total ?? $r->base_total ?? 0);
                                $deposit = (float) ($r->deposit_amount ?? 0);
                                $total = $rent + $deposit;
                            }
                            @endphp

                            {{ $total > 0 ? number_format($total, 2, '.', ' ') . ' ₽' : '—' }}
                        </td>


                        <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs border">
                                    {{ $statuses[$r->status] ?? $r->status }}
                                </span>
                        </td>

                        <td class="px-4 py-3">
                            <a href="{{ route('manager.rentals.show', $r) }}"
                               class="px-3 py-1.5 rounded bg-gray-800 text-white text-xs hover:opacity-90">
                                Подробнее
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            Аренды не найдены
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4">
                {{ $rentals->links() }}
            </div>
        </div>

    </div>
</div>
