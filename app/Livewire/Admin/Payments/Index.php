<?php

namespace App\Livewire\Admin\Payments;

use App\Models\Payment;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $q = '';
    public string $status = '';
    public string $provider = '';
    public string $from = ''; // YYYY-MM-DD
    public string $to = '';   // YYYY-MM-DD
    public int $perPage = 15;

    protected $queryString = [
        'q' => ['except' => ''],
        'status' => ['except' => ''],
        'provider' => ['except' => ''],
        'from' => ['except' => ''],
        'to' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updated($name): void
    {
        if (in_array($name, ['q', 'status', 'provider', 'from', 'to', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->q = '';
        $this->status = '';
        $this->provider = '';
        $this->from = '';
        $this->to = '';
        $this->perPage = 15;
        $this->resetPage();
    }

    public function simulateSuccess(int $paymentId): void
    {
        $payment = Payment::query()->findOrFail($paymentId);

        if ($payment->status === 'paid') {
            return;
        }

        $payment->update([
            'status'      => 'paid',
            'external_id' => $payment->external_id ?: ('fake_' . Str::uuid()),
            'paid_at'     => now(),
            // обязательное поле в твоей БД
            'payment_reference' => $payment->payment_reference ?: ('PAY-' . now()->format('YmdHis') . '-' . random_int(1000, 9999)),
        ]);

        session()->flash('success', "Платёж #{$payment->id} помечен как PAID.");
    }

    public function simulateFail(int $paymentId): void
    {
        $payment = Payment::query()->findOrFail($paymentId);

        if ($payment->status === 'paid') {
            session()->flash('error', 'Нельзя “провалить” уже оплаченный платёж.');
            return;
        }

        $payment->update([
            'status'      => 'failed',
            'external_id' => $payment->external_id ?: ('fake_' . Str::uuid()),
            'payment_reference' => $payment->payment_reference ?: ('FAIL-' . now()->format('YmdHis') . '-' . random_int(1000, 9999)),
        ]);

        session()->flash('success', "Платёж #{$payment->id} помечен как FAILED.");
    }

    public function render()
    {
        $statuses = [
            'pending' => 'Ожидает',
            'paid' => 'Оплачен',
            'failed' => 'Ошибка',
            'cancelled' => 'Отменён',
        ];

        $query = Payment::query()
            ->with([
                'rental' => fn ($q) => $q->with(['car', 'client']),
            ]);

        if ($this->q !== '') {
            $q = trim($this->q);

            $query->where(function ($qq) use ($q) {
                $qq->where('id', $q)
                    ->orWhere('payment_reference', 'like', "%{$q}%")
                    ->orWhere('external_id', 'like', "%{$q}%")
                    ->orWhereHas('rental', function ($qr) use ($q) {
                        $qr->where('id', $q)
                            ->orWhereHas('car', fn($qc) => $qc->where('brand','like',"%{$q}%")
                                ->orWhere('model','like',"%{$q}%")
                                ->orWhere('plate_number','like',"%{$q}%"))
                            ->orWhereHas('client', fn($qcl) => $qcl->where('first_name','like',"%{$q}%")
                                ->orWhere('last_name','like',"%{$q}%")
                                ->orWhere('phone','like',"%{$q}%")
                                ->orWhere('email','like',"%{$q}%"));
                    });
            });
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->provider !== '') {
            $query->where('provider', $this->provider);
        }

        if ($this->from !== '') {
            $query->whereDate('created_at', '>=', $this->from);
        }

        if ($this->to !== '') {
            $query->whereDate('created_at', '<=', $this->to);
        }

        $payments = (clone $query)
            ->orderByDesc('id')
            ->paginate($this->perPage);

        // суммы по текущей выборке (без пагинации)
        $paidSum = (clone $query)->where('status', 'paid')->sum('amount');
        $pendingSum = (clone $query)->where('status', 'pending')->sum('amount');

        $providers = Payment::query()
            ->select('provider')
            ->distinct()
            ->orderBy('provider')
            ->pluck('provider')
            ->filter()
            ->values()
            ->all();

        return view('livewire.admin.payments.index', compact(
            'payments',
            'statuses',
            'providers',
            'paidSum',
            'pendingSum',
        ));
    }
}
