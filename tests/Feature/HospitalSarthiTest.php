<?php

namespace Tests\Feature;

use App\Models\GuideAvailability;
use App\Models\GuideProfile;
use App\Models\Hospital;
use App\Models\Booking;
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
            ->assertSee('Add hospital')
            ->assertSee('Review guide profiles');
    }

    public function test_admin_can_manage_guide_profile_and_non_admins_cannot(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $guideUser = User::factory()->create(['role' => 'guide']);
        $guide = GuideProfile::create(['user_id' => $guideUser->id]);
        $hospital = Hospital::create(['name' => 'Northside Hospital', 'city' => 'Pune', 'address' => 'North Road']);

        $this->actingAs($admin)->put(route('admin.guides.update', $guide), [
            'name' => 'Updated Guide',
            'email' => $guideUser->email,
            'phone' => '9000000001',
            'bio' => 'Supports visitors through appointments.',
            'languages' => 'Hindi, English',
            'years_experience' => 8,
            'specialization' => 'Patient navigation',
            'hourly_rate' => 450,
            'status' => 'verified',
            'is_available' => '1',
            'hospitals' => [$hospital->id],
            'availability' => [
                ['weekday' => 1, 'start_time' => '09:00', 'end_time' => '13:00'],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $guideUser->id, 'name' => 'Updated Guide', 'phone' => '9000000001']);
        $this->assertDatabaseHas('guide_profiles', [
            'id' => $guide->id,
            'specialization' => 'Patient navigation',
            'years_experience' => 8,
            'hourly_rate' => 450,
            'status' => 'verified',
            'is_verified' => true,
            'is_available' => true,
        ]);
        $this->assertDatabaseHas('hospital_guide', ['guide_profile_id' => $guide->id, 'hospital_id' => $hospital->id]);
        $this->assertDatabaseHas('guide_availabilities', [
            'guide_profile_id' => $guide->id,
            'weekday' => 1,
            'start_time' => '09:00',
            'end_time' => '13:00',
        ]);

        $this->actingAs($guideUser)->put(route('admin.guides.update', $guide), [
            'name' => 'Attempted Change',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['id' => $guideUser->id, 'name' => 'Attempted Change']);
    }

    public function test_admin_can_create_patient_and_guide_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New Patient',
            'email' => 'patient@example.test',
            'phone' => '9000000002',
            'password' => 'safe-password-123',
            'password_confirmation' => 'safe-password-123',
        ])->assertRedirect();

        $this->post(route('admin.guides.store'), [
            'name' => 'New Guide',
            'email' => 'guide@example.test',
            'phone' => '9000000003',
            'password' => 'safe-password-123',
            'password_confirmation' => 'safe-password-123',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'patient@example.test', 'role' => 'patient']);
        $this->assertDatabaseHas('users', ['email' => 'guide@example.test', 'role' => 'guide']);
        $this->assertDatabaseHas('guide_profiles', [
            'user_id' => User::where('email', 'guide@example.test')->value('id'),
            'status' => 'pending',
            'is_verified' => false,
            'is_available' => false,
        ]);
    }

    public function test_admin_cannot_delete_users_with_bookings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = User::factory()->create(['role' => 'patient']);
        $guideUser = User::factory()->create(['role' => 'guide']);
        $guide = GuideProfile::create(['user_id' => $guideUser->id]);
        $hospital = Hospital::create(['name' => 'Northside Hospital', 'city' => 'Pune', 'address' => 'North Road']);
        $booking = Booking::create([
            'patient_id' => $patient->id,
            'guide_profile_id' => $guide->id,
            'hospital_id' => $hospital->id,
            'service' => 'OPD registration',
            'visit_date' => Carbon::tomorrow()->toDateString(),
            'start_time' => '10:00',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->delete(route('admin.users.delete', $patient))->assertStatus(422);
        $this->delete(route('admin.users.delete', $guideUser))->assertStatus(422);

        $this->assertDatabaseHas('users', ['id' => $patient->id]);
        $this->assertDatabaseHas('users', ['id' => $guideUser->id]);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
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
