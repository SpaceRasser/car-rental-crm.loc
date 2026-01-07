<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

        <div class="bg-white rounded shadow p-4 flex flex-wrap items-end justify-between gap-3">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 w-full md:w-auto">
                <div>
                    <label class="text-xs text-gray-500">Поиск</label>
                    <input wire:model.live="q" class="mt-1 w-full rounded border-gray-300" placeholder="название / код" />
                </div>

                <div>
                    <label class="text-xs text-gray-500">Активность</label>
                    <select wire:model.live="active" class="mt-1 w-full rounded border-gray-300">
                        <option value="">Все</option>
                        <option value="1">Активные</option>
                        <option value="0">Отключённые</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <select wire:model.live="perPage" class="rounded border-gray-300 text-sm">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                    </select>

                    <a href="{{ route('admin.extras.create') }}" class="px-4 py-2 rounded bg-gray-800 text-white text-sm">
                        + Добавить
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white rounded shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3">ID</th>
                        <th class="text-left px-4 py-3">Название</th>
                        <th class="text-left px-4 py-3">Тип</th>
                        <th class="text-left px-4 py-3">Цена</th>
                        <th class="text-left px-4 py-3">Активна</th>
                        <th class="text-left px-4 py-3">Действия</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y">
                    @forelse($extras as $e)
                    <tr wire:key="extra-{{ $e->id }}" class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">#{{ $e->id }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $e->name }}</div>
                            <div class="text-xs text-gray-500 font-mono">{{ $e->code ?: '—' }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $pricingLabels[$e->pricing_type] ?? $e->pricing_type }}</td>
                        <td class="px-4 py-3">{{ number_format((float)$e->price, 2, '.', ' ') }} ₽</td>
                        <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs border">
                                    {{ $e->is_active ? 'Да' : 'Нет' }}
                                </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.extras.edit', $e) }}"
                                   class="px-3 py-1.5 rounded border text-xs">Изменить</a>

                                <button type="button" wire:click="toggle({{ $e->id }})"
                                        class="px-3 py-1.5 rounded bg-gray-800 text-white text-xs">
                                    {{ $e->is_active ? 'Выключить' : 'Включить' }}
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Пока пусто</td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4">
                {{ $extras->links() }}
            </div>
        </div>

    </div>
</div>
