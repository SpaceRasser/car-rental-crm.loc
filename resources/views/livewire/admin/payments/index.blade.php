<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

        @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 p-3 rounded">
            {{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 p-3 rounded">
            {{ session('error') }}
        </div>
        @endif

        {{-- Фильтры --}}
        <div class="bg-white p-4 rounded shadow">
            <div class="grid grid-cols-1 md:grid-cols-7 gap-3">
                <div class="md:col-span-2">
                    <label class="text-xs text-gray-500">Поиск</label>
                    <input wire:model.live="q"
                           class="mt-1 w-full rounded border-gray-300"
                           placeholder="payment ref / external / аренда / клиент / авто" />
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
                    <label class="text-xs text-gray-500">Провайдер</label>
                    <select wire:model.live="provider" class="mt-1 w-full rounded border-gray-300">
                        <option value="">Все</option>
                        @foreach($providers as $p)
                        <option value="{{ $p }}">{{ $p }}</option>
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

                    <button type="button" wire:click="clearFilters" class="px-3 py-2 rounded border text-sm">
                        Сбросить
                    </button>
                </div>
            </div>

            <div class="mt-3 text-sm text-gray-600 flex flex-wrap gap-4">
                <div>Оплачено (по фильтрам): <b>{{ number_format((float)$paidSum, 2, '.', ' ') }} ₽</b></div>
                <div>Ожидает (по фильтрам): <b>{{ number_format((float)$pendingSum, 2, '.', ' ') }} ₽</b></div>
                <div class="text-gray-400">•</div>
                <div>Найдено: <b>{{ $payments->total() }}</b></div>
            </div>
        </div>

        {{-- Таблица --}}
        <div class="bg-white rounded shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3">ID</th>
                        <th class="text-left px-4 py-3">Аренда</th>
                        <th class="text-left px-4 py-3">Клиент / Авто</th>
                        <th class="text-left px-4 py-3">Сумма</th>
                        <th class="text-left px-4 py-3">Статус</th>
                        <th class="text-left px-4 py-3">Reference</th>
                        <th class="text-left px-4 py-3">Действия</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y">
                    @forelse($payments as $p)
                    <tr wire:key="payment-{{ $p->id }}" class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">#{{ $p->id }}</td>

                        <td class="px-4 py-3">
                            @if($p->rental)
                            <a class="underline"
                               href="{{ route('manager.rentals.show', $p->rental) }}">
                                Аренда #{{ $p->rental->id }}
                            </a>
                            @else
                            —
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <div class="font-medium">
                                {{ $p->rental?->client?->full_name ?? '—' }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $p->rental?->car?->brand }} {{ $p->rental?->car?->model }}
                                <span class="font-mono">({{ $p->rental?->car?->plate_number ?? '—' }})</span>
                            </div>
                        </td>

                        <td class="px-4 py-3">
                            {{ number_format((float)$p->amount, 2, '.', ' ') }} {{ $p->currency ?? 'RUB' }}
                            <div class="text-xs text-gray-500">{{ $p->provider }}</div>
                        </td>

                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs border">{{ $statuses[$p->status] ?? $p->status }}</span>
                        </td>

                        <td class="px-4 py-3 font-mono text-xs">
                            {{ $p->payment_reference ?? '—' }}
                            <div class="text-[10px] text-gray-500">{{ $p->external_id ?? '—' }}</div>
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                @if($p->status !== 'paid')
                                <button type="button"
                                        wire:click="simulateSuccess({{ $p->id }})"
                                        class="px-3 py-1.5 rounded bg-gray-800 text-white text-xs">
                                    Успех
                                </button>

                                <button type="button"
                                        wire:click="simulateFail({{ $p->id }})"
                                        class="px-3 py-1.5 rounded border text-xs">
                                    Ошибка
                                </button>
                                @else
                                <span class="text-xs text-green-700">Оплачено</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            Платежей не найдено
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4">
                {{ $payments->links() }}
            </div>
        </div>

    </div>
</div>
