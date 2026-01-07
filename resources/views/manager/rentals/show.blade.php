<x-app-layout>
    <x-slot name="header">
        @php
        // Договор показываем только когда аренда оформлена
        $canContract = in_array($rental->status, ['confirmed', 'active', 'closed'], true);
        @endphp

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('manager.rentals.index') }}"
                   class="px-3 py-2 rounded border text-sm">
                    ← Назад
                </a>

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Аренда #{{ $rental->id }}
                </h2>

                <span class="px-2 py-1 rounded text-xs border">
                    {{ $rental->status }}
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
    </x-slot>

    <livewire:manager.rentals.show :rentalId="$rental->id" />
</x-app-layout>
