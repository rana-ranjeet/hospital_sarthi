<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        DB::table('roles')->insert([
            ['name' => 'Administrator', 'slug' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Guide', 'slug' => 'guide', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Patient', 'slug' => 'patient', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('role')->constrained('roles')->nullOnDelete();
            $table->timestamp('blocked_at')->nullable()->index();
        });

        foreach (['admin', 'guide', 'patient'] as $role) {
            DB::table('users')->where('role', $role)->update([
                'role_id' => DB::table('roles')->where('slug', $role)->value('id'),
            ]);
        }

        Schema::table('guide_profiles', function (Blueprint $table) {
            $table->string('status')->default('pending')->index();
            $table->string('document_path')->nullable();
        });
        DB::table('guide_profiles')->where('is_verified', true)->update(['status' => 'verified']);

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('booking_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->timestamps();
            $table->unique(['booking_id', 'service_id']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('INR');
            $table->string('status')->default('pending')->index();
            $table->string('provider')->nullable();
            $table->string('transaction_reference')->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['booking_id', 'created_at']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();
            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
        });

        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->decimal('percentage_rate', 5, 2)->default(0);
            $table->decimal('fixed_amount', 10, 2)->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('effective_at')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->boolean('is_visible')->default(true)->index();
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn('is_visible');
        });
        Schema::dropIfExists('settings');
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('booking_services');
        Schema::dropIfExists('services');
        Schema::table('guide_profiles', function (Blueprint $table) {
            $table->dropColumn(['status', 'document_path']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn('blocked_at');
        });
        Schema::dropIfExists('roles');
    }
};
