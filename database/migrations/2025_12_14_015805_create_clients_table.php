<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Если клиент будет уметь логиниться (Breeze), можно связать с users
            $table->foreignId('user_id')
                ->nullable()
                ->unique()
                ->constrained('users')
                ->nullOnDelete();

            // Кто создал карточку (менеджер/админ)
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Контакты
            $table->string('first_name', 80);
            $table->string('last_name', 80);
            $table->string('middle_name', 80)->nullable();
            $table->string('phone', 30)->nullable()->index();
            $table->string('email', 120)->nullable()->index();

            // Документы (для аренды/тест-драйва)
            $table->string('driver_license_number', 50)->nullable()->index();
            $table->date('driver_license_issued_at')->nullable();
            $table->date('driver_license_expires_at')->nullable();
            $table->date('birth_date')->nullable();

            // CRM поля
            // allowed: normal, vip, blocked
            $table->string('reliability_status', 20)->default('normal')->index();
            $table->boolean('is_verified')->default(false)->index();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
