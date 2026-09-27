<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('patient_name', 120)->nullable();
            $table->string('mobile', 10)->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('payment_status')->default('pending')->index();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['patient_name', 'mobile', 'amount', 'payment_status']);
        });
    }
};