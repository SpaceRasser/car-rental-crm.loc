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
                    <label class="text-sm text-gray-600">Фамилия *</label>
                    <input wire:model.defer="last_name" class="mt-1 w-full rounded border-gray-300" />
                    @error('last_name') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Имя *</label>
                    <input wire:model.defer="first_name" class="mt-1 w-full rounded border-gray-300" />
                    @error('first_name') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Отчество</label>
                    <input wire:model.defer="middle_name" class="mt-1 w-full rounded border-gray-300" />
                    @error('middle_name') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Дата рождения</label>
                    <input type="date" wire:model.defer="birth_date" class="mt-1 w-full rounded border-gray-300" />
                    @error('birth_date') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Телефон</label>
                    <input wire:model.defer="phone" class="mt-1 w-full rounded border-gray-300" />
                    @error('phone') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Email</label>
                    <input wire:model.defer="email" class="mt-1 w-full rounded border-gray-300" />
                    @error('email') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">№ водительского удостоверения</label>
                    <input wire:model.defer="driver_license_number" class="mt-1 w-full rounded border-gray-300" />
                    @error('driver_license_number') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Статус надёжности</label>
                    <select wire:model.defer="reliability_status" class="mt-1 w-full rounded border-gray-300">
                        @foreach($statuses as $k => $label)
                        <option value="{{ $k }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('reliability_status') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Права выданы</label>
                    <input type="date" wire:model.defer="driver_license_issued_at" class="mt-1 w-full rounded border-gray-300" />
                    @error('driver_license_issued_at') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Права действуют до</label>
                    <input type="date" wire:model.defer="driver_license_expires_at" class="mt-1 w-full rounded border-gray-300" />
                    @error('driver_license_expires_at') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <div>
                <label class="text-sm text-gray-600">Заметки</label>
                <textarea wire:model.defer="notes" rows="4" class="mt-1 w-full rounded border-gray-300"></textarea>
                @error('notes') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="flex items-center gap-3">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model.defer="is_verified" class="rounded border-gray-300" />
                    Клиент проверен
                </label>

                <div class="flex-1"></div>

                <a href="{{ route('manager.clients.index') }}" class="px-4 py-2 rounded border">Отмена</a>

                <button type="submit" class="px-4 py-2 rounded bg-gray-800 text-white">
                    Сохранить
                </button>
            </div>
        </form>
    </div>
</div>
