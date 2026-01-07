<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Связи
            $table->foreignId('car_id')
                ->constrained('cars')
                ->cascadeOnDelete();

            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnDelete();

            // Кто ведёт аренду (менеджер/админ)
            $table->foreignId('manager_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Статусы аренды
            // allowed: new, confirmed, active, closed, canceled, overdue
            $table->string('status', 20)->default('new')->index();

            // Период аренды (план)
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at')->index();

            // Факт (для закрытия аренды)
            $table->dateTime('picked_up_at')->nullable();
            $table->dateTime('returned_at')->nullable()->index();

            // Финансовые слепки на момент оформления (чтобы история не ломалась, если цены авто поменяются)
            $table->decimal('daily_price', 10, 2);
            $table->decimal('deposit_amount', 10, 2)->default(0);

            // Итоги по аренде (можно считать и хранить)
            $table->unsignedSmallInteger('days_count')->nullable();
            $table->decimal('base_total', 10, 2)->nullable();     // days * daily_price
            $table->decimal('discount_total', 10, 2)->default(0);
            $table->decimal('penalty_total', 10, 2)->default(0);  // штрафы/доплаты
            $table->decimal('grand_total', 10, 2)->nullable();    // base - discount + penalty

            // Приём/возврат авто (на будущее, чтобы не потерять данные при закрытии)
            $table->unsignedInteger('mileage_start_km')->nullable();
            $table->unsignedInteger('mileage_end_km')->nullable();
            $table->unsignedTinyInteger('fuel_start_percent')->nullable(); // 0..100
            $table->unsignedTinyInteger('fuel_end_percent')->nullable();   // 0..100

            // Договор (под твою “вау-фичу” PDF)
            $table->string('contract_number', 50)->nullable()->unique();
            $table->string('contract_pdf_path', 255)->nullable();

            // Доп. инфо
            $table->string('purpose', 255)->nullable();
            $table->text('notes')->nullable();
            $table->text('cancel_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Быстрые выборки по календарю: авто + период
            $table->index(['car_id', 'starts_at', 'ends_at']);
            $table->index(['client_id', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
