<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

        @if (session('ok'))
        <div class="bg-green-50 border border-green-200 text-green-800 p-3 rounded">
            {{ session('ok') }}
        </div>
        @endif


        {{-- Фильтры --}}
        <div class="bg-white p-4 rounded shadow">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="md:col-span-2">
                    <label class="text-xs text-gray-500">Поиск</label>
                    <input wire:model.live="q" class="mt-1 w-full rounded border-gray-300"
                           placeholder="ФИО / телефон / email / права" />
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
                    <label class="text-xs text-gray-500">Верификация</label>
                    <select wire:model.live="verified" class="mt-1 w-full rounded border-gray-300">
                        <option value="">Все</option>
                        <option value="1">Проверен</option>
                        <option value="0">Не проверен</option>
                    </select>
                </div>
            </div>

            <div class="mt-3 flex items-center justify-between">
                <div class="text-sm text-gray-500">Найдено: <b>{{ $clients->total() }}</b></div>

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
                        <th class="text-left px-4 py-3">Клиент</th>
                        <th class="text-left px-4 py-3">Контакты</th>
                        <th class="text-left px-4 py-3">Статус</th>
                        <th class="text-left px-4 py-3">Проверка</th>
                        <th class="text-left px-4 py-3">Действия</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y">
                    @forelse($clients as $c)
                    <tr wire:key="client-{{ $c->id }}" class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $c->full_name }}</div>
                            <div class="text-xs text-gray-500">ID: {{ $c->id }}</div>
                        </td>

                        <td class="px-4 py-3">
                            <div class="text-sm">{{ $c->phone ?? '—' }}</div>
                            <div class="text-xs text-gray-500">{{ $c->email ?? '—' }}</div>
                        </td>

                        <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs border">
                                    {{ $statuses[$c->reliability_status] ?? $c->reliability_status }}
                                </span>
                        </td>

                        <td class="px-4 py-3">
                                <span class="text-xs {{ $c->is_verified ? 'text-green-700' : 'text-gray-500' }}">
                                    {{ $c->is_verified ? 'Проверен' : 'Не проверен' }}
                                </span>
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('manager.clients.show', $c) }}"
                                   class="px-3 py-1.5 rounded bg-gray-800 text-white text-xs hover:opacity-90">
                                    Подробнее
                                </a>
                                <button type="button"
                                        wire:click="toggleBlocked({{ $c->id }})"
                                        class="px-3 py-1.5 rounded border text-xs">
                                    {{ $c->reliability_status === 'blocked' ? 'Разблокировать' : 'Заблокировать' }}
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Клиенты не найдены</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4">
                {{ $clients->links() }}
            </div>
        </div>

    </div>
</div>
