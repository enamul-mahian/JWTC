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

    public function publicEmploymentLabels(): ?string
    {
        $items = json_decode($this->employment_status ?? '[]', true);
        if (!is_array($items)) return $this->employment_status;
        $ids = array_values(array_filter($items, static fn($item) => is_scalar($item) && ctype_digit((string)$item)));
        $names = $ids ? \Illuminate\Support\Facades\DB::table('job_types')->whereIn('id',$ids)->pluck('name','id')->all() : [];
        $labels = [];
        foreach ($items as $item) {
            if (!is_scalar($item)) continue;
            if (ctype_digit((string)$item)) { if (isset($names[$item])) $labels[] = $names[$item]; }
            else $labels[] = (string)$item;
        }
        return $labels ? json_encode($labels, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) : null;
    }

    public function toArray()
    {
        $values = parent::toArray();
        if (!app(\App\Services\PosterEmailVisibility::class)->enabled()) {
            unset($values['resume_receiving_email']);
            foreach (['job_context', 'job_responsibilities', 'additional_requirements', 'other_benefits', 'apply_procedure', 'additional_salary_info', 'faqs'] as $field) {
                if (isset($values[$field])) $values[$field] = app(\App\Services\JobContent::class)->redact($values[$field], $this->hiddenPosterEmails());
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