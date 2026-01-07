<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('test_drives', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Связи
            $table->foreignId('car_id')
                ->constrained('cars')
                ->cascadeOnDelete();

            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnDelete();

            // Кто ведёт (менеджер)
            $table->foreignId('manager_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Статусы тест-драйва
            // allowed: new, confirmed, completed, no_show, canceled
            $table->string('status', 20)->default('new')->index();

            // Планирование слота
            $table->dateTime('scheduled_at')->index();               // дата/время начала
            $table->unsignedSmallInteger('duration_minutes')->default(30); // длительность слота

            // Факт (по желанию фиксировать)
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();

            // Данные, которые обычно спрашивают на тест-драйв
            $table->unsignedSmallInteger('driving_experience_years')->nullable(); // стаж
            $table->string('phone', 30)->nullable(); // если хотим зафиксить на момент заявки
            $table->string('email', 120)->nullable();

            // Результаты тест-драйва (для CRM)
            $table->boolean('is_interested')->default(false)->index(); // заинтересован в покупке
            $table->unsignedTinyInteger('interest_score')->nullable(); // 1..10 (опционально)
            $table->text('feedback')->nullable();                      // отзыв клиента
            $table->text('notes')->nullable();                         // заметки менеджера
            $table->text('cancel_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Быстрые выборки по календарю
            $table->index(['car_id', 'scheduled_at']);
            $table->index(['client_id', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_drives');
    }
};
