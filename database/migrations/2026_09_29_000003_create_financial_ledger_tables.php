<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_key')->unique();
            $table->string('account_type')->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('guide_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->char('currency', 3)->default('INR');
            $table->bigInteger('balance_minor')->default(0);
            $table->timestamps();
        });

        Schema::create('ledger_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('idempotency_key')->nullable()->unique();
            $table->string('type')->index();
            $table->string('description');
            $table->nullableMorphs('source');
            $table->json('metadata')->nullable();
            $table->timestamp('posted_at');
            $table->timestamps();
        });

        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ledger_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ledger_account_id')->constrained()->restrictOnDelete();
            $table->string('side', 6);
            $table->unsignedBigInteger('amount_minor');
            $table->char('currency', 3)->default('INR');
            $table->timestamp('created_at');
            $table->index(['ledger_account_id', 'created_at']);
        });

        Schema::create('guide_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guide_profile_id')->constrained()->cascadeOnDelete();
            $table->string('account_holder_name', 120);
            $table->text('account_number_encrypted');
            $table->text('ifsc_encrypted');
            $table->string('account_last_four', 4);
            $table->string('ifsc_masked', 16);
            $table->string('bank_name')->nullable();
            $table->string('status')->default('pending')->index();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('guide_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guide_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guide_bank_account_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('amount_minor');
            $table->unsignedBigInteger('fee_minor')->default(0);
            $table->char('currency', 3)->default('INR');
            $table->string('status')->default('requested')->index();
            $table->string('idempotency_key')->unique();
            $table->string('provider')->nullable();
            $table->string('provider_reference')->nullable()->unique();
            $table->foreignId('ledger_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('failure_reason')->nullable();
            $table->json('provider_metadata')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount_minor');
            $table->string('status')->default('requested')->index();
            $table->string('reason', 500)->nullable();
            $table->string('idempotency_key')->unique();
            $table->string('provider_reference')->nullable()->unique();
            $table->foreignId('ledger_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('provider_metadata')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_reward_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ledger_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->index();
            $table->unsignedBigInteger('amount_minor');
            $table->string('reference')->unique();
            $table->string('description');
            $table->timestamps();
        });

        Schema::create('financial_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('event_id');
            $table->boolean('signature_verified')->default(false);
            $table->longText('payload');
            $table->string('processing_status')->default('received')->index();
            $table->text('error')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'event_id']);
        });

        Schema::create('financial_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action')->index();
            $table->nullableMorphs('subject');
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('request_id')->nullable();
            $table->timestamp('created_at');
            $table->index(['created_at', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_audit_logs');
        Schema::dropIfExists('financial_webhook_events');
        Schema::dropIfExists('customer_reward_transactions');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('guide_payouts');
        Schema::dropIfExists('guide_bank_accounts');
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('ledger_transactions');
        Schema::dropIfExists('ledger_accounts');
    }
};