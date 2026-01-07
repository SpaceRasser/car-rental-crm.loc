<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Кто сделал действие (менеджер/админ). Может быть null, если системное действие.
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // К чему относится действие (rental/test_drive/car/payment и т.д.)
            $table->string('subject_type', 120)->index();
            $table->unsignedBigInteger('subject_id')->index();

            // Тип события
            // examples: created, updated, status_changed, payment_paid, canceled, pdf_generated
            $table->string('event', 50)->index();

            // Человекочитаемое описание (для UI)
            $table->string('description', 255)->nullable();

            // Данные до/после или любые метаданные (MySQL 5.7 JSON поддерживает)
            $table->json('properties')->nullable();

            // IP / user-agent (по желанию, полезно)
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();

            $table->timestamps();

            $table->index(['subject_type', 'subject_id', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
