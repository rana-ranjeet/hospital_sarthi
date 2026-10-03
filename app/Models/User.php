<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->role && ($user->isDirty('role') || ! $user->role_id)) {
                $user->role_id = Role::query()->where('slug', $user->role)->value('id');
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'mobile_country_code',
        'mobile_number',
        'alternate_country_code',
        'alternate_mobile_number',
        'age',
        'gender',
        'blood_group',
        'relationship_with_patient',
        'other_relationship',
        'google_id',
        'avatar_url',
        'role',
        'role_id',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'phone',
        'mobile_country_code',
        'mobile_number',
        'alternate_country_code',
        'alternate_mobile_number',
        'age',
        'gender',
        'blood_group',
        'relationship_with_patient',
        'other_relationship',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'blocked_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function guideProfile()
    {
        return $this->hasOne(GuideProfile::class);
    }

    public function roleRecord()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function patientBookings()
    {
        return $this->hasMany(Booking::class, 'patient_id');
    }

    public function rewardTransactions()
    {
        return $this->hasMany(CustomerRewardTransaction::class);
    }
}
