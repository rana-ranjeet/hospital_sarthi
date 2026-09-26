<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guide_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('bio')->nullable();
            $table->json('languages')->nullable();
            $table->unsignedSmallInteger('years_experience')->default(0);
            $table->decimal('hourly_rate', 8, 2)->default(0);
            $table->boolean('is_verified')->default(false)->index();
            $table->boolean('is_available')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guide_profiles');
    }
};