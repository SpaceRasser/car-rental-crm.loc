<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Добавить клиента</h2>
            <a href="{{ route('manager.clients.index') }}" class="px-3 py-2 rounded border text-sm">← Назад</a>
        </div>
    </x-slot>

    <livewire:manager.clients.form />
</x-app-layout>

