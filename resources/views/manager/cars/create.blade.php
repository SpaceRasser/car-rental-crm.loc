<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Добавить автомобиль
            </h2>

            <a href="{{ route('manager.cars.index') }}" class="px-3 py-2 rounded border text-sm">
                ← Назад к списку
            </a>
        </div>
    </x-slot>

    <livewire:manager.cars.form />
</x-app-layout>
