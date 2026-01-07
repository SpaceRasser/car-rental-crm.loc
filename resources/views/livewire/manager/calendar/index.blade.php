<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

        <div class="bg-white rounded shadow p-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <button wire:click="prevMonth" class="px-3 py-2 rounded border text-sm">←</button>
                <div class="font-semibold">
                    {{ $monthObj->translatedFormat('F Y') }}
                </div>
                <button wire:click="nextMonth" class="px-3 py-2 rounded border text-sm">→</button>

                <button wire:click="goToday" class="ml-2 px-3 py-2 rounded bg-gray-800 text-white text-sm">
                    Сегодня
                </button>
            </div>

            <div class="flex items-center gap-4 text-sm">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" wire:model.live="showRentals" class="rounded border-gray-300">
                    Аренды
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" wire:model.live="showTestDrives" class="rounded border-gray-300">
                    Тест-драйвы
                </label>
            </div>
        </div>

        {{-- Сетка календаря --}}
        <div class="bg-white rounded shadow p-4">
            <div class="grid grid-cols-7 gap-2 text-xs text-gray-500 mb-2">
                @foreach($weekdays as $w)
                <div class="text-center font-semibold">{{ $w }}</div>
                @endforeach
            </div>

            <div class="grid grid-cols-7 gap-2">
                @foreach($days as $d)
                @php
                $key = $d->format('Y-m-d');
                $inMonth = $d->format('Y-m') === $monthObj->format('Y-m');
                $isSelected = $key === $selected->format('Y-m-d');

                $rCount = count($events[$key]['rentals'] ?? []);
                $tCount = count($events[$key]['test_drives'] ?? []);
                @endphp

                <button type="button"
                        wire:click="selectDate('{{ $key }}')"
                        class="text-left rounded border p-2 min-h-[78px] hover:bg-gray-50
                                   {{ $inMonth ? '' : 'opacity-40' }}
                                   {{ $isSelected ? 'ring-2 ring-gray-800' : '' }}">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-semibold">{{ $d->day }}</div>
                        @if($key === now()->format('Y-m-d'))
                        <span class="text-[10px] px-2 py-0.5 rounded bg-gray-800 text-white">сегодня</span>
                        @endif
                    </div>

                    <div class="mt-2 space-y-1">
                        @if($rCount)
                        <div class="text-[11px] text-gray-800">🚗 Аренды: <b>{{ $rCount }}</b></div>
                        @endif
                        @if($tCount)
                        <div class="text-[11px] text-gray-800">🕒 Тест-драйвы: <b>{{ $tCount }}</b></div>
                        @endif

                        @if(!$rCount && !$tCount)
                        <div class="text-[11px] text-gray-400">—</div>
                        @endif
                    </div>
                </button>
                @endforeach
            </div>
        </div>

        {{-- События выбранного дня --}}
        <div class="bg-white rounded shadow p-5">
            <div class="font-semibold mb-3">
                События на {{ $selected->format('d.m.Y') }}
            </div>

            @php
            $startDt = $selected->copy()->setTime(10, 0)->format('Y-m-d\TH:i');
            $endDt   = $selected->copy()->addDay()->setTime(10, 0)->format('Y-m-d\TH:i');

            $tdDt    = $selected->copy()->setTime(12, 0)->format('Y-m-d\TH:i');
            @endphp

            <div class="flex flex-wrap gap-2 mb-4">
                <a href="{{ route('manager.rentals.create', ['starts_at' => $startDt, 'ends_at' => $endDt]) }}"
                   class="px-3 py-2 rounded bg-gray-800 text-white text-sm">
                    + Создать аренду
                </a>

                <a href="{{ route('manager.test-drives.create', ['scheduled_at' => $tdDt]) }}"
                   class="px-3 py-2 rounded border text-sm">
                    + Создать тест-драйв
                </a>
            </div>


            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <div>
                    <div class="font-semibold mb-2">🚗 Аренды</div>

                    <div class="space-y-2">
                        @forelse($selectedRentals as $r)
                        <a href="{{ route('manager.rentals.show', $r) }}"
                           class="block rounded border p-3 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="font-medium">Аренда #{{ $r->id }}</div>

                                {{-- ✅ 1) Нормальный статус вместо raw --}}
                                <span class="text-xs px-2 py-1 rounded border">
            {{ $rentalStatus[$r->status] ?? $r->status }}
        </span>
                            </div>

                            <div class="text-sm text-gray-700 mt-1">
                                {{ $r->car?->brand }} {{ $r->car?->model }}
                                <span class="text-xs text-gray-500 font-mono">({{ $r->car?->plate_number ?? '—' }})</span>
                            </div>

                            <div class="text-xs text-gray-500 mt-1">
                                {{ optional($r->starts_at)->format('d.m H:i') ?? '—' }}
                                → {{ optional($r->ends_at)->format('d.m H:i') ?? '—' }}
                            </div>

                            {{-- ✅ 2) ВОТ СЮДА вставляем сумму --}}
                            @php
                            $rent = (float) ($r->grand_total ?? $r->base_total ?? 0);
                            $dep  = (float) ($r->deposit_amount ?? 0);
                            $sum  = $rent + $dep;
                            @endphp
                            <div class="text-xs text-gray-500 mt-1">
                                Сумма: <b>{{ number_format($sum, 2, '.', ' ') }} ₽</b>
                            </div>

                            <div class="text-xs text-gray-500 mt-1">
                                Клиент: {{ $r->client?->full_name ?? '—' }}
                            </div>
                        </a>
                        @empty
                        <div class="text-sm text-gray-500">Нет аренд на этот день</div>
                        @endforelse

                    </div>
                </div>

                <div>
                    <div class="font-semibold mb-2">🕒 Тест-драйвы</div>

                    <div class="space-y-2">
                        @forelse($selectedTestDrives as $t)
                        <a href="{{ route('manager.test-drives.show', $t) }}"
                           class="block rounded border p-3 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="font-medium">Тест-драйв #{{ $t->id }}</div>
                                <span class="text-xs px-2 py-1 rounded border">
                                        {{ $tdStatus[$t->status] ?? $t->status }}
                                    </span>
                            </div>
                            <div class="text-sm text-gray-700 mt-1">
                                {{ $t->car?->brand }} {{ $t->car?->model }}
                                <span class="text-xs text-gray-500 font-mono">({{ $t->car?->plate_number ?? '—' }})</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ optional($t->scheduled_at)->format('H:i') ?? '—' }} • {{ $t->duration_minutes }} мин
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                Клиент: {{ $t->client?->full_name ?? '—' }}
                            </div>
                        </a>
                        @empty
                        <div class="text-sm text-gray-500">Нет тест-драйвов на этот день</div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
