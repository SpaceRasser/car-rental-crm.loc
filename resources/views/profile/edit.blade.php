<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Профиль
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            @if(auth()->user()->role === 'client')
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-2xl">
                    <div class="text-lg font-semibold text-gray-800 dark:text-gray-200">Данные клиента</div>
                    <div class="text-sm text-gray-500 mt-1">
                        Для бронирования необходимо заполнить все обязательные поля.
                    </div>

                    @if (session('profile_incomplete'))
                        <div class="mt-4 bg-yellow-50 border border-yellow-200 text-yellow-800 p-3 rounded text-sm">
                            {{ session('profile_incomplete') }}
                        </div>
                    @endif

                    @if (session('status') === 'client-profile-updated')
                        <div class="mt-4 bg-green-50 border border-green-200 text-green-800 p-3 rounded text-sm">
                            Данные клиента обновлены.
                        </div>
                    @endif

                    <form method="post" action="{{ route('profile.client.update') }}" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        @csrf
                        @method('patch')

                        <div>
                            <label class="text-xs text-gray-500">Фамилия *</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $client->last_name ?? '') }}" class="mt-1 w-full rounded border-gray-300" />
                            @error('last_name') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="text-xs text-gray-500">Имя *</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $client->first_name ?? '') }}" class="mt-1 w-full rounded border-gray-300" />
                            @error('first_name') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="text-xs text-gray-500">Отчество</label>
                            <input type="text" name="middle_name" value="{{ old('middle_name', $client->middle_name ?? '') }}" class="mt-1 w-full rounded border-gray-300" />
                            @error('middle_name') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="text-xs text-gray-500">Дата рождения *</label>
                            <input type="date" name="birth_date" value="{{ old('birth_date', optional($client?->birth_date)->format('Y-m-d')) }}" class="mt-1 w-full rounded border-gray-300" />
                            @error('birth_date') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="text-xs text-gray-500">Телефон *</label>
                            <input type="text" name="phone" data-mask="phone" value="{{ old('phone', $client->phone ?? '') }}" class="mt-1 w-full rounded border-gray-300" />
                            @error('phone') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="text-xs text-gray-500">Email *</label>
                            <input type="email" name="email" data-mask="email" value="{{ old('email', $client->email ?? auth()->user()->email) }}" class="mt-1 w-full rounded border-gray-300" />
                            @error('email') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="text-xs text-gray-500">№ водительского удостоверения *</label>
                            <input type="text" name="driver_license_number" data-mask="license" value="{{ old('driver_license_number', $client->driver_license_number ?? '') }}" class="mt-1 w-full rounded border-gray-300" />
                            @error('driver_license_number') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="text-xs text-gray-500">Права выданы *</label>
                            <input type="date" name="driver_license_issued_at" value="{{ old('driver_license_issued_at', optional($client?->driver_license_issued_at)->format('Y-m-d')) }}" class="mt-1 w-full rounded border-gray-300" />
                            @error('driver_license_issued_at') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="text-xs text-gray-500">Права действуют до *</label>
                            <input type="date" name="driver_license_expires_at" value="{{ old('driver_license_expires_at', optional($client?->driver_license_expires_at)->format('Y-m-d')) }}" class="mt-1 w-full rounded border-gray-300" />
                            @error('driver_license_expires_at') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <button type="submit" class="px-4 py-2 rounded bg-gray-800 text-white text-sm">Сохранить данные</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

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
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Платежи</h3>
                </div>

                @if($client)
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
                            <tr>
                                <th class="text-left px-4 py-3">ID</th>
                                <th class="text-left px-4 py-3">Аренда</th>
                                <th class="text-left px-4 py-3">Сумма</th>
                                <th class="text-left px-4 py-3">Статус</th>
                                <th class="text-left px-4 py-3">Дата</th>
                                <th class="text-left px-4 py-3">Действия</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y">
                            @forelse($payments as $payment)
                                <tr>
                                    <td class="px-4 py-3 font-mono text-xs">#{{ $payment->id }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium">Аренда #{{ $payment->rental_id }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $payment->rental?->car?->brand }} {{ $payment->rental?->car?->model }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">{{ number_format((float)$payment->amount, 2, '.', ' ') }} ₽</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded text-xs border">{{ $payment->status }}</span>
                                    </td>
                                    <td class="px-4 py-3">{{ optional($payment->paid_at ?? $payment->created_at)->format('d.m.Y H:i') ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $rental = $payment->rental;
                                            $paid = $rental?->payments?->where('status', 'paid')->sum('amount') ?? 0;
                                            $total = (float)(($rental?->grand_total ?? 0) + ($rental?->deposit_amount ?? 0));
                                            $remaining = max(0, $total - (float)$paid);
                                        @endphp
                                        @if($remaining > 0 && $rental)
                                            <form method="post" action="{{ route('client.rentals.pay', $rental) }}">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 rounded bg-gray-800 text-white text-xs">
                                                    Оплатить {{ number_format((float)$remaining, 2, '.', ' ') }} ₽
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-500">Оплачено</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                        Платежей пока нет
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        <div class="text-sm font-semibold text-gray-700 mb-2">Счета к оплате</div>
                        <div class="space-y-2">
                            @foreach($rentals as $rental)
                                @php
                                    $paid = $rental->payments?->where('status', 'paid')->sum('amount') ?? 0;
                                    $total = (float)(($rental->grand_total ?? 0) + ($rental->deposit_amount ?? 0));
                                    $remaining = max(0, $total - (float)$paid);
                                @endphp
                                @if($remaining > 0)
                                    <div class="flex items-center justify-between border rounded p-3 text-sm">
                                        <div>
                                            <div class="font-medium">Аренда #{{ $rental->id }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ $rental->car?->brand }} {{ $rental->car?->model }} • {{ number_format((float)$remaining, 2, '.', ' ') }} ₽
                                            </div>
                                        </div>
                                        <form method="post" action="{{ route('client.rentals.pay', $rental) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded bg-gray-800 text-white text-xs">
                                                Оплатить
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-sm text-gray-500">Связанный профиль клиента не найден.</div>
                @endif
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
