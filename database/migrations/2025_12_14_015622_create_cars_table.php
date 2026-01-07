<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Основные данные
            $table->string('brand', 80);                 // Марка
            $table->string('model', 80);                 // Модель
            $table->unsignedSmallInteger('year');        // Год выпуска
            $table->string('color', 50)->nullable();     // Цвет

            // Идентификаторы
            $table->string('vin', 17)->unique();         // VIN (уникальный)
            $table->string('plate_number', 20)->unique(); // Гос. номер (уникальный)

            // Технические характеристики (упрощённо, без отдельных справочников — пока)
            $table->string('fuel_type', 30)->nullable();      // бензин/дизель/электро/гибрид...
            $table->string('transmission', 30)->nullable();   // AT/MT/CVT...
            $table->unsignedInteger('mileage_km')->default(0);// Пробег

            // Финансы
            $table->decimal('daily_price', 10, 2);            // Цена аренды за сутки
            $table->decimal('deposit_amount', 10, 2)->default(0); // Депозит (если нужен)

            // Статус авто в автопарке (важно для аренды/тест-драйва/ремонта)
            // allowed: available, rented, test_drive, maintenance, inactive
            $table->string('status', 20)->default('available')->index();

            // Доп. поля для CRM
            $table->text('description')->nullable();          // Описание/комплектация
            $table->date('last_service_at')->nullable();      // Последнее ТО
            $table->boolean('is_active')->default(true)->index(); // Показывать в каталоге/нет

            $table->timestamps();
            $table->softDeletes(); // чтобы не терять историю связей (аренды/тест-драйвы)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
