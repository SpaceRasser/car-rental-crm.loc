<div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

        @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 p-3 rounded">
            {{ session('success') }}
        </div>
        @endif

        <form wire:submit.prevent="save" class="bg-white rounded shadow p-5 space-y-5">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <label class="text-sm text-gray-600">Клиент *</label>
                    <input
                        type="text"
                        placeholder="Поиск клиента..."
                        data-select-target="rental-client-select"
                        class="mt-1 w-full rounded border-gray-300 text-sm"
                    />
                    <select id="rental-client-select" wire:model.live="client_id" class="mt-2 w-full rounded border-gray-300">
                        <option value="">— выбрать —</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}">
                            {{ trim($c->last_name.' '.$c->first_name.' '.$c->middle_name) }}
                            {{ $c->phone ? ' • '.$c->phone : '' }}
                        </option>
                        @endforeach
                    </select>
                    @error('client_id') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Автомобиль *</label>
                    <input
                        type="text"
                        placeholder="Поиск автомобиля..."
                        data-select-target="rental-car-select"
                        class="mt-1 w-full rounded border-gray-300 text-sm"
                    />
                    <select id="rental-car-select" wire:model.live="car_id" class="mt-2 w-full rounded border-gray-300">
                        <option value="">— выбрать —</option>
                        @foreach($cars as $car)
                        <option value="{{ $car->id }}">
                            {{ $car->brand }} {{ $car->model }} • {{ $car->plate_number }}
                        </option>
                        @endforeach
                    </select>
                    @error('car_id') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Доп. автомобили</label>
                    <input
                        type="text"
                        placeholder="Поиск автомобиля..."
                        data-select-target="rental-additional-cars-select"
                        class="mt-1 w-full rounded border-gray-300 text-sm"
                    />
                    <select id="rental-additional-cars-select" wire:model.defer="additional_car_ids" multiple class="mt-2 w-full rounded border-gray-300 h-32">
                        @foreach($cars as $car)
                        <option value="{{ $car->id }}">
                            {{ $car->brand }} {{ $car->model }} • {{ $car->plate_number }}
                        </option>
                        @endforeach
                    </select>
                    <div class="text-xs text-gray-500 mt-1">Используйте Ctrl/Cmd для выбора нескольких.</div>
                    @error('additional_car_ids') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                    @error('additional_car_ids.*') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Начало *</label>
                    <input type="datetime-local" wire:model.live="starts_at" class="mt-1 w-full rounded border-gray-300" />
                    @error('starts_at') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Окончание *</label>
                    <input type="datetime-local" wire:model.live="ends_at" class="mt-1 w-full rounded border-gray-300" />
                    @error('ends_at') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                @php
                $canOverride = in_array(auth()->user()->role, ['admin','manager'], true);
                @endphp

                @if($canOverride)
                <div class="text-sm">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" wire:model.live="overridePricing" class="rounded border-gray-300" />
                        Ручная цена (скидка / особые условия)
                    </label>
                    <div class="text-xs text-gray-500 mt-1">
                        По умолчанию цена и депозит берутся из карточки автомобиля и блокируются.
                    </div>
                </div>
                @endif


                <div>
                    <label class="text-sm text-gray-600">Цена/день (₽) *</label>
                    <input type="number" step="0.01"
                           wire:model.live="daily_price"
                           @disabled(!$overridePricing)
                           class="mt-1 w-full rounded border-gray-300 {{ $overridePricing ? '' : 'bg-gray-100 cursor-not-allowed' }}" />
                    @error('daily_price') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Депозит (₽) *</label>
                    <input type="number" step="0.01"
                           wire:model.live="deposit_amount"
                           @disabled(!$overridePricing)
                           class="mt-1 w-full rounded border-gray-300 {{ $overridePricing ? '' : 'bg-gray-100 cursor-not-allowed' }}" />
                    @error('deposit_amount') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                @php
                $extras = \App\Models\Extra::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
                @endphp

                <div class="md:col-span-2 bg-gray-50 rounded p-3">
                    <div class="font-semibold mb-2">Доп. услуги</div>

                    <div class="space-y-2">
                        @forelse($extras as $e)
                        @php $checked = isset($selectedExtras[$e->id]); @endphp

                        <div class="flex items-center justify-between gap-3 border rounded bg-white p-3">
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox"
                                       @checked($checked)
                                       wire:click="toggleExtra({{ $e->id }})"
                                       class="rounded border-gray-300" />

                                <span>
                        {{ $e->name }}
                        <span class="text-xs text-gray-500">
                            • {{ $e->pricing_type === 'per_day' ? 'за день' : 'фикс' }}
                            • {{ number_format((float)$e->price, 2, '.', ' ') }} ₽
                        </span>
                    </span>
                            </label>

                            @if($checked)
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500">Кол-во:</span>
                                <input type="number" min="1" step="1"
                                       wire:model.live="selectedExtras.{{ $e->id }}"
                                       class="w-20 rounded border-gray-300 text-sm" />
                            </div>
                            @endif
                        </div>
                        @empty
                        <div class="text-sm text-gray-500">Нет активных доп. услуг</div>
                        @endforelse
                    </div>
                </div>


                <div>
                    <label class="text-sm text-gray-600">Статус</label>
                    <input
                        type="text"
                        placeholder="Поиск статуса..."
                        data-select-target="rental-status-select"
                        class="mt-1 w-full rounded border-gray-300 text-sm"
                    />
                    <select id="rental-status-select" wire:model.defer="status" class="mt-2 w-full rounded border-gray-300">
                        @foreach($statuses as $k => $label)
                        <option value="{{ $k }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="bg-gray-50 rounded p-3 text-sm">
                    <div>Дней: <b>{{ $days }}</b></div>
                    <div>Аренда: <b>{{ number_format((float)$rent_amount, 2, '.', ' ') }} ₽</b></div>
                    <div>Итого к оплате: <b>{{ number_format((float)$total_amount, 2, '.', ' ') }} ₽</b></div>
                    <div>Доп. услуги: <b>{{ number_format((float)$extras_amount, 2, '.', ' ') }} ₽</b></div>
                    <div class="text-xs text-gray-500 mt-1">Итого = аренда + услуги + депозит</div>
                </div>
            </div>

            @if(!empty($additionalCarsSummary))
            <div class="bg-white rounded border p-4 text-sm">
                <div class="font-semibold mb-2">Стоимость по автомобилям</div>
                <div class="space-y-2">
                    @foreach($additionalCarsSummary as $summary)
                        <div class="flex items-center justify-between">
                            <div>{{ $summary['label'] }}</div>
                            <div class="text-xs text-gray-600">
                                {{ number_format((float) $summary['daily_price'], 2, '.', ' ') }} ₽/день •
                                депозит {{ number_format((float) $summary['deposit_amount'], 2, '.', ' ') }} ₽
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="bg-gray-50 rounded p-4 text-sm space-y-3">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" wire:model.defer="use_trusted_person" class="rounded border-gray-300" />
                    Доверенное лицо для аренды
                </label>
                @if($use_trusted_person)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="text-xs text-gray-500">ФИО</label>
                        <input wire:model.defer="trusted_person_name" class="mt-1 w-full rounded border-gray-300" />
                        @error('trusted_person_name') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Телефон</label>
                        <input wire:model.defer="trusted_person_phone" data-mask="phone" class="mt-1 w-full rounded border-gray-300" />
                        @error('trusted_person_phone') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">№ водительского удостоверения</label>
                        <input wire:model.defer="trusted_person_license_number" data-mask="license" class="mt-1 w-full rounded border-gray-300" />
                        @error('trusted_person_license_number') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>
                @endif
            </div>

            <div class="bg-gray-50 rounded p-4 text-sm space-y-3">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" wire:model.defer="use_trusted_person" class="rounded border-gray-300" />
                    Доверенное лицо для аренды
                </label>
                @if($use_trusted_person)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="text-xs text-gray-500">ФИО</label>
                        <input wire:model.defer="trusted_person_name" class="mt-1 w-full rounded border-gray-300" />
                        @error('trusted_person_name') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Телефон</label>
                        <input wire:model.defer="trusted_person_phone" data-mask="phone" class="mt-1 w-full rounded border-gray-300" />
                        @error('trusted_person_phone') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">№ водительского удостоверения</label>
                        <input wire:model.defer="trusted_person_license_number" data-mask="license" class="mt-1 w-full rounded border-gray-300" />
                        @error('trusted_person_license_number') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>
                @endif
            </div>

            <div>
                <label class="text-sm text-gray-600">Комментарий</label>
                <textarea wire:model.defer="notes" rows="3" class="mt-1 w-full rounded border-gray-300"></textarea>
                @error('notes') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('manager.rentals.index') }}" class="px-4 py-2 rounded border">Отмена</a>

                <div class="flex-1"></div>

                <button type="submit" class="px-4 py-2 rounded bg-gray-800 text-white">
                    Создать
                </button>
            </div>
        </form>
    </div>
</div>
