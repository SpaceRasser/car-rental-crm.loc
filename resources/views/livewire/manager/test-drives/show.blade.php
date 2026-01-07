<div class="py-6">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">

        @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 p-3 rounded">
            {{ session('success') }}
        </div>
        @endif
        @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 p-3 rounded">
            {{ session('error') }}
        </div>
        @endif

        <div class="bg-white rounded shadow p-5 text-sm space-y-2">
            <div class="flex items-center justify-between">
                <div class="font-semibold">Тест-драйв #{{ $td->id }}</div>
                <span class="px-2 py-1 rounded text-xs border">
                    {{ $statusLabels[$td->status] ?? $td->status }}
                </span>
            </div>

            <div class="pt-2 border-t"></div>

            <div><span class="text-gray-500">Дата/время:</span> {{ optional($td->scheduled_at)->format('d.m.Y H:i') ?? '—' }}</div>
            <div><span class="text-gray-500">Длительность:</span> {{ $td->duration_minutes }} мин.</div>

            <div class="pt-2 border-t"></div>

            <div><span class="text-gray-500">Клиент:</span> {{ $td->client?->full_name ?? '—' }}</div>
            <div><span class="text-gray-500">Телефон:</span> {{ $td->client?->phone ?? '—' }}</div>

            <div class="pt-2 border-t"></div>

            <div><span class="text-gray-500">Авто:</span> {{ $td->car?->brand }} {{ $td->car?->model }}</div>
            <div><span class="text-gray-500">Госномер:</span> <span class="font-mono">{{ $td->car?->plate_number ?? '—' }}</span></div>

            @if($td->notes)
            <div class="pt-2 border-t"></div>
            <div><span class="text-gray-500">Комментарий:</span></div>
            <div class="whitespace-pre-line">{{ $td->notes }}</div>
            @endif
        </div>

        <div class="bg-white rounded shadow p-5">
            <div class="font-semibold mb-3">Действия</div>

            <div class="flex flex-wrap gap-2">
                @if($td->status === 'new')
                <button wire:click="setStatus('confirmed')" class="px-3 py-2 rounded bg-gray-800 text-white text-sm">
                    Подтвердить
                </button>
                <button wire:click="setStatus('cancelled')" class="px-3 py-2 rounded border text-sm">
                    Отменить
                </button>
                @endif

                @if($td->status === 'confirmed')
                <button wire:click="setStatus('completed')" class="px-3 py-2 rounded bg-gray-800 text-white text-sm">
                    Завершить
                </button>
                <button wire:click="setStatus('no_show')" class="px-3 py-2 rounded border text-sm">
                    Не пришёл
                </button>
                <button wire:click="setStatus('cancelled')" class="px-3 py-2 rounded border text-sm">
                    Отменить
                </button>
                @endif
            </div>

            <div class="mt-4">
                <div class="text-sm text-gray-600">Результат тест-драйва</div>
                <textarea wire:model.defer="result_notes" rows="4" class="mt-1 w-full rounded border-gray-300"></textarea>

                <div class="flex justify-end mt-3">
                    <button wire:click="saveResult" class="px-4 py-2 rounded bg-gray-800 text-white text-sm">
                        Сохранить результат
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>
