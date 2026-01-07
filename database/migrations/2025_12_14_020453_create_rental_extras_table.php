<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rental_extras', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('rental_id')
                ->constrained('rentals')
                ->cascadeOnDelete();

            $table->foreignId('extra_id')
                ->constrained('extras')
                ->cascadeOnDelete();

            // Слепок цены на момент выбора (чтобы не ломалась история)
            $table->string('pricing_type', 20); // per_day/fixed
            $table->decimal('price', 10, 2);    // цена за единицу

            // Кол-во (например 2 детских кресла)
            $table->unsignedSmallInteger('qty')->default(1);

            $table->timestamps();
            $table->softDeletes();

            // Чтобы не добавить один и тот же extra дважды (можно qty менять)
            $table->unique(['rental_id', 'extra_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_extras');
    }
};
