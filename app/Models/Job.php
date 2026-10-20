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