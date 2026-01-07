<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CompanySetting;

class RentalContractController extends Controller
{
    public function show(Rental $rental)
    {
        if (!in_array($rental->status, ['confirmed', 'active', 'closed'], true)) {
            abort(403, 'Договор доступен только для подтверждённых/активных/закрытых аренд.');
        }

        // ✅ сразу грузим всё нужное, включая extras
        $rental->load(['car', 'client', 'payments', 'extras']);

        $groupRentals = collect([$rental]);
        if ($rental->group_uuid) {
            $groupRentals = Rental::query()
                ->with(['car', 'client', 'payments', 'extras'])
                ->where('group_uuid', $rental->group_uuid)
                ->orderBy('id')
                ->get();
        }

        // ✅ оплачено
        $paid = (float) $groupRentals
            ->flatMap(fn ($item) => $item->payments)
            ->where('status', 'paid')
            ->sum('amount');

        // ✅ дни (на всякий)
        $days = (int) ($rental->days_count ?? 1);
        $days = max(1, $days);

        // ✅ база аренды (без доп.услуг и без депозита)
        $base = (float) $groupRentals->sum(fn ($item) => (float) ($item->base_total ?? 0));
        if ($base <= 0) {
            $base = (float) $groupRentals->sum(function ($item) use ($days) {
                $daily = (float) ($item->daily_price ?? 0);
                return round($days * $daily, 2);
            });
        }

        // ✅ доп. услуги (по слепку pivot)
        $extras = $rental->extras ?? collect();

        $extrasTotal = 0.0;

        foreach ($extras as $e) {
            $type = (string) ($e->pivot->pricing_type ?? $e->pricing_type ?? 'fixed');
            $price = (float) ($e->pivot->price ?? $e->price ?? 0);
            $qty = (int) ($e->pivot->qty ?? 1);
            $qty = max(1, $qty);

            $line = $type === 'per_day'
                ? $price * $qty * $days
                : $price * $qty;

            $extrasTotal += $line;
        }

        $extrasTotal = round($extrasTotal, 2);

        // ✅ скидка/штрафы (если используешь)
        $discount = (float) $groupRentals->sum(fn ($item) => (float) ($item->discount_total ?? 0));
        $penalty  = (float) $groupRentals->sum(fn ($item) => (float) ($item->penalty_total ?? 0));

        // ✅ аренда итого (без депозита)
        $rent = (float) $groupRentals->sum(fn ($item) => (float) ($item->grand_total ?? 0));
        if ($rent <= 0) {
            $rent = round($base + $extrasTotal - $discount + $penalty, 2);
        }

        // ✅ депозит
        $deposit = (float) $groupRentals->sum(fn ($item) => (float) ($item->deposit_amount ?? 0));

        // ✅ итог к оплате
        $total = round($rent + $deposit, 2);

        // ✅ остаток
        $remaining = round(max(0, $total - $paid), 2);

        $company = CompanySetting::query()->first();

        $data = compact(
            'rental',
            'groupRentals',
            'company',
            'paid',
            'days',
            'base',
            'extras',
            'extrasTotal',
            'discount',
            'penalty',
            'rent',
            'deposit',
            'total',
            'remaining'
        );

        $pdf = Pdf::loadView('pdf.rental-contract', $data)
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);

        $filename = 'dogovor-arendy-'.$rental->id.'.pdf';

        activity()
            ->causedBy(auth()->user())
            ->performedOn($rental)
            ->event(request()->boolean('download') ? 'contract_downloaded' : 'contract_opened')
            ->withProperties(['download' => request()->boolean('download')])
            ->log(request()->boolean('download') ? "Скачан договор аренды #{$rental->id}" : "Открыт договор аренды #{$rental->id}");

        return request()->boolean('download')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }
}
