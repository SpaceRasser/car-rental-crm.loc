<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Клиенты
            </h2>

            <a href="{{ route('manager.clients.create') }}" class="px-4 py-2 rounded bg-gray-800 text-white text-sm">
                + Добавить клиента
            </a>
        </div>
    </x-slot>

    <livewire:manager.clients.index />
</x-app-layout>
