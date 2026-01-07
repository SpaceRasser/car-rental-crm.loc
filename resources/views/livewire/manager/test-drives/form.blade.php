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
                    <select wire:model.live="client_id" class="mt-1 w-full rounded border-gray-300">
                        <option value="">— выбрать —</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}">
                            {{ $c->full_name }}{{ $c->phone ? ' • '.$c->phone : '' }}
                        </option>
                        @endforeach
                    </select>
                    @error('client_id') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Автомобиль *</label>
                    <select wire:model.live="car_id" class="mt-1 w-full rounded border-gray-300">
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
                    <label class="text-sm text-gray-600">Дата/время *</label>
                    <input type="datetime-local" wire:model.live="scheduled_at" class="mt-1 w-full rounded border-gray-300" />
                    @error('scheduled_at') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Длительность (мин.) *</label>
                    <input type="number" wire:model.live="duration_minutes" class="mt-1 w-full rounded border-gray-300" />
                    @error('duration_minutes') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Статус</label>
                    <select wire:model.defer="status" class="mt-1 w-full rounded border-gray-300">
                        @foreach($statuses as $k => $label)
                        <option value="{{ $k }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <div>
                <label class="text-sm text-gray-600">Комментарий</label>
                <textarea wire:model.defer="notes" rows="3" class="mt-1 w-full rounded border-gray-300"></textarea>
                @error('notes') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('manager.test-drives.index') }}" class="px-4 py-2 rounded border">Отмена</a>
                <div class="flex-1"></div>
                <button type="submit" class="px-4 py-2 rounded bg-gray-800 text-white">Создать</button>
            </div>
        </form>
    </div>
</div>
