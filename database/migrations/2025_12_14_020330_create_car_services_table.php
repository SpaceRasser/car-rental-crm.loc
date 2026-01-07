<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('car_services', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('car_id')
                ->constrained('cars')
                ->cascadeOnDelete();

            // Кто оформил (менеджер/админ)
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Тип обслуживания
            // allowed: maintenance, repair, inspection, accident
            $table->string('kind', 20)->default('maintenance')->index();

            // Статус работ
            // allowed: planned, in_progress, done, canceled
            $table->string('status', 20)->default('planned')->index();

            // Период
            $table->dateTime('starts_at')->nullable()->index();
            $table->dateTime('ends_at')->nullable()->index();

            // Финансы/описание
            $table->decimal('cost', 10, 2)->default(0);
            $table->text('description')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['car_id', 'status']);
            $table->index(['car_id', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_services');
    }
};
