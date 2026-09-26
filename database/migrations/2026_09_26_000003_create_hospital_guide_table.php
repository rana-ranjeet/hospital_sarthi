<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_guide', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guide_profile_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['hospital_id', 'guide_profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_guide');
    }
};