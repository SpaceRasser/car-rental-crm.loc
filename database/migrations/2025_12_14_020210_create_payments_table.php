<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Платёж привязан к аренде (в 99% случаев так и будет)
            $table->foreignId('rental_id')
                ->constrained('rentals')
                ->cascadeOnDelete();

            // Кто создал/провёл платёж (менеджер) — опционально
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Тип платежа
            // allowed: rent, deposit, penalty, refund
            $table->string('kind', 20)->default('rent')->index();

            // Провайдер/метод (под фейковый шлюз тоже)
            // examples: fake_gateway, card, cash, bank_transfer
            $table->string('provider', 50)->default('fake_gateway')->index();

            // Статус транзакции
            // allowed: pending, paid, failed, canceled, refunded
            $table->string('status', 20)->default('pending')->index();

            // Суммы
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('RUB'); // можешь сменить на нужную

            // Идентификаторы “шлюза” (для фейка тоже полезно)
            $table->string('payment_reference', 64)->unique(); // наш уникальный референс
            $table->string('external_id', 128)->nullable()->index(); // id транзакции у "провайдера"

            // Время фактической оплаты/возврата
            $table->dateTime('paid_at')->nullable()->index();
            $table->dateTime('refunded_at')->nullable();

            // Диагностика/ответ шлюза (MySQL 5.7 JSON поддерживает)
            $table->json('provider_payload')->nullable();
            $table->text('fail_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['rental_id', 'status']);
            $table->index(['provider', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
