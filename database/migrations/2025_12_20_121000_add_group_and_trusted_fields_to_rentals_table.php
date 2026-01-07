<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->uuid('group_uuid')->nullable()->index()->after('id');
            $table->boolean('is_trusted_person')->default(false)->after('notes');
            $table->string('trusted_person_name', 120)->nullable()->after('is_trusted_person');
            $table->string('trusted_person_phone', 30)->nullable()->after('trusted_person_name');
            $table->string('trusted_person_license_number', 50)->nullable()->after('trusted_person_phone');
        });
    }

    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn([
                'group_uuid',
                'is_trusted_person',
                'trusted_person_name',
                'trusted_person_phone',
                'trusted_person_license_number',
            ]);
        });
    }
};
