<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Создать тест-драйв</h2>

            <a href="{{ route('manager.test-drives.index') }}" class="px-3 py-2 rounded border text-sm">
                ← Назад
            </a>
        </div>
    </x-slot>

    <livewire:manager.test-drives.form />
</x-app-layout>
