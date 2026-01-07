<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Client;
use App\Models\Rental;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RentalCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $query = Car::query()
            ->where('is_active', true)
            ->where('status', 'available')
            ->with(['mainPhoto', 'photos']);

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('plate_number', 'like', "%{$search}%");
            });
        }

        if ($brand = $request->string('brand')->toString()) {
            $query->where('brand', $brand);
        }

        if ($transmission = $request->string('transmission')->toString()) {
            $query->where('transmission', $transmission);
        }

        if ($fuelType = $request->string('fuel_type')->toString()) {
            $query->where('fuel_type', $fuelType);
        }

        if ($priceMin = $request->string('price_min')->toString()) {
            $query->where('daily_price', '>=', (float) $priceMin);
        }

        if ($priceMax = $request->string('price_max')->toString()) {
            $query->where('daily_price', '<=', (float) $priceMax);
        }

        $cars = $query->orderBy('daily_price')->paginate(12)->withQueryString();

        return view('client.catalog.rentals.index', [
            'cars' => $cars,
            'brands' => Car::query()
                ->where('is_active', true)
                ->where('status', 'available')
                ->select('brand')
                ->distinct()
                ->orderBy('brand')
                ->pluck('brand'),
            'transmissions' => Car::query()
                ->where('is_active', true)
                ->where('status', 'available')
                ->select('transmission')
                ->whereNotNull('transmission')
                ->distinct()
                ->orderBy('transmission')
                ->pluck('transmission'),
            'fuelTypes' => Car::query()
                ->where('is_active', true)
                ->where('status', 'available')
                ->select('fuel_type')
                ->whereNotNull('fuel_type')
                ->distinct()
                ->orderBy('fuel_type')
                ->pluck('fuel_type'),
        ]);
    }

    public function show(Car $car): View
    {
        $this->ensureCarAvailable($car);
        $car->load(['photos', 'mainPhoto']);

        return view('client.catalog.rentals.show', [
            'car' => $car,
        ]);
    }

    public function book(Request $request, Car $car): RedirectResponse
    {
        $this->ensureCarAvailable($car);
        $client = Client::query()->where('user_id', $request->user()->id)->first();

        if (!$client) {
            return redirect()->route('profile.edit')
                ->with('profile_incomplete', 'Для бронирования заполните данные клиента в профиле.');
        }

        $missingFields = $client->missingRequiredProfileFields();
        if (!empty($missingFields)) {
            return redirect()->route('profile.edit')
                ->with('profile_incomplete', 'Заполните обязательные поля: '.implode(', ', $missingFields).'.');
        }

        $data = $request->validate([
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        Rental::create([
            'car_id' => $car->id,
            'client_id' => $client->id,
            'status' => 'new',
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'daily_price' => $car->daily_price,
            'deposit_amount' => $car->deposit_amount ?? 0,
            'notes' => $data['notes'] ?? null,
            'purpose' => 'Запрос из клиентского каталога',
        ]);

        return redirect()
            ->route('client.catalog.rentals.show', $car)
            ->with('booking_success', 'Запрос на аренду отправлен. Менеджер свяжется с вами.');
    }

    private function ensureCarAvailable(Car $car): void
    {
        if (!$car->is_active || $car->status !== 'available') {
            abort(404);
        }
    }
}
