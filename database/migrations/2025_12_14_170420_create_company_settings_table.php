<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();

            $table->string('company_name', 150)->default('Компания');
            $table->string('legal_name', 200)->nullable();

            $table->string('inn', 20)->nullable();
            $table->string('kpp', 20)->nullable();
            $table->string('ogrn', 30)->nullable();

            $table->string('address', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 120)->nullable();

            $table->string('director_name', 150)->nullable();
            $table->string('director_position', 100)->nullable();

            $table->string('bank_name', 150)->nullable();
            $table->string('bik', 30)->nullable();
            $table->string('account', 40)->nullable();
            $table->string('corr_account', 40)->nullable();

            // префикс/формат номера договора (на будущее)
            $table->string('contract_prefix', 30)->default('CR');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
