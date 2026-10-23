<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Dashboard\JobCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravelcm\Subscriptions\Traits\HasPlanSubscriptions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, HasPlanSubscriptions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'user_type',
        'status',
        'job_category_id',
        'photo',
        'password',
        'provider',
        'provider_id',
        'provider_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'employer_approved_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function uniqueCodes()
    {
        return $this->hasMany(UniqueCode::class);
    }

    public function personal_info()
    {
        return $this->hasOne(PersonalInformation::class);
    }

    public function cover_letter()
    {
        return $this->hasOne(CoverLetter::class);
    }

    public function work_experiences()
    {
        return $this->hasMany(WorkExperience::class);
    }

    public function trainings()
    {
        return $this->hasMany(Training::class);
    }

    public function skills()
    {
        return $this->hasMany(JobseekerSkill::class);
    }

    public function languages()
    {
        return $this->hasMany(Language::class);
    }

    public function educations()
    {
        return $this->hasMany(Education::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function cv_downloads()
    {
        return $this->hasMany(CvDownload::class);
    }

    /** Canonical account type for historic mixed-case records; stored values stay intact. */
    public function getUserTypeAttribute($value): string
    {
        return strtolower(trim((string)$value));
    }
}
