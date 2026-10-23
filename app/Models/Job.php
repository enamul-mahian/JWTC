<?php

namespace App\Models;

use App\Models\Dashboard\Company;
use App\Models\Dashboard\DegreeName;
use App\Models\Dashboard\DegreeLavel;
use App\Models\Dashboard\JobCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];


    public function publicPosterEmail(): ?string
    {
        if (!app(\App\Services\PosterEmailVisibility::class)->enabled()) return null;
        $email = $this->resume_receiving_email ?: $this->company?->contact_per_email;
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    private function hiddenPosterEmails(): array
    {
        return app(\App\Services\PosterEmailVisibility::class)->enabled() ? [] : [$this->resume_receiving_email, $this->company?->contact_per_email];
    }

    public function publicDescription(?string $value): string
    {
        return app(\App\Services\JobContent::class)->text($value, $this->hiddenPosterEmails());
    }

    public function publicDescriptionHtml(?string $value): string
    {
        return app(\App\Services\JobContent::class)->render($value, $this->hiddenPosterEmails());
    }

    public function toArray()
    {
        $values = parent::toArray();
        if (!app(\App\Services\PosterEmailVisibility::class)->enabled()) {
            unset($values['resume_receiving_email']);
            foreach (['job_context', 'job_responsibilities', 'additional_requirements', 'other_benefits', 'apply_procedure', 'additional_salary_info'] as $field) {
                if (isset($values[$field])) $values[$field] = $this->publicDescriptionHtml($values[$field]);
            }
        }
        return $values;
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function job_category()
    {
        return $this->belongsTo(JobCategory::class);
    }

    public function degree_level()
    {
        return $this->belongsTo(DegreeLavel::class);
    }

    public function degree_name()
    {
        return $this->belongsTo(DegreeName::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    // public function location()
    // {
    //     return $this->belongsTo(Location::class, 'job_location_id', 'id');
    // }
  

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}