<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('manager.rentals.index') }}"
                   class="px-3 py-2 rounded border text-sm">
                    ← Назад
                </a>

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Аренда #{{ $rental->id }}
                </h2>
            </div>
        </div>
    </x-slot>

    <livewire:manager.rentals.show :rentalId="$rental->id" />
</x-app-layout>
