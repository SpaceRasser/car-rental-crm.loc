<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Редактировать клиента #{{ $client->id }}
            </h2>
            <a href="{{ route('manager.clients.show', $client) }}" class="px-3 py-2 rounded border text-sm">← К карточке</a>
        </div>
    </x-slot>

    <livewire:manager.clients.form :clientId="$client->id" />
</x-app-layout>
