<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Rental;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentsController extends Controller
{
    public function payRental(Request $request, Rental $rental): RedirectResponse
    {
        $client = Client::query()->where('user_id', $request->user()->id)->first();

        if (!$client || $rental->client_id !== $client->id) {
            abort(403);
        }

        $paid = (float) $rental->payments()->where('status', 'paid')->sum('amount');
        $total = (float) (($rental->grand_total ?? 0) + ($rental->deposit_amount ?? 0));
        $remaining = round(max(0, $total - $paid), 2);

        if ($remaining <= 0) {
            return redirect()->route('profile.edit')->with('status', 'payment-already-paid');
        }

        Payment::create([
            'rental_id' => $rental->id,
            'kind' => 'rent',
            'provider' => 'fake_gateway',
            'status' => 'paid',
            'amount' => $remaining,
            'currency' => 'RUB',
            'payment_reference' => Str::uuid()->toString(),
            'external_id' => 'FAKE-'.Str::upper(Str::random(10)),
            'paid_at' => now(),
            'provider_payload' => ['gateway' => 'fake'],
        ]);

        return redirect()->route('profile.edit')->with('status', 'payment-paid');
    }
}
