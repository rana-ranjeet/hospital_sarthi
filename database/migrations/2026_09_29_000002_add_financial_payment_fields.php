<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('gateway_order_id')->nullable()->unique();
            $table->string('gateway_payment_id')->nullable()->unique();
            $table->string('idempotency_key')->nullable()->unique();
            $table->unsignedBigInteger('gateway_fee_minor')->default(0);
            $table->unsignedBigInteger('commission_minor')->default(0);
            $table->unsignedBigInteger('guide_earning_minor')->default(0);
            $table->unsignedBigInteger('reward_minor')->default(0);
            $table->unsignedBigInteger('refund_minor')->default(0);
            $table->timestamp('verified_at')->nullable();
            $table->json('gateway_metadata')->nullable();
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->decimal('reward_percentage_rate', 5, 2)->default(0);
            $table->decimal('gateway_fee_percentage', 5, 2)->default(0);
            $table->decimal('gateway_fee_fixed_amount', 10, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropColumn(['reward_percentage_rate', 'gateway_fee_percentage', 'gateway_fee_fixed_amount']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['gateway_order_id']);
            $table->dropUnique(['gateway_payment_id']);
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn([
                'gateway_order_id', 'gateway_payment_id', 'idempotency_key', 'gateway_fee_minor',
                'commission_minor', 'guide_earning_minor', 'reward_minor', 'refund_minor',
                'verified_at', 'gateway_metadata',
            ]);
        });
    }
};