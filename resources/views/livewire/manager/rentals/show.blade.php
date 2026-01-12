<div class="py-6">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">

        @if (session('rental_success'))
        <div class="bg-green-50 border border-green-200 text-green-800 p-3 rounded">
            {{ session('rental_success') }}
        </div>
        @endif
        @if (session('rental_error'))
        <div class="bg-red-50 border border-red-200 text-red-800 p-3 rounded">
            {{ session('rental_error') }}
        </div>
        @endif

        @php
            $canContract = in_array($rental->status, ['confirmed', 'active', 'closed'], true);
        @endphp

        <div class="bg-white rounded shadow p-5 text-sm flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="font-semibold">Статус:</span>
                <span class="px-2 py-1 rounded text-xs border">
                    {{ $statusLabels[$rental->status] ?? $rental->status }}
                </span>
            </div>

            @if($canContract)
                <div class="flex items-center gap-2">
                    <a target="_blank"
                       href="{{ route('manager.rentals.contract', $rental) }}"
                       class="px-3 py-2 rounded bg-gray-800 text-white text-sm">
                        Договор PDF
                    </a>

                    <a target="_blank"
                       href="{{ route('manager.rentals.contract', [$rental, 'download' => 1]) }}"
                       class="px-3 py-2 rounded border text-sm">
                        Скачать
                    </a>
                </div>
            @else
                <div class="text-sm text-gray-500">
                    Договор станет доступен после подтверждения аренды
                </div>
            @endif
        </div>

        {{-- Основная инфа --}}
        <div class="bg-white rounded shadow p-5 text-sm space-y-2">
            <div class="flex items-center justify-between">
                <div class="font-semibold">Аренда #{{ $rental->id }}</div>
                <span class="px-2 py-1 rounded text-xs border">
                    {{ $statusLabels[$rental->status] ?? $rental->status }}
                </span>
            </div>

            <div class="pt-2 border-t"></div>

            <div><span class="text-gray-500">Клиент:</span> {{ $rental->client?->full_name ?? '—' }}</div>
            <div><span class="text-gray-500">Телефон:</span> {{ $rental->client?->phone ?? '—' }}</div>

            <div class="pt-2 border-t"></div>

            <div><span class="text-gray-500">Автомобили:</span></div>
            <div class="space-y-1">
                @foreach($groupRentals as $item)
                    <div>
                        <span class="font-medium">{{ $item->car?->brand }} {{ $item->car?->model }}</span>
                        <span class="text-xs text-gray-500 font-mono">{{ $item->car?->plate_number ?? '—' }}</span>
                        @if($item->is_trusted_person)
                            <span class="ml-2 text-xs text-red-600">
                                Доверенное лицо: {{ $item->trusted_person_name ?? '—' }}
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="pt-2 border-t"></div>

            <div><span class="text-gray-500">Период:</span>
                {{ optional($rental->starts_at)->format('d.m.Y H:i') ?? '—' }}
                → {{ optional($rental->ends_at)->format('d.m.Y H:i') ?? '—' }}
            </div>

            @php
            $rent = (float) ($rental->grand_total ?? $rental->base_total ?? 0);
            @endphp

            <div><span class="text-gray-500">Аренда (база):</span> {{ number_format((float)$base, 2, '.', ' ') }} ₽</div>

            <div><span class="text-gray-500">Доп. услуги:</span> {{ number_format((float)$extrasTotal, 2, '.', ' ') }} ₽</div>

            @if((float)$discount > 0)
            <div><span class="text-gray-500">Скидка:</span> -{{ number_format((float)$discount, 2, '.', ' ') }} ₽</div>
            @endif

            @if((float)$penalty > 0)
            <div><span class="text-gray-500">Штрафы/доплаты:</span> +{{ number_format((float)$penalty, 2, '.', ' ') }} ₽</div>
            @endif

            <div><span class="text-gray-500">Аренда (итого):</span> {{ number_format((float)$rentTotal, 2, '.', ' ') }} ₽</div>
            <div><span class="text-gray-500">Депозит:</span> {{ number_format((float)$deposit, 2, '.', ' ') }} ₽</div>

            <div><span class="text-gray-500">Итого к оплате:</span> {{ number_format((float)$total, 2, '.', ' ') }} ₽</div>


            <div class="pt-2 border-t"></div>

            <div><span class="text-gray-500">Оплачено:</span> {{ number_format((float)$paid, 2, '.', ' ') }} ₽</div>
            <div><span class="text-gray-500">Остаток:</span> {{ number_format((float)$remaining, 2, '.', ' ') }} ₽</div>

        </div>

        {{-- Действия по статусам --}}
        <div class="bg-white rounded shadow p-5">
            <div class="font-semibold mb-3">Действия</div>

            <div class="flex flex-wrap gap-2">
                @if($rental->status === 'new')
                <button type="button" wire:click="setStatus('confirmed')" @disabled($remaining > 0)
                        class="px-3 py-2 rounded bg-gray-800 text-white text-sm {{ $remaining > 0 ? 'opacity-50 cursor-not-allowed' : '' }}">
                    Подтвердить
                </button>
                @if($remaining > 0)
                    <div class="text-xs text-gray-500 flex items-center">
                        Для подтверждения необходимо оплатить аренду
                    </div>
                @endif
                <button type="button" wire:click="setStatus('cancelled')" class="px-3 py-2 rounded border text-sm">
                    Отменить
                </button>
                @endif

                @if($rental->status === 'confirmed')
                <button type="button" wire:click="openPickup" class="px-3 py-2 rounded bg-gray-800 text-white text-sm">
                    Выдать авто
                </button>
                <button type="button" wire:click="setStatus('cancelled')" class="px-3 py-2 rounded border text-sm">
                    Отменить
                </button>
                @endif

                @if(in_array($rental->status, ['active','overdue'], true))
                <button type="button" wire:click="openReturn" class="px-3 py-2 rounded bg-gray-800 text-white text-sm">
                    Закрыть аренду
                </button>
                @endif
            </div>

            {{-- Форма выдачи --}}
            @if($showPickupForm)
            <div class="mt-4 border-t pt-4">
                <div class="font-semibold mb-3">Выдача авто</div>

                <div class="space-y-4">
                    @foreach($groupRentals as $item)
                        <div class="rounded border p-3">
                            <div class="text-sm font-semibold">
                                {{ $item->car?->brand }} {{ $item->car?->model }}
                                <span class="text-xs text-gray-500 font-mono">{{ $item->car?->plate_number ?? '—' }}</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
                                <div>
                                    <label class="text-xs text-gray-500">Пробег при выдаче (км) *</label>
                                    <input type="number" wire:model.defer="pickupData.{{ $item->id }}.mileage_start_km"
                                           class="mt-1 w-full rounded border-gray-300" />
                                    @error('pickupData.' . $item->id . '.mileage_start_km') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label class="text-xs text-gray-500">Топливо при выдаче (%) *</label>
                                    <input type="number" min="0" max="100" wire:model.defer="pickupData.{{ $item->id }}.fuel_start_percent"
                                           class="mt-1 w-full rounded border-gray-300" />
                                    @error('pickupData.' . $item->id . '.fuel_start_percent') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 flex gap-2">
                    <button type="button" wire:click="confirmPickup"
                            class="px-3 py-2 rounded bg-gray-800 text-white text-sm">
                        Подтвердить выдачу
                    </button>
                    <button type="button" wire:click="cancelPickup"
                            class="px-3 py-2 rounded border text-sm">
                        Отмена
                    </button>
                </div>
            </div>
            @endif

            {{-- Форма возврата --}}
            @if($showReturnForm)
            <div class="mt-4 border-t pt-4">
                <div class="font-semibold mb-3">Возврат авто</div>

                <div class="space-y-4">
                    @foreach($groupRentals as $item)
                        @php
                            $pickupMileage = $item->mileage_start_km ?? '—';
                            $pickupFuel = $item->fuel_start_percent ?? '—';
                        @endphp
                        <div class="rounded border p-3">
                            <div class="text-sm font-semibold">
                                {{ $item->car?->brand }} {{ $item->car?->model }}
                                <span class="text-xs text-gray-500 font-mono">{{ $item->car?->plate_number ?? '—' }}</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-2">
                                <div>
                                    <label class="text-xs text-gray-500">Пробег при возврате (км) *</label>
                                    <input type="number" wire:model.defer="returnData.{{ $item->id }}.mileage_end_km"
                                           placeholder="Выдача: {{ $pickupMileage }} км"
                                           class="mt-1 w-full rounded border-gray-300" />
                                    @error('returnData.' . $item->id . '.mileage_end_km') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label class="text-xs text-gray-500">Топливо при возврате (%) *</label>
                                    <input type="number" min="0" max="100" wire:model.defer="returnData.{{ $item->id }}.fuel_end_percent"
                                           placeholder="Выдача: {{ $pickupFuel }}%"
                                           class="mt-1 w-full rounded border-gray-300" />
                                    @error('returnData.' . $item->id . '.fuel_end_percent') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label class="text-xs text-gray-500">Штрафы/доплаты (₽)</label>
                                    <input type="number" step="0.01" wire:model.defer="returnData.{{ $item->id }}.penalty_total"
                                           class="mt-1 w-full rounded border-gray-300" />
                                    @error('returnData.' . $item->id . '.penalty_total') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 flex gap-2">
                    <button type="button" wire:click="confirmReturn"
                            class="px-3 py-2 rounded bg-gray-800 text-white text-sm">
                        Подтвердить возврат и закрыть
                    </button>
                    <button type="button" wire:click="cancelReturn"
                            class="px-3 py-2 rounded border text-sm">
                        Отмена
                    </button>
                </div>

                <div class="mt-2 text-xs text-gray-500">
                    Штрафы/доплаты добавятся к сумме аренды (grand_total) и повлияют на “Итого к оплате”.
                </div>
            </div>
            @endif

        </div>

        {{-- Доп. услуги --}}
        <div class="bg-white rounded shadow p-5">
            <div class="font-semibold mb-3">Доп. услуги</div>

            @if(($extrasLines ?? collect())->count())
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3">Услуга</th>
                        <th class="text-left px-4 py-3">Тип</th>
                        <th class="text-left px-4 py-3">Цена</th>
                        <th class="text-left px-4 py-3">Кол-во</th>
                        <th class="text-left px-4 py-3">Сумма</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y">
                    @foreach($extrasLines as $x)
                    <tr wire:key="extra-line-{{ $x->id }}">
                        <td class="px-4 py-3">{{ $x->name }}</td>
                        <td class="px-4 py-3">
                            {{ $x->pricing_type === 'per_day' ? 'за день' : 'фикс.' }}
                        </td>
                        <td class="px-4 py-3">{{ number_format((float)$x->price, 2, '.', ' ') }} ₽</td>
                        <td class="px-4 py-3">{{ $x->qty }}</td>
                        <td class="px-4 py-3">{{ number_format((float)$x->amount, 2, '.', ' ') }} ₽</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-sm text-gray-500">Доп. услуг не выбрано</div>
            @endif
        </div>


        {{-- Платежи --}}
        <div class="bg-white rounded shadow p-5">
            <div class="flex items-center justify-between">
                <div class="font-semibold">Платежи</div>

                @if($remaining > 0)
                <button type="button" wire:click="createPayment"
                        class="px-3 py-2 rounded bg-gray-800 text-white text-sm">
                    + Создать платёж
                </button>
                @else
                <button type="button" disabled
                        class="px-3 py-2 rounded border text-sm opacity-50 cursor-not-allowed">
                    Аренда оплачена
                </button>
                @endif

            </div>

            @if (session('payment_success'))
            <div class="mt-3 bg-green-50 border border-green-200 text-green-800 p-3 rounded text-sm">
                {{ session('payment_success') }}
            </div>
            @endif
            @if (session('payment_error'))
            <div class="mt-3 bg-red-50 border border-red-200 text-red-800 p-3 rounded text-sm">
                {{ session('payment_error') }}
            </div>
            @endif

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3">ID</th>
                        <th class="text-left px-4 py-3">Сумма</th>
                        <th class="text-left px-4 py-3">Статус</th>
                        <th class="text-left px-4 py-3">External ID</th>
                        <th class="text-left px-4 py-3">Reference</th>
                        <th class="text-left px-4 py-3">Действия</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y">
                    @forelse($rental->payments as $p)
                    <tr wire:key="payment-{{ $p->id }}">
                        <td class="px-4 py-3 font-mono text-xs">#{{ $p->id }}</td>
                        <td class="px-4 py-3">{{ number_format((float)$p->amount, 2, '.', ' ') }} {{ $p->currency ?? 'RUB' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs border">{{ $p->status }}</span>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $p->external_id ?? '—' }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $p->payment_reference ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                @if($p->status !== 'paid')
                                <button type="button" wire:click="simulateSuccess({{ $p->id }})"
                                        class="px-3 py-1.5 rounded bg-gray-800 text-white text-xs">
                                    Симулировать успех
                                </button>

                                <button type="button" wire:click="simulateFail({{ $p->id }})"
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
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            Платежей пока нет
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>


    </div>
</div>
