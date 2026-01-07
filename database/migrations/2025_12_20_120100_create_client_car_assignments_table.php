<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_car_assignments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('car_id')->constrained('cars')->cascadeOnDelete();
            $table->string('relation_type', 20)->default('client')->index();
            $table->timestamps();

            $table->unique(['client_id', 'car_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_car_assignments');
    }
};
