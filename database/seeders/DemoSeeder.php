<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('ru_RU');
        $now = Carbon::now();

        DB::beginTransaction();

        try {
            /** -----------------------------
             * 1) USERS (admin / manager / clients)
             * ------------------------------*/
            $adminId = DB::table('users')->insertGetId([
                'name' => 'Admin',
                'email' => 'admin@car-rental-crm.loc',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $managerId = DB::table('users')->insertGetId([
                'name' => 'Manager',
                'email' => 'manager@car-rental-crm.loc',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $clientUserId = DB::table('users')->insertGetId([
                'name' => 'Client',
                'email' => 'client@car-rental-crm.loc',
                'password' => Hash::make('password'),
                'role' => 'client',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // ещё немного клиентских аккаунтов для демо
            $extraClientUserIds = [];
            for ($i = 0; $i < 10; $i++) {
                $extraClientUserIds[] = DB::table('users')->insertGetId([
                    'name' => $faker->name(),
                    'email' => $faker->unique()->safeEmail(),
                    'password' => Hash::make('password'),
                    'role' => 'client',
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            /** -----------------------------
             * 2) EXTRAS
             * ------------------------------*/
            $extrasSeed = [
                ['name' => 'GPS-навигатор', 'pricing_type' => 'per_day', 'price' => 500],
                ['name' => 'Детское кресло', 'pricing_type' => 'per_day', 'price' => 400],
                ['name' => 'Доп. водитель', 'pricing_type' => 'fixed', 'price' => 1500],
                ['name' => 'Полная страховка', 'pricing_type' => 'per_day', 'price' => 1200],
                ['name' => 'Wi-Fi роутер', 'pricing_type' => 'per_day', 'price' => 300],
                ['name' => 'Мойка при возврате', 'pricing_type' => 'fixed', 'price' => 800],
            ];

            $extraIds = [];
            foreach ($extrasSeed as $e) {
                $extraIds[] = DB::table('extras')->insertGetId([
                    'name' => $e['name'],
                    'description' => null,
                    'pricing_type' => $e['pricing_type'],
                    'price' => $e['price'],
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            /** -----------------------------
             * 3) CARS + PHOTOS
             * ------------------------------*/
            $brands = [
                ['Toyota', ['Camry', 'Corolla', 'RAV4']],
                ['BMW', ['3 Series', '5 Series', 'X5']],
                ['Mercedes', ['C-Class', 'E-Class', 'GLC']],
                ['Audi', ['A4', 'A6', 'Q5']],
                ['Kia', ['K5', 'Sportage', 'Rio']],
                ['Hyundai', ['Sonata', 'Tucson', 'Elantra']],
            ];
            $colors = ['Чёрный', 'Белый', 'Серый', 'Синий', 'Красный', 'Серебро'];
            $fuel = ['Бензин', 'Дизель', 'Гибрид', 'Электро'];
            $trans = ['AT', 'MT', 'CVT'];

            $carIds = [];
            for ($i = 0; $i < 50; $i++) {
                [$brand, $models] = $brands[array_rand($brands)];
                $model = $models[array_rand($models)];

                $carId = DB::table('cars')->insertGetId([
                    'brand' => $brand,
                    'model' => $model,
                    'year' => (int)$faker->numberBetween(2016, 2025),
                    'color' => $colors[array_rand($colors)],
                    'vin' => $this->uniqueVin(),
                    'plate_number' => $this->uniquePlate($i),
                    'fuel_type' => $fuel[array_rand($fuel)],
                    'transmission' => $trans[array_rand($trans)],
                    'mileage_km' => (int)$faker->numberBetween(5_000, 180_000),
                    'daily_price' => (float)$faker->numberBetween(2500, 15000),
                    'deposit_amount' => (float)$faker->numberBetween(0, 20000),
                    'status' => 'available',
                    'description' => $faker->sentence(10),
                    'last_service_at' => $faker->boolean(70) ? $faker->dateTimeBetween('-12 months', 'now') : null,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $carIds[] = $carId;

                // 3 фото, первое — main
                for ($p = 1; $p <= 3; $p++) {
                    DB::table('car_photos')->insert([
                        'car_id' => $carId,
                        'path' => "seed/cars/{$carId}/{$p}.jpg",
                        'sort_order' => $p,
                        'is_main' => $p === 1 ? 1 : 0,
                        'alt' => "{$brand} {$model} photo {$p}",
                        'created_at' => $now,
                        'updated_at' => $now,
                        'deleted_at' => null,
                    ]);
                }
            }

            /** -----------------------------
             * 4) CLIENTS (часть с user_id, часть без)
             * ------------------------------*/
            $clientIds = [];

            // клиент из Breeze-аккаунта
            $clientIds[] = DB::table('clients')->insertGetId([
                'user_id' => $clientUserId,
                'created_by' => $managerId,
                'first_name' => 'Иван',
                'last_name' => 'Клиентов',
                'middle_name' => null,
                'phone' => '+7' . $faker->numerify('9#########'),
                'email' => 'client@car-rental-crm.loc',
                'driver_license_number' => 'DL-' . $faker->numerify('########'),
                'driver_license_issued_at' => $faker->dateTimeBetween('-8 years', '-1 years'),
                'driver_license_expires_at' => $faker->dateTimeBetween('+1 years', '+8 years'),
                'birth_date' => $faker->dateTimeBetween('-45 years', '-20 years'),
                'reliability_status' => 'normal',
                'is_verified' => 1,
                'notes' => 'Демо-клиент (аккаунт).',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);

            // доп. клиенты с аккаунтами
            foreach ($extraClientUserIds as $uid) {
                $fullName = explode(' ', $faker->name());
                $clientIds[] = DB::table('clients')->insertGetId([
                    'user_id' => $uid,
                    'created_by' => $managerId,
                    'first_name' => $fullName[1] ?? $faker->firstName(),
                    'last_name' => $fullName[0] ?? $faker->lastName(),
                    'middle_name' => null,
                    'phone' => '+7' . $faker->numerify('9#########'),
                    'email' => $faker->unique()->safeEmail(),
                    'driver_license_number' => 'DL-' . $faker->numerify('########'),
                    'driver_license_issued_at' => $faker->dateTimeBetween('-8 years', '-1 years'),
                    'driver_license_expires_at' => $faker->dateTimeBetween('+1 years', '+8 years'),
                    'birth_date' => $faker->dateTimeBetween('-55 years', '-18 years'),
                    'reliability_status' => $faker->randomElement(['normal', 'vip', 'blocked']),
                    'is_verified' => $faker->boolean(60),
                    'notes' => $faker->boolean(30) ? $faker->sentence(8) : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]);
            }

            // клиенты без аккаунта (основной объём)
            for ($i = 0; $i < 180; $i++) {
                $clientIds[] = DB::table('clients')->insertGetId([
                    'user_id' => null,
                    'created_by' => $managerId,
                    'first_name' => $faker->firstName(),
                    'last_name' => $faker->lastName(),
                    'middle_name' => $faker->boolean(30) ? $faker->firstNameMale() : null,
                    'phone' => '+7' . $faker->numerify('9#########'),
                    'email' => $faker->boolean(70) ? $faker->unique()->safeEmail() : null,
                    'driver_license_number' => $faker->boolean(70) ? ('DL-' . $faker->numerify('########')) : null,
                    'driver_license_issued_at' => null,
                    'driver_license_expires_at' => null,
                    'birth_date' => $faker->boolean(50) ? $faker->dateTimeBetween('-60 years', '-18 years') : null,
                    'reliability_status' => $faker->randomElement(['normal', 'vip', 'blocked']),
                    'is_verified' => $faker->boolean(25),
                    'notes' => $faker->boolean(20) ? $faker->sentence(10) : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]);
            }

            /** -----------------------------
             * 5) RENTALS + RENTAL_EXTRAS
             * ------------------------------*/
            $rentalIds = [];
            $intervalsByCar = []; // для уменьшения пересечений

            $statuses = ['new', 'confirmed', 'active', 'closed', 'canceled', 'overdue'];
            $contractSeq = 1;

            for ($i = 0; $i < 120; $i++) {
                $carId = $carIds[array_rand($carIds)];
                $clientId = $clientIds[array_rand($clientIds)];

                // берём текущие цены авто как слепок
                $car = DB::table('cars')->where('id', $carId)->first();

                $start = Carbon::now()->addDays($faker->numberBetween(-20, 20))->setTime($faker->randomElement([9, 10, 11, 12, 13, 14, 15]), 0);
                $days = $faker->numberBetween(1, 10);
                $end = (clone $start)->addDays($days)->setTime(18, 0);

                // стараемся избегать пересечений по авто
                $startEnd = $this->findNonOverlappingInterval($intervalsByCar[$carId] ?? [], $start, $end);
                $start = $startEnd['start'];
                $end = $startEnd['end'];

                $status = $faker->randomElement($statuses);

                $daysCount = max(1, $start->diffInDays($end));
                $baseTotal = $daysCount * (float)$car->daily_price;

                $discount = $faker->boolean(20) ? (float)$faker->numberBetween(200, 1500) : 0;
                $penalty  = $faker->boolean(15) ? (float)$faker->numberBetween(300, 5000) : 0;
                $grand    = max(0, $baseTotal - $discount + $penalty);

                $pickedUpAt = null;
                $returnedAt = null;

                if (in_array($status, ['active', 'closed', 'overdue'], true)) {
                    $pickedUpAt = (clone $start)->subHours(1);
                }
                if ($status === 'closed') {
                    $returnedAt = (clone $end)->addHours($faker->numberBetween(-2, 6));
                }
                if ($status === 'overdue') {
                    $returnedAt = null;
                }

                $contractNumber = 'CTR-' . $now->format('Y') . '-' . str_pad((string)$contractSeq++, 6, '0', STR_PAD_LEFT);

                $rentalId = DB::table('rentals')->insertGetId([
                    'car_id' => $carId,
                    'client_id' => $clientId,
                    'manager_id' => $managerId,
                    'status' => $status,
                    'starts_at' => $start,
                    'ends_at' => $end,
                    'picked_up_at' => $pickedUpAt,
                    'returned_at' => $returnedAt,
                    'daily_price' => (float)$car->daily_price,
                    'deposit_amount' => (float)$car->deposit_amount,
                    'days_count' => $daysCount,
                    'base_total' => $baseTotal,
                    'discount_total' => $discount,
                    'penalty_total' => $penalty,
                    'grand_total' => $grand,
                    'mileage_start_km' => $faker->boolean(60) ? (int)$faker->numberBetween(5_000, 200_000) : null,
                    'mileage_end_km' => $faker->boolean(40) ? (int)$faker->numberBetween(5_000, 210_000) : null,
                    'fuel_start_percent' => $faker->boolean(50) ? (int)$faker->numberBetween(10, 100) : null,
                    'fuel_end_percent' => $faker->boolean(40) ? (int)$faker->numberBetween(10, 100) : null,
                    'contract_number' => $contractNumber,
                    'contract_pdf_path' => null,
                    'purpose' => $faker->boolean(50) ? $faker->randomElement(['Личная', 'Работа', 'Путешествие', 'Тест перед покупкой']) : null,
                    'notes' => $faker->boolean(20) ? $faker->sentence(10) : null,
                    'cancel_reason' => $status === 'canceled' ? $faker->sentence(6) : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]);

                $rentalIds[] = $rentalId;
                $intervalsByCar[$carId][] = ['start' => $start, 'end' => $end];

                // rental_extras (0..3)
                $pickedExtras = $faker->randomElements($extraIds, $faker->numberBetween(0, 3));
                foreach ($pickedExtras as $eid) {
                    $extra = DB::table('extras')->where('id', $eid)->first();
                    DB::table('rental_extras')->insert([
                        'rental_id' => $rentalId,
                        'extra_id' => $eid,
                        'pricing_type' => $extra->pricing_type,
                        'price' => (float)$extra->price,
                        'qty' => $faker->numberBetween(1, 2),
                        'created_at' => $now,
                        'updated_at' => $now,
                        'deleted_at' => null,
                    ]);
                }

                // activity log
                DB::table('activity_logs')->insert([
                    'user_id' => $managerId,
                    'subject_type' => 'rental',
                    'subject_id' => $rentalId,
                    'event' => 'created',
                    'description' => 'Создана аренда',
                    'properties' => json_encode(['status' => $status, 'contract_number' => $contractNumber]),
                    'ip' => null,
                    'user_agent' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            /** -----------------------------
             * 6) TEST DRIVES
             * ------------------------------*/
            $tdStatuses = ['new', 'confirmed', 'completed', 'no_show', 'canceled'];

            for ($i = 0; $i < 150; $i++) {
                $carId = $carIds[array_rand($carIds)];
                $clientId = $clientIds[array_rand($clientIds)];

                $scheduled = Carbon::now()
                    ->addDays($faker->numberBetween(-10, 15))
                    ->setTime($faker->randomElement([10, 11, 12, 13, 14, 15, 16, 17]), 0);

                $duration = $faker->randomElement([30, 45, 60]);
                $status = $faker->randomElement($tdStatuses);

                $startedAt = null;
                $endedAt = null;
                if ($status === 'completed') {
                    $startedAt = (clone $scheduled)->addMinutes(5);
                    $endedAt = (clone $startedAt)->addMinutes($duration);
                }

                $isInterested = $status === 'completed' ? $faker->boolean(55) : false;

                $tdId = DB::table('test_drives')->insertGetId([
                    'car_id' => $carId,
                    'client_id' => $clientId,
                    'manager_id' => $managerId,
                    'status' => $status,
                    'scheduled_at' => $scheduled,
                    'duration_minutes' => $duration,
                    'started_at' => $startedAt,
                    'ended_at' => $endedAt,
                    'driving_experience_years' => $faker->boolean(70) ? $faker->numberBetween(0, 25) : null,
                    'phone' => $faker->boolean(40) ? ('+7' . $faker->numerify('9#########')) : null,
                    'email' => $faker->boolean(40) ? $faker->safeEmail() : null,
                    'is_interested' => $isInterested ? 1 : 0,
                    'interest_score' => $isInterested ? $faker->numberBetween(6, 10) : null,
                    'feedback' => $status === 'completed' && $faker->boolean(50) ? $faker->sentence(12) : null,
                    'notes' => $faker->boolean(15) ? $faker->sentence(8) : null,
                    'cancel_reason' => $status === 'canceled' ? $faker->sentence(6) : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]);

                DB::table('activity_logs')->insert([
                    'user_id' => $managerId,
                    'subject_type' => 'test_drive',
                    'subject_id' => $tdId,
                    'event' => 'created',
                    'description' => 'Создан тест-драйв',
                    'properties' => json_encode(['status' => $status]),
                    'ip' => null,
                    'user_agent' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            /** -----------------------------
             * 7) PAYMENTS (fake_gateway)
             * ------------------------------*/
            $paymentStatuses = ['pending', 'paid', 'failed', 'canceled', 'refunded'];
            $kinds = ['rent', 'deposit', 'penalty', 'refund'];

            foreach ($rentalIds as $rid) {
                $rental = DB::table('rentals')->where('id', $rid)->first();

                // 1-3 платежа на аренду
                $count = $faker->numberBetween(1, 3);

                for ($i = 0; $i < $count; $i++) {
                    $kind = $faker->randomElement($kinds);
                    $status = $faker->randomElement($paymentStatuses);

                    $amount = match ($kind) {
                        'deposit' => (float)$rental->deposit_amount,
                        'penalty' => (float)max(0, $rental->penalty_total),
                        'refund'  => (float)$faker->numberBetween(200, 5000),
                        default   => (float)max(0, $rental->grand_total),
                    };

                    if ($amount <= 0) {
                        $amount = (float)$faker->numberBetween(500, 5000);
                    }

                    $paidAt = $status === 'paid' ? Carbon::now()->addDays($faker->numberBetween(-15, 2)) : null;
                    $refundedAt = $status === 'refunded' ? Carbon::now()->addDays($faker->numberBetween(-5, 5)) : null;

                    $paymentRef = 'PAY-' . Str::uuid()->toString();
                    $externalId = in_array($status, ['paid', 'pending'], true)
                        ? 'FGW-' . strtoupper(Str::random(12))
                        : null;

                    $pid = DB::table('payments')->insertGetId([
                        'rental_id' => $rid,
                        'created_by' => $managerId,
                        'kind' => $kind,
                        'provider' => 'fake_gateway',
                        'status' => $status,
                        'amount' => $amount,
                        'currency' => 'RUB',
                        'payment_reference' => $paymentRef,
                        'external_id' => $externalId,
                        'paid_at' => $paidAt,
                        'refunded_at' => $refundedAt,
                        'provider_payload' => json_encode([
                            'gateway' => 'fake_gateway',
                            'reference' => $paymentRef,
                            'external_id' => $externalId,
                        ]),
                        'fail_reason' => $status === 'failed' ? $faker->sentence(6) : null,
                        'created_at' => $now,
                        'updated_at' => $now,
                        'deleted_at' => null,
                    ]);

                    DB::table('activity_logs')->insert([
                        'user_id' => $managerId,
                        'subject_type' => 'payment',
                        'subject_id' => $pid,
                        'event' => 'created',
                        'description' => 'Создан платеж',
                        'properties' => json_encode(['status' => $status, 'kind' => $kind, 'amount' => $amount]),
                        'ip' => null,
                        'user_agent' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            /** -----------------------------
             * 8) CAR SERVICES
             * ------------------------------*/
            $serviceKinds = ['maintenance', 'repair', 'inspection', 'accident'];
            $serviceStatuses = ['planned', 'in_progress', 'done', 'canceled'];

            for ($i = 0; $i < 40; $i++) {
                $carId = $carIds[array_rand($carIds)];
                $status = $faker->randomElement($serviceStatuses);

                $start = Carbon::now()->addDays($faker->numberBetween(-30, 10))->setTime(10, 0);
                $end = (clone $start)->addDays($faker->numberBetween(0, 5))->setTime(18, 0);

                DB::table('car_services')->insert([
                    'car_id' => $carId,
                    'created_by' => $managerId,
                    'kind' => $faker->randomElement($serviceKinds),
                    'status' => $status,
                    'starts_at' => $start,
                    'ends_at' => $end,
                    'cost' => (float)$faker->numberBetween(0, 50000),
                    'description' => $faker->sentence(10),
                    'notes' => $faker->boolean(25) ? $faker->sentence(8) : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]);
            }

            /** -----------------------------
             * 9) Проставим текущие статусы cars (упрощённо)
             * ------------------------------*/
            foreach ($carIds as $carId) {
                $hasActiveRental = DB::table('rentals')
                    ->where('car_id', $carId)
                    ->whereIn('status', ['active', 'confirmed'])
                    ->where('starts_at', '<=', Carbon::now())
                    ->where('ends_at', '>=', Carbon::now())
                    ->exists();

                $hasService = DB::table('car_services')
                    ->where('car_id', $carId)
                    ->whereIn('status', ['planned', 'in_progress'])
                    ->where('starts_at', '<=', Carbon::now())
                    ->where('ends_at', '>=', Carbon::now())
                    ->exists();

                $status = 'available';
                if ($hasService) $status = 'maintenance';
                else if ($hasActiveRental) $status = 'rented';

                DB::table('cars')->where('id', $carId)->update([
                    'status' => $status,
                    'updated_at' => $now,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function uniqueVin(): string
    {
        // 17 символов, без I/O/Q (чтобы выглядело как VIN)
        $chars = 'ABCDEFGHJKLMNPRSTUVWXYZ0123456789';
        $vin = '';
        for ($i = 0; $i < 17; $i++) {
            $vin .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $vin;
    }

    private function uniquePlate(int $i): string
    {
        // Уникальность обеспечим индексом + добавим счётчик
        // Пример: A123BC-07
        $letters = 'ABCEHKMOPTX';
        $p1 = $letters[random_int(0, strlen($letters) - 1)];
        $p2 = $letters[random_int(0, strlen($letters) - 1)];
        $p3 = $letters[random_int(0, strlen($letters) - 1)];
        $num = str_pad((string)random_int(1, 999), 3, '0', STR_PAD_LEFT);
        $reg = str_pad((string)(($i % 99) + 1), 2, '0', STR_PAD_LEFT);

        return "{$p1}{$num}{$p2}{$p3}-{$reg}";
    }

    private function findNonOverlappingInterval(array $intervals, Carbon $start, Carbon $end): array
    {
        // Пытаемся сдвинуть интервал, чтобы не пересекался (до 15 попыток)
        for ($t = 0; $t < 15; $t++) {
            $overlap = false;

            foreach ($intervals as $iv) {
                /** @var Carbon $s */
                /** @var Carbon $e */
                $s = $iv['start'];
                $e = $iv['end'];

                if ($start < $e && $end > $s) {
                    $overlap = true;
                    break;
                }
            }

            if (!$overlap) {
                return ['start' => $start, 'end' => $end];
            }

            // сдвигаем на +1..+3 дня
            $shift = random_int(1, 3);
            $start = (clone $start)->addDays($shift);
            $end = (clone $end)->addDays($shift);
        }

        return ['start' => $start, 'end' => $end];
    }
}
