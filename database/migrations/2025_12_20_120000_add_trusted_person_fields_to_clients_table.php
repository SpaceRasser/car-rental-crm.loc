<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('trusted_person_name', 120)->nullable()->after('notes');
            $table->string('trusted_person_phone', 30)->nullable()->after('trusted_person_name');
            $table->string('trusted_person_license_number', 50)->nullable()->after('trusted_person_phone');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'trusted_person_name',
                'trusted_person_phone',
                'trusted_person_license_number',
            ]);
        });
    }
};
