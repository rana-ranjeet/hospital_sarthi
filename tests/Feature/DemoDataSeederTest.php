<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\GuideProfile;
use App\Models\Hospital;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_repeatable_sample_data(): void
    {
        $this->seed(DemoDataSeeder::class);
        $this->seed(DemoDataSeeder::class);

        $this->assertSame(1, User::where('role', 'admin')->count());
        $this->assertSame(10, User::where('role', 'guide')->count());
        $this->assertSame(0, GuideProfile::whereNull('city')->count());
        $this->assertSame(20, User::where('role', 'patient')->count());
        $this->assertSame(5, Hospital::count());
        $this->assertSame(20, Booking::count());
        $this->assertSame(8, Booking::query()->distinct()->count('service'));
        $this->assertSame(10, Review::count());
    }
}