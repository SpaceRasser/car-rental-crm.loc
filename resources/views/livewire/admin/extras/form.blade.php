<div class="py-6">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">

        @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 p-3 rounded">
            {{ session('success') }}
        </div>
        @endif

        <form wire:submit.prevent="save" class="bg-white rounded shadow p-5 space-y-4">
            <div>
                <label class="text-sm text-gray-600">Название *</label>
                <input wire:model.defer="name" class="mt-1 w-full rounded border-gray-300" />
                @error('name') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm text-gray-600">Код (опционально)</label>
                <input wire:model.defer="code" class="mt-1 w-full rounded border-gray-300" placeholder="например: CHILD_SEAT" />
                @error('code') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-gray-600">Тип расчёта *</label>
                    <input
                        type="text"
                        placeholder="Поиск типа..."
                        data-select-target="extra-pricing-select"
                        class="mt-1 w-full rounded border-gray-300 text-sm"
                    />
                    <select id="extra-pricing-select" wire:model.defer="pricing_type" class="mt-2 w-full rounded border-gray-300">
                        @foreach($pricingLabels as $k => $label)
                        <option value="{{ $k }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('pricing_type') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Цена *</label>
                    <input type="number" step="0.01" wire:model.defer="price" class="mt-1 w-full rounded border-gray-300" />
                    @error('price') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <div>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model.defer="is_active" class="rounded border-gray-300" />
                    Активна
                </label>
                @error('is_active') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm text-gray-600">Описание</label>
                <textarea rows="4" wire:model.defer="description" class="mt-1 w-full rounded border-gray-300"></textarea>
                @error('description') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.extras.index') }}" class="px-4 py-2 rounded border">Назад</a>
                <div class="flex-1"></div>
                <button class="px-4 py-2 rounded bg-gray-800 text-white">Сохранить</button>
            </div>
        </form>

    </div>
</div>
