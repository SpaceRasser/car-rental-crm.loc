<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

        <div class="bg-white rounded shadow p-4">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <div class="md:col-span-2">
                    <label class="text-xs text-gray-500">Поиск</label>
                    <input wire:model.live="q" class="mt-1 w-full rounded border-gray-300" placeholder="текст / subject_id / causer_id" />
                </div>

                <div>
                    <label class="text-xs text-gray-500">Событие</label>
                    <select wire:model.live="event" class="mt-1 w-full rounded border-gray-300">
                        <option value="">Все</option>
                        @foreach($events as $k => $label)
                        <option value="{{ $k }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs text-gray-500">Раздел</label>
                    <select wire:model.live="subject" class="mt-1 w-full rounded border-gray-300">
                        <option value="">Все</option>
                        @foreach($subjects as $class => $label)
                        <option value="{{ $class }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs text-gray-500">Пользователь</label>
                    <select wire:model.live="causerId" class="mt-1 w-full rounded border-gray-300">
                        <option value="">Все</option>
                        @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} (#{{ $u->id }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <select wire:model.live="perPage" class="rounded border-gray-300 text-sm">
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>

                    <button wire:click="clear" class="px-3 py-2 rounded border text-sm">Сброс</button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-3">
                <div>
                    <label class="text-xs text-gray-500">С</label>
                    <input type="date" wire:model.live="from" class="mt-1 w-full rounded border-gray-300" />
                </div>
                <div>
                    <label class="text-xs text-gray-500">По</label>
                    <input type="date" wire:model.live="to" class="mt-1 w-full rounded border-gray-300" />
                </div>
                <div class="md:col-span-2 text-sm text-gray-500 flex items-end">
                    Найдено: <b class="ml-1">{{ $logs->total() }}</b>
                </div>
            </div>
        </div>

        <div class="bg-white rounded shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3">Дата</th>
                        <th class="text-left px-4 py-3">Кто</th>
                        <th class="text-left px-4 py-3">Событие</th>
                        <th class="text-left px-4 py-3">Объект</th>
                        <th class="text-left px-4 py-3">Описание</th>
                        <th class="text-left px-4 py-3">Детали</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y">
                    @foreach($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-xs text-gray-600">
                            {{ $log->created_at?->format('d.m.Y H:i:s') }}
                        </td>

                        <td class="px-4 py-3">
                            <div class="font-medium">
                                {{ $log->causer?->name ?? 'Система' }}
                            </div>
                            <div class="text-xs text-gray-500">#{{ $log->causer_id ?? '—' }}</div>
                        </td>

                        <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs border">
                                    {{ $events[$log->event] ?? ($log->event ?? '—') }}
                                </span>
                        </td>

                        <td class="px-4 py-3">
                            <div class="text-xs text-gray-500">
                                {{ $subjects[$log->subject_type] ?? class_basename($log->subject_type ?? '') }}
                            </div>
                            <div class="font-mono text-xs">
                                #{{ $log->subject_id ?? '—' }}
                            </div>
                        </td>

                        <td class="px-4 py-3">
                            {{ $log->description }}
                        </td>

                        <td class="px-4 py-3">
                            @php $props = $log->properties?->toArray() ?? []; @endphp
                            @if(!empty($props))
                            <details class="text-xs">
                                <summary class="cursor-pointer text-gray-700">Показать</summary>
                                <pre class="mt-2 p-2 bg-gray-100 rounded overflow-auto text-[11px]">{{ json_encode($props, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                            </details>
                            @else
                            <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4">
                {{ $logs->links() }}
            </div>
        </div>

    </div>
</div>
