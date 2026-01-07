<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Client\RentalCatalogController;
use App\Http\Controllers\Client\TestDriveCatalogController;
use App\Http\Controllers\Client\PaymentsController;
use App\Http\Controllers\Manager\CatalogController;
use App\Models\Client;
use App\Models\Extra;
use App\Models\Rental;
use App\Models\TestDrive;
use Illuminate\Support\Facades\Route;
use App\Models\Car;
use App\Http\Controllers\Manager\RentalContractController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/dashboard', function () {
    $role = auth()->user()->role;

    return match ($role) {
        'admin'   => redirect()->route('admin.dashboard'),
        'manager' => redirect()->route('manager.dashboard'),
        default   => redirect()->route('client.dashboard'),
    };
})->middleware(['auth'])->name('dashboard');


Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('/dashboard', 'dashboards.admin')->name('dashboard');
    Route::view('/users', 'admin.users.index')->name('users.index');
    Route::view('/settings/company', 'admin.settings.company')->name('settings.company');
    Route::view('/payments', 'admin.payments.index')->name('payments.index');
    Route::view('/audit-logs', 'admin.audit-logs.index')->name('audit-logs.index');
    Route::view('/extras', 'admin.extras.index')->name('extras.index');
    Route::view('/extras/create', 'admin.extras.create')->name('extras.create');
    Route::get('/extras/{extra}/edit', function (Extra $extra) {
        return view('admin.extras.edit', compact('extra'));
    })->name('extras.edit');
});

Route::middleware(['auth', 'role:admin,manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::view('/dashboard', 'dashboards.manager')->name('dashboard');
    Route::get('/catalog/rentals', [CatalogController::class, 'rentals'])->name('catalog.rentals');
    Route::get('/catalog/test-drives', [CatalogController::class, 'testDrives'])->name('catalog.test-drives');
    Route::view('/cars', 'manager.cars.index')->name('cars.index');
    Route::view('/cars/create', 'manager.cars.create')->name('cars.create');
    Route::get('/cars/{car}/edit', function (Car $car) {
        return view('manager.cars.edit', compact('car'));
    })->name('cars.edit');
    Route::get('/cars/{car}', function (Car $car) {
        $car->load(['mainPhoto', 'photos', 'services']); // на будущее
        return view('manager.cars.show', compact('car'));
    })->name('cars.show');
    Route::view('/clients', 'manager.clients.index')->name('clients.index');
    Route::view('/clients/create', 'manager.clients.create')->name('clients.create');
    Route::get('/clients/{client}/edit', function (Client $client) {
        return view('manager.clients.edit', compact('client'));
    })->name('clients.edit');
    Route::view('/rentals', 'manager.rentals.index')->name('rentals.index');
    Route::view('/rentals/create', 'manager.rentals.create')->name('rentals.create');
    Route::get('/rentals/{rental}', function (Rental $rental) {
        $rental->load(['car', 'client']);
        return view('manager.rentals.show', compact('rental'));
    })->name('rentals.show');
    Route::view('/test-drives', 'manager.test-drives.index')->name('test-drives.index');
    Route::view('/test-drives/create', 'manager.test-drives.create')->name('test-drives.create');
    Route::get('/test-drives/{testDrive}', function (TestDrive $testDrive) {
        $testDrive->load(['client', 'car', 'manager']);
        return view('manager.test-drives.show', compact('testDrive'));
    })->name('test-drives.show');
    Route::get('/clients/{client}', function (Client $client) {
        $client->load([
            'carAssignments' => fn($q) => $q->with('car')->orderByDesc('id'),
            'rentals' => fn($q) => $q
                ->with(['car', 'payments' => fn($p) => $p->orderByDesc('id')])
                ->orderByDesc('id')
                ->limit(10),

            'testDrives' => fn($q) => $q
                ->with(['car', 'manager'])
                ->orderByDesc('scheduled_at')
                ->limit(10),
        ]);

        return view('manager.clients.show', compact('client'));
    })->name('clients.show');
    Route::view('/calendar', 'manager.calendar.index')->name('calendar.index');
    Route::get('/rentals/{rental}/contract', [RentalContractController::class, 'show'])
        ->name('rentals.contract');
});

Route::middleware(['auth', 'role:client'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [RentalCatalogController::class, 'index'])->name('dashboard');
    Route::get('/catalog/rentals', [RentalCatalogController::class, 'index'])->name('catalog.rentals');
    Route::get('/catalog/rentals/{car}', [RentalCatalogController::class, 'show'])->name('catalog.rentals.show');
    Route::post('/catalog/rentals/{car}/book', [RentalCatalogController::class, 'book'])->name('catalog.rentals.book');

    Route::get('/catalog/test-drives', [TestDriveCatalogController::class, 'index'])->name('catalog.test-drives');
    Route::get('/catalog/test-drives/{car}', [TestDriveCatalogController::class, 'show'])->name('catalog.test-drives.show');
    Route::post('/catalog/test-drives/{car}/book', [TestDriveCatalogController::class, 'book'])->name('catalog.test-drives.book');

    Route::post('/rentals/{rental}/pay', [PaymentsController::class, 'payRental'])->name('rentals.pay');
});

// профиль оставляем как есть
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/client', [ProfileController::class, 'updateClient'])->name('profile.client.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
