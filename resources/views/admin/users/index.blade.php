<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Пользователи
            </h2>
        </div>
    </x-slot>

    <livewire:admin.users.index />
</x-app-layout>
