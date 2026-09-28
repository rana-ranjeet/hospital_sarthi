<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['users', 'bookings'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('mobile_country_code', 5)->default('+91');
                $table->string('mobile_number', 15)->nullable();
                $table->string('alternate_country_code', 5)->default('+91');
                $table->string('alternate_mobile_number', 15)->nullable();
                $table->unsignedTinyInteger('age')->nullable();
                $table->string('gender', 32)->nullable();
                $table->string('blood_group', 3)->nullable();
                $table->string('relationship_with_patient', 32)->nullable();
                $table->string('other_relationship', 120)->nullable();
            });
        }
    }

    public function down(): void
    {
        $columns = [
            'mobile_country_code',
            'mobile_number',
            'alternate_country_code',
            'alternate_mobile_number',
            'age',
            'gender',
            'blood_group',
            'relationship_with_patient',
            'other_relationship',
        ];

        foreach (['users', 'bookings'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};