<?php

namespace Tests\Feature;

use App\Models\GuideAvailability;
use App\Models\GuideProfile;
use App\Models\Hospital;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HospitalSarthiTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_the_patient_companion_experience(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('A familiar face')
            ->assertSee('Tokens & queues', false)
            ->assertSee('Not medical care or advice.', false);
    }

    public function test_hospital_api_only_returns_matching_active_hospitals(): void
    {
        Hospital::create(['name' => 'Green Valley Hospital', 'city' => 'Pune', 'address' => 'North Road']);
        Hospital::create(['name' => 'City Medical Centre', 'city' => 'Mumbai', 'address' => 'Central Road']);
        Hospital::create(['name' => 'Hidden Hospital', 'city' => 'Pune', 'address' => 'West Road', 'is_active' => false]);

        $this->getJson('/api/hospitals?q=Pune')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'Green Valley Hospital');
    }

    public function test_guide_registration_creates_an_unverified_profile(): void
    {
        $this->post('/register', [
            'name' => 'Asha Guide',
            'email' => 'asha@example.test',
            'phone' => '9000000000',
            'role' => 'guide',
            'password' => 'safe-password-123',
            'password_confirmation' => 'safe-password-123',
        ])->assertRedirect(route('dashboard'));

        $user = User::where('email', 'asha@example.test')->firstOrFail();
        $this->assertSame('guide', $user->role);
        $this->assertFalse($user->guideProfile->is_verified);
    }

    public function test_registration_cannot_assign_the_admin_role(): void
    {
        $this->from('/register')->post('/register', [
            'name' => 'Untrusted User',
            'email' => 'untrusted@example.test',
            'role' => 'admin',
            'password' => 'safe-password-123',
            'password_confirmation' => 'safe-password-123',
        ])->assertRedirect('/register')->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'untrusted@example.test']);
    }

    public function test_patient_dashboard_renders_without_bookings(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);

        $this->actingAs($patient)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Booking requests')
            ->assertSee('No visits booked yet.');
    }

    public function test_guide_dashboard_renders_profile_and_availability_controls(): void
    {
        $guide = User::factory()->create(['role' => 'guide']);
        GuideProfile::create(['user_id' => $guide->id]);

        $this->actingAs($guide)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Your profile & availability', false)
            ->assertSee('Weekly availability');
    }

    public function test_admin_dashboard_renders_management_sections(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Add a hospital')
            ->assertSee('Review guide profiles');
    }

    public function test_only_patients_can_submit_a_booking_request(): void
    {
        $guide = User::factory()->create(['role' => 'guide']);

        $this->actingAs($guide)
            ->post('/bookings', [])
            ->assertForbidden();
    }

    public function test_guides_cannot_use_the_sanctum_booking_endpoint(): void
    {
        $guide = User::factory()->create(['role' => 'guide']);

        $this->actingAs($guide, 'sanctum')
            ->postJson('/api/bookings', [])
            ->assertForbidden();
    }

    public function test_patient_can_request_a_verified_guide_during_listed_availability(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $guideUser = User::factory()->create(['role' => 'guide']);
        $hospital = Hospital::create(['name' => 'Northside Hospital', 'city' => 'Pune', 'address' => 'North Road']);
        $guide = GuideProfile::create(['user_id' => $guideUser->id, 'is_verified' => true, 'is_available' => true]);
        $guide->hospitals()->attach($hospital);
        $visitDate = Carbon::today()->next(Carbon::MONDAY);
        GuideAvailability::create([
            'guide_profile_id' => $guide->id,
            'weekday' => $visitDate->dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '12:00',
        ]);

        $this->actingAs($patient)->post('/bookings', [
            'hospital_id' => $hospital->id,
            'guide_profile_id' => $guide->id,
            'service' => 'OPD registration',
            'visit_date' => $visitDate->toDateString(),
            'start_time' => '10:00',
            'message' => 'Please meet us at the main entrance.',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('bookings', [
            'patient_id' => $patient->id,
            'guide_profile_id' => $guide->id,
            'status' => 'pending',
        ]);
    }
}
