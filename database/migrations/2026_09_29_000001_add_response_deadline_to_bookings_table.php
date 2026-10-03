<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('response_deadline')->nullable()->index();
        });

        DB::table('bookings')
            ->where('status', 'pending')
            ->whereNull('response_deadline')
            ->orderBy('id')
            ->get(['id', 'created_at'])
            ->each(function ($booking) {
                DB::table('bookings')->where('id', $booking->id)->update([
                    'response_deadline' => Carbon::parse($booking->created_at)->addMinutes(2),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('response_deadline');
        });
    }
};