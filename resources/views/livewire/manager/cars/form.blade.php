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
                    <label class="text-sm text-gray-600">Марка *</label>
                    <input wire:model.defer="brand" class="mt-1 w-full rounded border-gray-300" />
                    @error('brand') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Модель *</label>
                    <input wire:model.defer="model" class="mt-1 w-full rounded border-gray-300" />
                    @error('model') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Год *</label>
                    <input type="number" wire:model.defer="year" class="mt-1 w-full rounded border-gray-300" />
                    @error('year') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Цвет</label>
                    <input wire:model.defer="color" class="mt-1 w-full rounded border-gray-300" />
                    @error('color') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">VIN *</label>
                    <input wire:model.defer="vin" class="mt-1 w-full rounded border-gray-300 font-mono" />
                    @error('vin') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Госномер *</label>
                    <input wire:model.defer="plate_number" class="mt-1 w-full rounded border-gray-300 font-mono" />
                    @error('plate_number') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Топливо</label>
                    <input wire:model.defer="fuel_type" class="mt-1 w-full rounded border-gray-300" placeholder="Бензин/Дизель/Электро..." />
                    @error('fuel_type') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">КПП</label>
                    <input wire:model.defer="transmission" class="mt-1 w-full rounded border-gray-300" placeholder="Автомат/Механика..." />
                    @error('transmission') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Пробег (км)</label>
                    <input type="number" wire:model.defer="mileage_km" class="mt-1 w-full rounded border-gray-300" />
                    @error('mileage_km') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Статус *</label>
                    <input
                        type="text"
                        placeholder="Поиск статуса..."
                        data-select-target="car-status-select"
                        class="mt-1 w-full rounded border-gray-300 text-sm"
                    />
                    <select id="car-status-select" wire:model.defer="status" class="mt-2 w-full rounded border-gray-300">
                        @foreach($statuses as $k => $label)
                        <option value="{{ $k }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Цена/день (₽) *</label>
                    <input type="number" step="0.01" wire:model.defer="daily_price" class="mt-1 w-full rounded border-gray-300" />
                    @error('daily_price') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Депозит (₽) *</label>
                    <input type="number" step="0.01" wire:model.defer="deposit_amount" class="mt-1 w-full rounded border-gray-300" />
                    @error('deposit_amount') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <div>
                <label class="text-sm text-gray-600">Описание</label>
                <textarea wire:model.defer="description" rows="4" class="mt-1 w-full rounded border-gray-300"></textarea>
                @error('description') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="flex items-center gap-3">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model.defer="is_active" class="rounded border-gray-300" />
                    Активен
                </label>

                <div class="flex-1"></div>

                <a href="{{ route('manager.cars.index') }}" class="px-4 py-2 rounded border">
                    Отмена
                </a>

                <button type="submit" class="px-4 py-2 rounded bg-gray-800 text-white">
                    Сохранить
                </button>
            </div>
        </form>
    </div>
</div>
