<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">История аренд</h3>
                    @if(!$client)
                        <span class="text-xs text-gray-500">Профиль клиента не найден</span>
                    @endif
                </div>

                @if($client)
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
                            <tr>
                                <th class="text-left px-4 py-3">ID</th>
                                <th class="text-left px-4 py-3">Авто</th>
                                <th class="text-left px-4 py-3">Период</th>
                                <th class="text-left px-4 py-3">Статус</th>
                                <th class="text-left px-4 py-3">Сумма</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y">
                            @forelse($rentals as $rental)
                                @php
                                    $total = (float)($rental->grand_total ?? $rental->base_total ?? 0);
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 font-mono text-xs">#{{ $rental->id }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium">{{ $rental->car?->brand }} {{ $rental->car?->model }}</div>
                                        <div class="text-xs text-gray-500 font-mono">{{ $rental->car?->plate_number ?? '—' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div>{{ optional($rental->starts_at)->format('d.m.Y H:i') ?? '—' }}</div>
                                        <div class="text-xs text-gray-500">→ {{ optional($rental->ends_at)->format('d.m.Y H:i') ?? '—' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded text-xs border">{{ $rental->status }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ number_format($total, 2, '.', ' ') }} ₽
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                        История аренд пуста
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-sm text-gray-500">Связанный профиль клиента не найден.</div>
                @endif
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">История тест-драйвов</h3>
                </div>

                @if($client)
                    @php
                        $tdStatus = [
                            'new' => 'Ожидает подтверждения',
                            'confirmed' => 'Подтверждён',
                            'completed' => 'Завершён',
                            'no_show' => 'Не пришёл',
                            'cancelled' => 'Отменён',
                        ];
                    @endphp
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
                            <tr>
                                <th class="text-left px-4 py-3">ID</th>
                                <th class="text-left px-4 py-3">Дата/время</th>
                                <th class="text-left px-4 py-3">Авто</th>
                                <th class="text-left px-4 py-3">Статус</th>
                                <th class="text-left px-4 py-3">Менеджер</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y">
                            @forelse($testDrives as $testDrive)
                                <tr>
                                    <td class="px-4 py-3 font-mono text-xs">#{{ $testDrive->id }}</td>
                                    <td class="px-4 py-3">
                                        <div>{{ optional($testDrive->scheduled_at)->format('d.m.Y H:i') ?? '—' }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $testDrive->duration_minutes ? $testDrive->duration_minutes.' мин.' : '—' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium">{{ $testDrive->car?->brand }} {{ $testDrive->car?->model }}</div>
                                        <div class="text-xs text-gray-500 font-mono">{{ $testDrive->car?->plate_number ?? '—' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded text-xs border">
                                            {{ $tdStatus[$testDrive->status] ?? $testDrive->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ $testDrive->manager?->name ?? '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                        История тест-драйвов пуста
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-sm text-gray-500">Связанный профиль клиента не найден.</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
