<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('car_photos', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('car_id')
                ->constrained('cars')
                ->cascadeOnDelete();

            // Путь до файла в storage (например: cars/12/main.jpg)
            $table->string('path', 255);

            // Для сортировки в галерее
            $table->unsignedInteger('sort_order')->default(0)->index();

            // Главная фотка (для карточек/списка)
            $table->boolean('is_main')->default(false)->index();

            // Опционально
            $table->string('alt', 150)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['car_id', 'is_main']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_photos');
    }
};
