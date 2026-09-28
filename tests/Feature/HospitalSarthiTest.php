<?php

namespace Tests\Feature;

use App\Models\GuideAvailability;
use App\Models\GuideProfile;
use App\Models\Hospital;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class HospitalSarthiTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_the_patient_companion_experience(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Bhopal', false)
            ->assertSee('Lucknow', false)
            ->assertSee('A familiar face')
            ->assertSee('Tokens & queues', false)
            ->assertSee('Not medical care or advice.', false);
    }

    public function test_google_sign_in_is_offered_and_signed_in_users_see_their_profile_icon(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Continue with Google')
            ->assertSee(route('auth.google.redirect'), false);
        $this->get(route('register', ['role' => 'guide']))
            ->assertOk()
            ->assertSee('Sign up with Google')
            ->assertSee(route('auth.google.redirect', ['role' => 'guide']), false);

        $patient = User::factory()->create([
            'role' => 'patient',
            'name' => 'Google Patient',
            'avatar_url' => 'https://example.test/avatar.png',
        ]);

        $this->actingAs($patient)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('account-profile-avatar', false)
            ->assertSee('Google Patient');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('account-profile-avatar', false)
            ->assertSee('Google Patient');
    }

    public function test_google_redirect_preserves_the_selected_signup_role(): void
    {
        config([
            'services.google.client_id' => 'test-client-id',
            'services.google.client_secret' => 'test-client-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);

        $this->get(route('auth.google.redirect', ['role' => 'guide']))
            ->assertRedirectContains('accounts.google.com')
            ->assertSessionHas('google_signup_role', 'guide');
    }

    public function test_google_redirect_rejects_the_secret_placeholder(): void
    {
        config([
            'services.google.client_id' => 'test-client-id.apps.googleusercontent.com',
            'services.google.client_secret' => 'YOUR_CLIENT_SECRET',
        ]);

        $this->get(route('auth.google.redirect'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('google');
    }

    public function test_google_callback_creates_a_guide_when_guide_signup_was_selected(): void
    {
        $googleUser = SocialiteUser::fake([
            'id' => 'google-guide-123',
            'name' => 'Google Guide',
            'email' => 'google-guide@example.test',
            'avatar' => 'https://example.test/guide.png',
            'email_verified' => true,
        ]);
        $provider = \Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('user')->once()->andReturn($googleUser);
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->withSession(['google_signup_role' => 'guide'])
            ->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard'));

        $guide = User::where('email', 'google-guide@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($guide);
        $this->assertSame('guide', $guide->role);
        $this->assertDatabaseHas('users', [
            'id' => $guide->id,
            'google_id' => 'google-guide-123',
            'avatar_url' => 'https://example.test/guide.png',
        ]);
        $this->assertDatabaseHas('guide_profiles', ['user_id' => $guide->id]);
    }

    public function test_google_callback_links_a_verified_email_to_an_existing_account(): void
    {
        $patient = User::factory()->create(['role' => 'patient', 'email' => 'existing@example.test']);
        $googleUser = SocialiteUser::fake([
            'id' => 'google-existing-123',
            'email' => 'existing@example.test',
            'email_verified' => true,
        ]);
        $provider = \Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('user')->once()->andReturn($googleUser);
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback'))->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($patient);
        $this->assertDatabaseHas('users', ['id' => $patient->id, 'google_id' => 'google-existing-123']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_homepage_modal_defaults_to_an_active_database_service(): void
    {
        $service = Service::create(['name' => 'OPD registration', 'base_price' => 300, 'is_active' => true]);

        $this->get('/')
            ->assertOk()
            ->assertSee('serviceId: '.$service->id, false);
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

    public function test_hospital_api_searches_lucknow_and_bhopal_without_returning_hidden_listings(): void
    {
        Hospital::create(['name' => 'Lucknow Partner Hospital', 'city' => 'Lucknow', 'address' => 'Gomti Nagar']);
        Hospital::create(['name' => 'Bhopal Partner Hospital', 'city' => 'Bhopal', 'address' => 'Arera Hills']);
        Hospital::create(['name' => 'Hidden Bhopal Hospital', 'city' => 'Bhopal', 'address' => 'Old City', 'is_active' => false]);

        $this->getJson('/api/hospitals?q=Lucknow')->assertOk()->assertJsonCount(1)->assertJsonPath('0.city', 'Lucknow');
        $this->getJson('/api/hospitals?q=Bhopal')->assertOk()->assertJsonCount(1)->assertJsonPath('0.name', 'Bhopal Partner Hospital');
    }

    public function test_guides_api_only_returns_guides_available_at_selected_hospital_time(): void
    {
        $hospital = Hospital::create(['name' => 'Lucknow Partner Hospital', 'city' => 'Lucknow', 'address' => 'Gomti Nagar']);
        $guideUser = User::factory()->create(['role' => 'guide']);
        $guide = GuideProfile::create(['user_id' => $guideUser->id, 'city' => 'Lucknow', 'is_verified' => true, 'is_available' => true]);
        $guide->hospitals()->attach($hospital);
        $otherCityGuideUser = User::factory()->create(['role' => 'guide']);
        $otherCityGuide = GuideProfile::create(['user_id' => $otherCityGuideUser->id, 'city' => 'Bhopal', 'is_verified' => true, 'is_available' => true]);
        $otherCityGuide->hospitals()->attach($hospital);
        $visitDate = Carbon::today()->next(Carbon::MONDAY);
        GuideAvailability::create([
            'guide_profile_id' => $guide->id,
            'weekday' => $visitDate->dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '12:00',
        ]);
        GuideAvailability::create([
            'guide_profile_id' => $otherCityGuide->id,
            'weekday' => $visitDate->dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '12:00',
        ]);

        $this->getJson('/api/guides?'.http_build_query([
            'hospital_id' => $hospital->id,
            'date' => $visitDate->toDateString(),
            'time' => '10:00',
        ]))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $guide->id);

        $this->getJson('/api/guides?'.http_build_query([
            'hospital_id' => $hospital->id,
            'date' => $visitDate->toDateString(),
            'time' => '13:00',
        ]))->assertOk()->assertJsonCount(0);
    }

    public function test_guide_registration_creates_an_unverified_profile(): void
    {
        $this->post('/register', [
            'name' => 'Asha Guide',
            'email' => 'asha@example.test',
            'phone' => '9000000000',
            'role' => 'guide',
            'city' => 'Bhopal',
            'password' => 'safe-password-123',
            'password_confirmation' => 'safe-password-123',
        ])->assertRedirect(route('dashboard'));

        $user = User::where('email', 'asha@example.test')->firstOrFail();
        $this->assertSame('guide', $user->role);
        $this->assertFalse($user->guideProfile->is_verified);
        $this->assertSame('Bhopal', $user->guideProfile->city);
    }

    public function test_guide_registration_requires_a_city(): void
    {
        $this->from('/register')->post('/register', [
            'name' => 'Asha Guide',
            'email' => 'asha@example.test',
            'role' => 'guide',
            'password' => 'safe-password-123',
            'password_confirmation' => 'safe-password-123',
        ])->assertRedirect('/register')->assertSessionHasErrors('city');

        $this->assertDatabaseMissing('users', ['email' => 'asha@example.test']);
    }

    public function test_patient_registration_saves_contact_and_profile_details(): void
    {
        $this->post('/register', [
            'name' => 'Asha Patient',
            'email' => 'asha-patient@example.test',
            'role' => 'patient',
            'mobile_country_code' => '+91',
            'mobile_number' => '9876543210',
            'alternate_country_code' => '+44',
            'alternate_mobile_number' => '7700900123',
            'age' => '34',
            'gender' => 'Female',
            'blood_group' => 'O+',
            'relationship_with_patient' => 'Other',
            'other_relationship' => 'Cousin',
            'password' => 'safe-password-123',
            'password_confirmation' => 'safe-password-123',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'asha-patient@example.test',
            'mobile_country_code' => '+91',
            'mobile_number' => '9876543210',
            'alternate_country_code' => '+44',
            'alternate_mobile_number' => '7700900123',
            'age' => 34,
            'gender' => 'Female',
            'blood_group' => 'O+',
            'relationship_with_patient' => 'Other',
            'other_relationship' => 'Cousin',
        ]);
    }

    public function test_registration_validates_phone_length_for_the_selected_country(): void
    {
        $this->from('/register')->post('/register', [
            'name' => 'Asha Patient',
            'email' => 'bad-phone@example.test',
            'role' => 'patient',
            'mobile_country_code' => '+44',
            'mobile_number' => '770090012',
            'alternate_country_code' => '+91',
            'password' => 'safe-password-123',
            'password_confirmation' => 'safe-password-123',
        ])->assertRedirect('/register')->assertSessionHasErrors('mobile_number');

        $this->assertDatabaseMissing('users', ['email' => 'bad-phone@example.test']);

        $this->from('/register')->post('/register', [
            'name' => 'Asha Patient',
            'email' => 'bad-prefix@example.test',
            'role' => 'patient',
            'mobile_country_code' => '+91',
            'mobile_number' => '1234567890',
            'alternate_country_code' => '+91',
            'password' => 'safe-password-123',
            'password_confirmation' => 'safe-password-123',
        ])->assertRedirect('/register')->assertSessionHasErrors('mobile_number');

        $this->assertDatabaseMissing('users', ['email' => 'bad-prefix@example.test']);
    }

    public function test_patient_can_update_their_profile_details(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);

        $this->actingAs($patient)->patch(route('patient.profile.update'), [
            'name' => 'Updated Patient',
            'mobile_country_code' => '+91',
            'mobile_number' => '9876543210',
            'alternate_country_code' => '+91',
            'alternate_mobile_number' => '',
            'age' => '28',
            'gender' => 'Prefer not to say',
            'blood_group' => 'AB-',
            'relationship_with_patient' => 'Self',
            'other_relationship' => '',
        ])->assertRedirect()->assertSessionHas('status', 'Your patient profile was updated.');

        $this->assertDatabaseHas('users', [
            'id' => $patient->id,
            'name' => 'Updated Patient',
            'mobile_number' => '9876543210',
            'age' => 28,
            'gender' => 'Prefer not to say',
            'blood_group' => 'AB-',
            'relationship_with_patient' => 'Self',
        ]);
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
            ->assertSee('GUIDE WORKSPACE')
            ->assertSee('New bookings')
            ->assertSee('Availability')
            ->assertSee('Weekly availability')
            ->assertSee('guide-dashboard.css');
    }

    public function test_verified_guide_can_toggle_availability(): void
    {
        $guide = User::factory()->create(['role' => 'guide']);
        $profile = GuideProfile::create(['user_id' => $guide->id, 'is_verified' => true, 'is_available' => false]);

        $this->actingAs($guide)
            ->patch(route('guide.availability.update'), ['is_available' => '1'])
            ->assertRedirect();

        $this->assertDatabaseHas('guide_profiles', ['id' => $profile->id, 'is_available' => true]);
    }

    public function test_unverified_guide_cannot_toggle_availability_online(): void
    {
        $guide = User::factory()->create(['role' => 'guide']);
        $profile = GuideProfile::create(['user_id' => $guide->id, 'is_verified' => false, 'is_available' => false]);

        $this->actingAs($guide)
            ->patch(route('guide.availability.update'), ['is_available' => '1'])
            ->assertForbidden();
        $this->assertDatabaseHas('guide_profiles', ['id' => $profile->id, 'is_available' => false]);
    }

    public function test_admin_dashboard_renders_management_sections(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Admin workspace')
            ->assertSee(route('admin.manage', 'users'), false)
            ->assertSee(route('admin.manage', 'services'), false)
            ->assertDontSee('Add hospital');
    }

    public function test_admin_management_pages_are_restricted_to_admins(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = User::factory()->create(['role' => 'patient']);
        $guideUser = User::factory()->create(['role' => 'guide']);
        GuideProfile::create(['user_id' => $guideUser->id]);
        Hospital::create(['name' => 'Northside Hospital', 'city' => 'Pune', 'address' => 'North Road']);
        Service::create(['name' => 'OPD assistance', 'base_price' => 300, 'is_active' => true]);

        $this->actingAs($admin)
            ->get(route('admin.manage', 'services'))
            ->assertOk()
            ->assertSee('Booking services')
            ->assertSee('Add service');

        foreach (['users', 'guides', 'hospitals', 'services', 'bookings', 'payments', 'reviews', 'commission'] as $section) {
            $this->get(route('admin.manage', $section))->assertOk();
        }

        $this->get(route('admin.manage', 'guides'))->assertSee('Edit guide');
        $this->get(route('admin.manage', 'hospitals'))->assertSee('Northside Hospital');
        $this->get(route('admin.manage', 'services'))->assertSee('OPD assistance');

        $this->actingAs($patient)
            ->get(route('admin.manage', 'services'))
            ->assertForbidden();
    }

    public function test_admin_can_filter_hospitals_and_bookings_by_city(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $patient = User::factory()->create(['role' => 'patient']);
        $guideUser = User::factory()->create(['role' => 'guide']);
        $guide = GuideProfile::create(['user_id' => $guideUser->id]);
        $lucknow = Hospital::create(['name' => 'Lucknow General Hospital', 'city' => 'Lucknow', 'address' => 'Gomti Nagar']);
        $bhopal = Hospital::create(['name' => 'Bhopal General Hospital', 'city' => 'Bhopal', 'address' => 'Arera Hills']);
        foreach ([$lucknow, $bhopal] as $hospital) {
            Booking::create([
                'patient_id' => $patient->id,
                'guide_profile_id' => $guide->id,
                'hospital_id' => $hospital->id,
                'service' => 'OPD registration',
                'visit_date' => Carbon::tomorrow()->toDateString(),
                'start_time' => '10:00',
                'status' => 'pending',
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.manage', ['section' => 'hospitals', 'city' => 'Lucknow']))
            ->assertOk()
            ->assertSee('Lucknow General Hospital')
            ->assertDontSee('Bhopal General Hospital');

        $this->get(route('admin.manage', ['section' => 'bookings', 'city' => 'Bhopal']))
            ->assertOk()
            ->assertSee('Bhopal General Hospital')
            ->assertDontSee('Lucknow General Hospital');
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
            'city' => 'Pune',
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
            'city' => 'Pune',
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
            'city' => 'Lucknow',
            'password' => 'safe-password-123',
            'password_confirmation' => 'safe-password-123',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'patient@example.test', 'role' => 'patient']);
        $this->assertDatabaseHas('users', ['email' => 'guide@example.test', 'role' => 'guide']);
        $this->assertDatabaseHas('guide_profiles', [
            'user_id' => User::where('email', 'guide@example.test')->value('id'),
            'city' => 'Lucknow',
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

    public function test_guide_can_mark_an_accepted_booking_received_for_the_patient(): void
    {
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
            'status' => 'accepted',
        ]);

        $this->actingAs($guideUser)
            ->patch(route('guide.bookings.respond', $booking), ['status' => 'received'])
            ->assertRedirect();

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'received']);

        $this->actingAs($patient)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Received');
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
        Service::create(['name' => 'OPD registration', 'base_price' => 300, 'is_active' => true]);
        $visitDate = Carbon::today()->next(Carbon::MONDAY);
        GuideAvailability::create([
            'guide_profile_id' => $guide->id,
            'weekday' => $visitDate->dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '12:00',
        ]);

        $this->actingAs($patient)
            ->get(route('hospitals.guides', $hospital))
            ->assertOk()
            ->assertSee('name="mobile_number"', false)
            ->assertSee('name="alternate_mobile_number"', false)
            ->assertSee('Relationship with Patient');

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

    public function test_modal_booking_stores_database_service_price_and_pending_payment(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $guideUser = User::factory()->create(['role' => 'guide']);
        $hospital = Hospital::create(['name' => 'Northside Hospital', 'city' => 'Pune', 'address' => 'North Road']);
        $guide = GuideProfile::create(['user_id' => $guideUser->id, 'is_verified' => true, 'is_available' => true]);
        $guide->hospitals()->attach($hospital);
        $service = Service::create(['name' => 'OPD registration', 'base_price' => 425, 'is_active' => true]);
        $visitDate = Carbon::today()->next(Carbon::MONDAY);
        GuideAvailability::create([
            'guide_profile_id' => $guide->id,
            'weekday' => $visitDate->dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '12:00',
        ]);

        $response = $this->actingAs($patient)->postJson(route('bookings.store'), [
            'hospital_id' => $hospital->id,
            'service_id' => $service->id,
            'guide_id' => $guide->id,
            'date' => $visitDate->toDateString(),
            'time' => '10:00',
            'patient_name' => 'Asha Patient',
            'mobile_country_code' => '+91',
            'mobile_number' => '9876543210',
            'alternate_country_code' => '+44',
            'alternate_mobile_number' => '7700900123',
            'age' => 34,
            'gender' => 'Female',
            'blood_group' => 'O+',
            'relationship_with_patient' => 'Other',
            'other_relationship' => 'Cousin',
            'note' => 'Please meet at the main entrance.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Booking created successfully. Payment is pending; no payment was collected.');

        $bookingId = $response->json('booking_id');
        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'patient_id' => $patient->id,
            'patient_name' => 'Asha Patient',
            'mobile' => '9876543210',
            'mobile_country_code' => '+91',
            'mobile_number' => '9876543210',
            'alternate_country_code' => '+44',
            'alternate_mobile_number' => '7700900123',
            'age' => 34,
            'gender' => 'Female',
            'blood_group' => 'O+',
            'relationship_with_patient' => 'Other',
            'other_relationship' => 'Cousin',
            'guide_profile_id' => $guide->id,
            'hospital_id' => $hospital->id,
            'service' => $service->name,
            'message' => 'Please meet at the main entrance.',
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
        $booking = Booking::findOrFail($bookingId);
        $this->assertSame($visitDate->toDateString(), $booking->visit_date->toDateString());
        $this->assertSame('10:00', substr($booking->start_time, 0, 5));
        $this->assertSame('425.00', $booking->amount);
        $this->assertDatabaseHas('booking_services', ['booking_id' => $bookingId, 'service_id' => $service->id]);
    }

    public function test_modal_booking_rejects_invalid_mobile_and_past_visit_date(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $guideUser = User::factory()->create(['role' => 'guide']);
        $hospital = Hospital::create(['name' => 'Northside Hospital', 'city' => 'Pune', 'address' => 'North Road']);
        $guide = GuideProfile::create(['user_id' => $guideUser->id]);
        $service = Service::create(['name' => 'OPD registration', 'base_price' => 425, 'is_active' => true]);

        $this->actingAs($patient)->postJson(route('bookings.store'), [
            'hospital_id' => $hospital->id,
            'service_id' => $service->id,
            'guide_id' => $guide->id,
            'date' => Carbon::yesterday()->toDateString(),
            'time' => '10:00',
            'patient_name' => 'Asha Patient',
            'mobile' => '12345',
        ])->assertUnprocessable()->assertJsonValidationErrors(['mobile', 'visit_date']);

        $this->assertDatabaseCount('bookings', 0);
    }
}
