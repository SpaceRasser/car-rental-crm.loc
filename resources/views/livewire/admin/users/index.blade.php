<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

        @if (session('ok'))
        <div class="bg-green-50 border border-green-200 text-green-800 p-3 rounded">
            {{ session('ok') }}
        </div>
        @endif

        @if (session('err'))
        <div class="bg-red-50 border border-red-200 text-red-800 p-3 rounded">
            {{ session('err') }}
        </div>
        @endif

        <div class="bg-white rounded shadow p-4">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <div class="md:col-span-2">
                    <label class="text-xs text-gray-500">Поиск</label>
                    <input wire:model.live="q" class="mt-1 w-full rounded border-gray-300" placeholder="имя / email / id" />
                </div>

                <div>
                    <label class="text-xs text-gray-500">Роль</label>
                    <select wire:model.live="role" class="mt-1 w-full rounded border-gray-300">
                        <option value="">Все</option>
                        @foreach($roles as $k => $label)
                        <option value="{{ $k }}">{{ $label }}</option>
                        @endforeach
                    </select>
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
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3">ID</th>
                        <th class="text-left px-4 py-3">Имя</th>
                        <th class="text-left px-4 py-3">Email</th>
                        <th class="text-left px-4 py-3">Роль</th>
                        <th class="text-left px-4 py-3">Статус</th>
                        <th class="text-left px-4 py-3">Действия</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y">
                    @foreach($users as $u)
                    <tr wire:key="user-{{ $u->id }}" class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">#{{ $u->id }}</td>
                        <td class="px-4 py-3">{{ $u->name }}</td>
                        <td class="px-4 py-3">{{ $u->email }}</td>

                        <td class="px-4 py-3">
                            <select class="rounded border-gray-300 text-sm"
                                    wire:change="setRole({{ $u->id }}, $event.target.value)">
                                @foreach($roles as $k => $label)
                                <option value="{{ $k }}" @selected($u->role === $k)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>

                        <td class="px-4 py-3">
                            @if($u->is_active)
                            <span class="px-2 py-1 rounded text-xs border">Активен</span>
                            @else
                            <span class="px-2 py-1 rounded text-xs border">Отключён</span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <button type="button"
                                    wire:click="toggleActive({{ $u->id }})"
                                    class="px-3 py-1.5 rounded border text-xs">
                                {{ $u->is_active ? 'Отключить' : 'Включить' }}
                            </button>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4">
                {{ $users->links() }}
            </div>
        </div>

    </div>
</div>
