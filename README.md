# Hospital Sarthi

A Laravel 10 patient-companion booking platform for practical, non-medical help inside a hospital. Guides can assist with navigation, registration, queues, test-centre locations, billing, pharmacy, reports and discharge steps. They do not diagnose, treat or provide medical advice.

## Local setup (XAMPP)

Requirements: PHP 8.1+, Composer, and MySQL. From this project directory:

1. Install PHP dependencies with `composer install` (Laravel 10 has current Composer security advisories; see the runtime note below).
2. Copy `.env.example` to `.env` if needed, then set `DB_DATABASE=hospital_sarthi` and your local MySQL credentials.
3. Create the database in phpMyAdmin or run `CREATE DATABASE hospital_sarthi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;` in MySQL.
4. Generate the app key with `php artisan key:generate`.
5. To create the initial admin, set `ADMIN_NAME`, `ADMIN_EMAIL`, and a strong `ADMIN_PASSWORD` in `.env`. Leave the password blank if you do not want to create an admin yet.
6. Run `php artisan migrate --seed`.
7. Start the app with `php artisan serve` and open the URL printed by Artisan.

The page stylesheet and JavaScript are served from `public/`; no Node or asset build step is needed. Bootstrap, Bootstrap Icons, fonts and editorial photography load from their CDNs.

**Runtime note:** This starter is pinned to Laravel 10 to run on XAMPP's PHP 8.1. Laravel 10 is out of security support, and Composer reports framework advisories. Upgrade the runtime to PHP 8.2+ and the framework to a patched Laravel 12 release before production use.

## Roles and workflows

- Patients can register, search active hospitals, compare approved guides and request a time slot. They can cancel pending requests.
- Guides can register, describe their experience, select hospitals, publish weekly hours, set an hourly rate and accept or decline requests. New profiles stay hidden until an admin verifies them.
- Admin accounts are created only through the environment-configured seeder. Admins can add or hide hospitals and approve or unverify guides.
- Booking requests are checked against the guide's verification, hospital coverage, weekly availability and existing pending/accepted requests.

## API

- `GET /api/hospitals?q={name-or-city}`: search active hospital listings.
- `GET /api/guides?hospital_id={id}`: list verified guides who are available at a hospital.
- `POST /api/bookings`: create a booking with a Sanctum-authenticated request. Required fields: `hospital_id`, `guide_profile_id`, `service`, `visit_date`, and `start_time`; `message` is optional.

## Tests

Run `php artisan test`. Feature tests use an isolated in-memory SQLite database.
