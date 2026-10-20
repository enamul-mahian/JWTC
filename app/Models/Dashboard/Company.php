<?php

namespace App\Models\Dashboard;

use App\Models\CompanySize;
use App\Models\Country;
use App\Models\IndustryType;
use App\Models\Job;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function company_sizes()
    {
        return $this->belongsTo(CompanySize::class);
    }

    public function industryType()
    {
        return $this->belongsTo(IndustryType::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
