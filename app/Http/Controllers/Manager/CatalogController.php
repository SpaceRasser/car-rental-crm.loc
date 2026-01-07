<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function rentals(Request $request): View
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

        return view('manager.catalog.rentals.index', [
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

    public function testDrives(Request $request): View
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

        $cars = $query->orderBy('brand')->paginate(12)->withQueryString();

        return view('manager.catalog.test-drives.index', [
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
}
