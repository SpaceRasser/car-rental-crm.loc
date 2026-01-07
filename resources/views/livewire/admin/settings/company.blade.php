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
                    <label class="text-sm text-gray-600">Название компании *</label>
                    <input wire:model.defer="company_name" class="mt-1 w-full rounded border-gray-300" />
                    @error('company_name') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Юр. название</label>
                    <input wire:model.defer="legal_name" class="mt-1 w-full rounded border-gray-300" />
                    @error('legal_name') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">ИНН</label>
                    <input wire:model.defer="inn" class="mt-1 w-full rounded border-gray-300" />
                </div>

                <div>
                    <label class="text-sm text-gray-600">КПП</label>
                    <input wire:model.defer="kpp" class="mt-1 w-full rounded border-gray-300" />
                </div>

                <div>
                    <label class="text-sm text-gray-600">ОГРН</label>
                    <input wire:model.defer="ogrn" class="mt-1 w-full rounded border-gray-300" />
                </div>

                <div>
                    <label class="text-sm text-gray-600">Префикс договора</label>
                    <input wire:model.defer="contract_prefix" class="mt-1 w-full rounded border-gray-300" />
                    <div class="text-xs text-gray-500 mt-1">Напр. CR → CR-000123</div>
                </div>

                <div class="md:col-span-2">
                    <label class="text-sm text-gray-600">Адрес</label>
                    <input wire:model.defer="address" class="mt-1 w-full rounded border-gray-300" />
                </div>

                <div>
                    <label class="text-sm text-gray-600">Телефон</label>
                    <input wire:model.defer="phone" class="mt-1 w-full rounded border-gray-300" />
                </div>

                <div>
                    <label class="text-sm text-gray-600">Email</label>
                    <input wire:model.defer="email" class="mt-1 w-full rounded border-gray-300" />
                    @error('email') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">Директор (ФИО)</label>
                    <input wire:model.defer="director_name" class="mt-1 w-full rounded border-gray-300" />
                </div>

                <div>
                    <label class="text-sm text-gray-600">Должность</label>
                    <input wire:model.defer="director_position" class="mt-1 w-full rounded border-gray-300" />
                </div>

                <div class="md:col-span-2">
                    <div class="font-semibold text-sm">Банк</div>
                </div>

                <div class="md:col-span-2">
                    <label class="text-sm text-gray-600">Банк</label>
                    <input wire:model.defer="bank_name" class="mt-1 w-full rounded border-gray-300" />
                </div>

                <div>
                    <label class="text-sm text-gray-600">БИК</label>
                    <input wire:model.defer="bik" class="mt-1 w-full rounded border-gray-300" />
                </div>

                <div>
                    <label class="text-sm text-gray-600">Р/с</label>
                    <input wire:model.defer="account" class="mt-1 w-full rounded border-gray-300" />
                </div>

                <div class="md:col-span-2">
                    <label class="text-sm text-gray-600">К/с</label>
                    <input wire:model.defer="corr_account" class="mt-1 w-full rounded border-gray-300" />
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="px-4 py-2 rounded bg-gray-800 text-white">
                    Сохранить
                </button>
            </div>
        </form>
    </div>
</div>
