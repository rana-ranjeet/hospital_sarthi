<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guide_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guide_profile_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
            $table->unique(['guide_profile_id', 'weekday']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guide_availabilities');
    }
};