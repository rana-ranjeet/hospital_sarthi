<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\GuideAvailability;
use App\Models\GuideProfile;
use App\Models\Hospital;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    private const SERVICES = [
        'OPD registration',
        'Department navigation',
        'Token and queue guidance',
        'Diagnostic centre navigation',
        'Billing and pharmacy',
        'Report collection',
        'Discharge process',
        'General hospital navigation',
    ];

    private const PATIENT_NAMES = [
        'Aarav Sharma', 'Anaya Patel', 'Vivaan Singh', 'Diya Gupta', 'Aditya Kumar',
        'Isha Mehta', 'Arjun Verma', 'Saanvi Joshi', 'Reyansh Shah', 'Aadhya Das',
        'Krishna Nair', 'Myra Kapoor', 'Ishaan Rao', 'Pari Malhotra', 'Atharv Jain',
        'Kiara Reddy', 'Kabir Sinha', 'Aanya Iyer', 'Rudra Bansal', 'Zoya Khan',
    ];

    private const GUIDE_NAMES = [
        'Asha Menon', 'Rohan Mehta', 'Kavita Rao', 'Imran Khan', 'Neha Kapoor',
        'Sanjay Patel', 'Pooja Nair', 'Arvind Das', 'Meera Joshi', 'Farhan Ali',
    ];

    private const REVIEW_COMMENTS = [
        'The guide made the registration process much easier for our family.',
        'Clear directions and a kind presence throughout the visit.',
        'Helped us find the right department without feeling rushed.',
        'Patient and organized support while we managed a busy appointment.',
        'Made the hospital feel less confusing on a stressful day.',
        'Helpful with queues and finding the right counters.',
        'A reassuring companion who kept us on track.',
        'Friendly, punctual, and very familiar with the hospital layout.',
        'The visit went smoothly thanks to the practical guidance.',
        'Thoughtful support from arrival through report collection.',
    ];

    public function run(): void
    {
        $hospitals = $this->seedHospitals();
        $this->seedAdmin();
        $services = $this->seedServices();
        $guides = $this->seedGuides($hospitals);
        $patients = $this->seedPatients();
        $this->seedBookingsAndReviews($patients, $guides, $hospitals, $services);
    }

    private function seedHospitals(): array
    {
        $records = [
            ['name' => 'Sunrise Multispeciality Hospital', 'city' => 'Delhi', 'type' => 'Multispeciality', 'address' => '12 Ring Road, South Delhi', 'phone' => '011-4000-1001'],
            ['name' => 'Green Valley Medical Centre', 'city' => 'Gurugram', 'type' => 'Medical Centre', 'address' => '24 Golf Course Road, Gurugram', 'phone' => '0124-400-1002'],
            ['name' => 'Riverside Community Hospital', 'city' => 'Noida', 'type' => 'Community Hospital', 'address' => '8 Sector 18, Noida', 'phone' => '0120-400-1003'],
            ['name' => 'CityCare General Hospital', 'city' => 'Jaipur', 'type' => 'General Hospital', 'address' => '31 Civil Lines, Jaipur', 'phone' => '0141-400-1004'],
            ['name' => 'Lotus Park Hospital', 'city' => 'Lucknow', 'type' => 'Multispeciality', 'address' => '16 Gomti Nagar, Lucknow', 'phone' => '0522-400-1005'],
        ];

        return array_map(
            fn (array $record) => Hospital::updateOrCreate(
                ['name' => $record['name']],
                $record + ['is_active' => true]
            ),
            $records
        );
    }

    private function seedAdmin(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL') ?: 'admin@hospital-sarthi.test'],
            [
                'name' => env('ADMIN_NAME') ?: 'Platform Admin',
                'phone' => '9000000000',
                'role' => 'admin',
                'password' => env('ADMIN_PASSWORD') ?: 'password',
            ]
        );
    }

    private function seedServices(): array
    {
        return array_map(
            fn (string $name) => Service::updateOrCreate(['name' => $name], ['is_active' => true]),
            self::SERVICES
        );
    }

    private function seedGuides(array $hospitals): array
    {
        $guides = [];

        foreach (self::GUIDE_NAMES as $index => $name) {
            $number = $index + 1;
            $user = User::updateOrCreate(
                ['email' => sprintf('guide%02d@example.test', $number)],
                [
                    'name' => $name,
                    'phone' => sprintf('910000%04d', $number),
                    'role' => 'guide',
                    'password' => 'password',
                ]
            );

            $profile = GuideProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'bio' => 'Experienced hospital companion offering practical, non-medical visit support.',
                    'languages' => ['English', 'Hindi'],
                    'years_experience' => 2 + ($number % 8),
                    'hourly_rate' => 250 + ($number * 25),
                    'is_verified' => true,
                    'is_available' => true,
                ]
            );

            $primaryHospital = $hospitals[$index % count($hospitals)];
            $secondaryHospital = $hospitals[($index + 1) % count($hospitals)];
            $profile->hospitals()->syncWithoutDetaching([$primaryHospital->id, $secondaryHospital->id]);

            for ($weekday = 1; $weekday <= 5; $weekday++) {
                GuideAvailability::updateOrCreate(
                    ['guide_profile_id' => $profile->id, 'weekday' => $weekday],
                    ['start_time' => '09:00', 'end_time' => '17:00']
                );
            }

            $guides[] = $profile;
        }

        return $guides;
    }

    private function seedPatients(): array
    {
        $patients = [];

        foreach (self::PATIENT_NAMES as $index => $name) {
            $number = $index + 1;
            $patients[] = User::updateOrCreate(
                ['email' => sprintf('patient%02d@example.test', $number)],
                [
                    'name' => $name,
                    'phone' => sprintf('920000%04d', $number),
                    'role' => 'patient',
                    'password' => 'password',
                ]
            );
        }

        return $patients;
    }

    private function seedBookingsAndReviews(array $patients, array $guides, array $hospitals, array $services): void
    {
        $laterStatuses = ['pending', 'accepted', 'cancelled', 'rejected', 'pending', 'accepted', 'cancelled', 'pending', 'accepted', 'rejected'];
        $ratings = [5, 5, 4, 5, 4, 5, 5, 4, 5, 5];

        foreach ($patients as $index => $patient) {
            $number = $index + 1;
            $isReviewed = $index < count(self::REVIEW_COMMENTS);
            $visitDate = $isReviewed
                ? now()->subDays(11 - $index)->toDateString()
                : now()->addDays($index - 9)->toDateString();
            $guide = $guides[$index % count($guides)];

            $service = $services[$index % count($services)];
            $booking = Booking::updateOrCreate(
                ['patient_id' => $patient->id, 'message' => sprintf('Demo booking %02d', $number)],
                [
                    'guide_profile_id' => $guide->id,
                    'hospital_id' => $hospitals[$index % count($hospitals)]->id,
                    'service' => $service->name,
                    'visit_date' => $visitDate,
                    'start_time' => sprintf('%02d:00', 9 + ($index % 8)),
                    'status' => $isReviewed ? 'completed' : $laterStatuses[$index - count(self::REVIEW_COMMENTS)],
                ]
            );
            $booking->services()->sync([$service->id]);

            if ($isReviewed) {
                Review::updateOrCreate(
                    ['booking_id' => $booking->id],
                    ['rating' => $ratings[$index], 'comment' => self::REVIEW_COMMENTS[$index]]
                );
            }
        }
    }
}