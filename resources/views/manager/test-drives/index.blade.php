<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Тест-драйвы</h2>

            <a href="{{ route('manager.test-drives.create') }}" class="px-4 py-2 rounded bg-gray-800 text-white text-sm">
                + Создать тест-драйв
            </a>
        </div>
    </x-slot>

    <livewire:manager.test-drives.index />
</x-app-layout>
