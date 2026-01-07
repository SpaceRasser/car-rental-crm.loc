<?php

namespace App\Livewire\Manager\Rentals;

use App\Models\Payment;
use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Component;

class Show extends Component
{
    public int $rentalId;

    // UI состояния
    public bool $showPickupForm = false;
    public bool $showReturnForm = false;

    // Выдача
    public ?int $pickup_mileage_start_km = null;
    public ?int $pickup_fuel_start_percent = null;

    // Возврат
    public ?int $return_mileage_end_km = null;
    public ?int $return_fuel_end_percent = null;
    public string $return_penalty_total = '0.00';

    public function mount(int $rentalId): void
    {
        $this->rentalId = $rentalId;
    }

    public function getRentalProperty(): Rental
    {
        return Rental::with([
            'car',
            'client',
            'extras', // ✅ добавили
            'payments' => fn($q) => $q->orderByDesc('id'),
        ])->findOrFail($this->rentalId);
    }


    // -------------------------
    // Статусы (как было)
    // -------------------------
    public function setStatus(string $status): void
    {
        $rental = $this->rental;

        $allowed = ['new', 'confirmed', 'active', 'closed', 'cancelled', 'overdue'];
        if (!in_array($status, $allowed, true)) {
            return;
        }

        $current = $rental->status;

        $map = [
            'new'       => ['confirmed', 'cancelled'],
            'confirmed' => ['active', 'cancelled'],
            'active'    => ['closed', 'overdue'],
            'overdue'   => ['closed'],
            'closed'    => [],
            'cancelled' => [],
        ];

        if (!in_array($status, $map[$current] ?? [], true)) {
            session()->flash('rental_error', "Нельзя сменить статус с '{$current}' на '{$status}'.");
            return;
        }

        $rental->update(['status' => $status]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($rental)
            ->event('status_changed')
            ->withProperties(['from' => $current, 'to' => $status])
            ->log("Статус аренды #{$rental->id}: {$current} → {$status}");


        // синхронизация статуса авто
        if ($rental->car) {
            if ($status === 'active') {
                $rental->car->update(['status' => 'rented']);
            }
            if (in_array($status, ['closed', 'cancelled'], true)) {
                $rental->car->update(['status' => 'available']);
            }
        }

        session()->flash('rental_success', 'Статус обновлён.');
    }

    // -------------------------
    // Выдача / Возврат
    // -------------------------
    public function openPickup(): void
    {
        $rental = $this->rental;

        if ($rental->status !== 'confirmed') {
            session()->flash('rental_error', 'Выдача доступна только для статуса "Подтверждена".');
            return;
        }

        $this->resetValidation();
        $this->showReturnForm = false;
        $this->showPickupForm = true;

        // если уже есть данные — подставим
        $this->pickup_mileage_start_km = $rental->mileage_start_km;
        $this->pickup_fuel_start_percent = $rental->fuel_start_percent;
    }

    public function cancelPickup(): void
    {
        $this->resetValidation();
        $this->showPickupForm = false;
    }

    public function confirmPickup(): void
    {
        $rental = $this->rental;

        if ($rental->status !== 'confirmed') {
            session()->flash('rental_error', 'Выдача доступна только для статуса "Подтверждена".');
            return;
        }

        $data = $this->validate([
            'pickup_mileage_start_km' => ['required', 'integer', 'min:0'],
            'pickup_fuel_start_percent' => ['required', 'integer', 'min:0', 'max:100'],
        ], [], [
            'pickup_mileage_start_km' => 'Пробег при выдаче',
            'pickup_fuel_start_percent' => 'Топливо при выдаче',
        ]);

        $rental->update([
            'picked_up_at' => now(),
            'mileage_start_km' => (int) $data['pickup_mileage_start_km'],
            'fuel_start_percent' => (int) $data['pickup_fuel_start_percent'],
            'status' => 'active',
        ]);

        if ($rental->car) {
            $rental->car->update(['status' => 'rented']);
        }

        $this->showPickupForm = false;

        session()->flash('rental_success', 'Авто выдано. Аренда активирована.');
    }

    public function openReturn(): void
    {
        $rental = $this->rental;

        if (!in_array($rental->status, ['active', 'overdue'], true)) {
            session()->flash('rental_error', 'Возврат доступен только для "Активна/Просрочена".');
            return;
        }

        $this->resetValidation();
        $this->showPickupForm = false;
        $this->showReturnForm = true;

        $this->return_mileage_end_km = $rental->mileage_end_km;
        $this->return_fuel_end_percent = $rental->fuel_end_percent;
        $this->return_penalty_total = (string) ($rental->penalty_total ?? '0.00');
    }

    public function cancelReturn(): void
    {
        $this->resetValidation();
        $this->showReturnForm = false;
    }

    public function confirmReturn(): void
    {
        $rental = $this->rental;

        if (!in_array($rental->status, ['active', 'overdue'], true)) {
            session()->flash('rental_error', 'Возврат доступен только для "Активна/Просрочена".');
            return;
        }

        $data = $this->validate([
            'return_mileage_end_km' => ['required', 'integer', 'min:0'],
            'return_fuel_end_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'return_penalty_total' => ['nullable', 'numeric', 'min:0'],
        ], [], [
            'return_mileage_end_km' => 'Пробег при возврате',
            'return_fuel_end_percent' => 'Топливо при возврате',
            'return_penalty_total' => 'Штрафы/доплаты',
        ]);

        $mileageEnd = (int) $data['return_mileage_end_km'];
        $mileageStart = (int) ($rental->mileage_start_km ?? 0);

        if ($rental->mileage_start_km !== null && $mileageEnd < $mileageStart) {
            $this->addError('return_mileage_end_km', 'Пробег при возврате не может быть меньше пробега при выдаче.');
            return;
        }

        $penalty = round((float) ($data['return_penalty_total'] ?? 0), 2);

        // пересчёт grand_total: base - discount + penalty
        $base = (float) ($rental->base_total ?? 0);
        $discount = (float) ($rental->discount_total ?? 0);
        $grand = round(max(0, $base - $discount + $penalty), 2);

        $rental->update([
            'returned_at' => now(),
            'mileage_end_km' => $mileageEnd,
            'fuel_end_percent' => (int) $data['return_fuel_end_percent'],
            'penalty_total' => $penalty,
            'grand_total' => $grand,
            'status' => 'closed',
        ]);

        if ($rental->car) {
            $rental->car->update(['status' => 'available']);
        }

        $this->showReturnForm = false;

        session()->flash('rental_success', 'Аренда закрыта. Возврат зафиксирован.');
    }

    // -------------------------
    // Платежи (как у тебя уже правильно)
    // -------------------------
    public function createPayment(): void
    {
        $rental = $this->rental;

        // ✅ дни: берем слепок, иначе считаем как в Form (ceil по суткам)
        $days = (int) ($rental->days_count ?? 0);
        if ($days <= 0 && $rental->starts_at && $rental->ends_at) {
            $from = \Carbon\Carbon::parse($rental->starts_at);
            $to   = \Carbon\Carbon::parse($rental->ends_at);

            $minutes = max(1, $from->diffInMinutes($to));
            $days = max(1, (int) ceil($minutes / 1440));
        }
        if ($days <= 0) $days = 1;

        $base     = (float) ($rental->base_total ?? 0);
        $discount = (float) ($rental->discount_total ?? 0);
        $penalty  = (float) ($rental->penalty_total ?? 0);
        $deposit  = (float) ($rental->deposit_amount ?? 0);

        // ✅ услуги из pivot rental_extras (fixed/per_day, qty)
        $extrasTotal = (float) $rental->extras->sum(function ($e) use ($days) {
            $type  = (string) ($e->pivot->pricing_type ?? 'fixed');
            $price = (float) ($e->pivot->price ?? 0);
            $qty   = (int)   ($e->pivot->qty ?? 1);

            $mult = $type === 'per_day' ? $days : 1;

            return round($price * $qty * $mult, 2);
        });

        // ✅ аренда итого (без депозита)
        $rentTotal = round(max(0, $base + $extrasTotal - $discount + $penalty), 2);

        // ✅ всего к оплате (с депозитом)
        $total = round($rentTotal + $deposit, 2);

        // ✅ сколько уже реально оплачено
        $paid = (float) $rental->payments()
            ->where('status', 'paid')
            ->sum('amount');

        $remaining = round(max(0, $total - $paid), 2);

        if ($remaining <= 0) {
            session()->flash('payment_error', 'Эта аренда уже полностью оплачена.');
            return;
        }

        \App\Models\Payment::create([
            'rental_id' => $rental->id,
            'amount' => $remaining,
            'currency' => 'RUB',
            'provider' => 'fake',
            'status' => 'pending',

            // ✅ обязательное поле в твоей БД
            'payment_reference' => 'INV-' . now()->format('YmdHis') . '-' . random_int(1000, 9999),

            'external_id' => null,
            'paid_at' => null,
            'created_by' => auth()->id(),
        ]);

        session()->flash('payment_success', 'Платёж создан (pending).');
    }


    public function simulateSuccess(int $paymentId): void
    {
        $payment = Payment::where('rental_id', $this->rentalId)->findOrFail($paymentId);

        if ($payment->status === 'paid') {
            return;
        }

        $payment->update([
            'status'            => 'paid',
            'external_id'       => 'fake_' . Str::uuid(),
            'payment_reference' => $payment->payment_reference ?: ('PAY-' . now()->format('YmdHis') . '-' . random_int(1000, 9999)),
            'paid_at'           => now(),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($payment)
            ->event('payment_paid')
            ->withProperties(['rental_id' => $payment->rental_id, 'amount' => $payment->amount])
            ->log("Платёж #{$payment->id} помечен как PAID");


        $rental = $this->rental;
        if ($rental->status === 'new') {
            $rental->update(['status' => 'confirmed']);
        }

        session()->flash('payment_success', 'Оплата успешно симулирована.');
    }

    public function simulateFail(int $paymentId): void
    {
        $payment = Payment::where('rental_id', $this->rentalId)->findOrFail($paymentId);

        if ($payment->status === 'paid') {
            session()->flash('payment_error', 'Нельзя “провалить” уже оплаченный платёж.');
            return;
        }

        $payment->update([
            'status'            => 'failed',
            'external_id'       => 'fake_' . Str::uuid(),
            'payment_reference' => $payment->payment_reference ?: ('FAIL-' . now()->format('YmdHis') . '-' . random_int(1000, 9999)),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($payment)
            ->event('payment_failed')
            ->withProperties(['rental_id' => $payment->rental_id, 'amount' => $payment->amount])
            ->log("Платёж #{$payment->id} помечен как FAILED");


        session()->flash('payment_success', 'Платёж помечен как failed.');
    }

    public function render()
    {
        $rental = $this->rental;

        // ✅ дни: берем слепок, иначе считаем как в Form (ceil по суткам)
        $days = (int) ($rental->days_count ?? 0);
        if ($days <= 0 && $rental->starts_at && $rental->ends_at) {
            $from = \Carbon\Carbon::parse($rental->starts_at);
            $to   = \Carbon\Carbon::parse($rental->ends_at);

            $minutes = max(1, $from->diffInMinutes($to));
            $days = max(1, (int) ceil($minutes / 1440));
        }
        if ($days <= 0) $days = 1;

        $base    = (float) ($rental->base_total ?? 0);
        $discount = (float) ($rental->discount_total ?? 0);
        $penalty  = (float) ($rental->penalty_total ?? 0);

        // ✅ строки услуг + сумма услуг
        $extrasLines = $rental->extras->map(function ($e) use ($days) {
            $type = (string) ($e->pivot->pricing_type ?? 'fixed'); // fixed/per_day
            $price = (float) ($e->pivot->price ?? 0);
            $qty = (int) ($e->pivot->qty ?? 1);
            $mult = $type === 'per_day' ? $days : 1;

            $amount = round($price * $qty * $mult, 2);

            return (object) [
                'id' => $e->id,
                'name' => $e->name,
                'pricing_type' => $type,
                'price' => $price,
                'qty' => $qty,
                'days' => $days,
                'amount' => $amount,
            ];
        })->values();

        $extrasTotal = round((float) $extrasLines->sum('amount'), 2);

        // ✅ аренда итого (база + услуги - скидка + штрафы)
        $rentTotal = round(max(0, $base + $extrasTotal - $discount + $penalty), 2);

        $deposit = (float) ($rental->deposit_amount ?? 0);
        $total = round($rentTotal + $deposit, 2);

        $paid = (float) $rental->payments->where('status', 'paid')->sum('amount');
        $remaining = round(max(0, $total - $paid), 2);

        $statusLabels = [
            'new' => 'Новая',
            'confirmed' => 'Подтверждена',
            'active' => 'Активна',
            'closed' => 'Закрыта',
            'cancelled' => 'Отменена',
            'overdue' => 'Просрочена',
        ];

        return view('livewire.manager.rentals.show', compact(
            'rental',
            'days',
            'base',
            'extrasLines',
            'extrasTotal',
            'discount',
            'penalty',
            'rentTotal',
            'deposit',
            'paid',
            'total',
            'remaining',
            'statusLabels'
        ));
    }

}
