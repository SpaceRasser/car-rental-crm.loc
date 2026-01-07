<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

        <div class="bg-white p-4 rounded shadow">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <div class="md:col-span-2">
                    <label class="text-xs text-gray-500">Поиск</label>
                    <input wire:model.live="q" class="mt-1 w-full rounded border-gray-300"
                           placeholder="клиент / телефон / авто / VIN / госномер / ID" />
                </div>

                <div>
                    <label class="text-xs text-gray-500">Статус</label>
                    <select wire:model.live="status" class="mt-1 w-full rounded border-gray-300">
                        <option value="">Все</option>
                        <option value="new">Ожидает подтверждения</option>
                        <option value="confirmed">Подтверждён</option>
                        <option value="completed">Завершён</option>
                        <option value="no_show">Не пришёл</option>
                        <option value="cancelled">Отменён</option>
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
        </div>

        <div class="bg-white rounded shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3">ID</th>
                        <th class="text-left px-4 py-3">Дата/время</th>
                        <th class="text-left px-4 py-3">Клиент</th>
                        <th class="text-left px-4 py-3">Авто</th>
                        <th class="text-left px-4 py-3">Статус</th>
                        <th class="text-left px-4 py-3">Действия</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y">
                    @forelse($items as $t)
                    <tr class="hover:bg-gray-50" wire:key="td-{{ $t->id }}">
                        <td class="px-4 py-3 font-mono text-xs">#{{ $t->id }}</td>

                        <td class="px-4 py-3">
                            {{ optional($t->scheduled_at)->format('d.m.Y H:i') ?? '—' }}
                        </td>

                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $t->client?->full_name ?? '—' }}</div>
                            <div class="text-xs text-gray-500">{{ $t->client?->phone ?? '—' }}</div>
                        </td>

                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $t->car?->brand }} {{ $t->car?->model }}</div>
                            <div class="text-xs text-gray-500 font-mono">{{ $t->car?->plate_number ?? '—' }}</div>
                        </td>

                        <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs border">
                                    {{ $statuses[$t->status] ?? $t->status }}
                                </span>
                        </td>

                        <td class="px-4 py-3">
                            <a href="{{ route('manager.test-drives.show', $t) }}"
                               class="px-3 py-1.5 rounded bg-gray-800 text-white text-xs">
                                Подробнее
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Тест-драйвы не найдены</td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4">
                {{ $items->links() }}
            </div>
        </div>

    </div>
</div>
