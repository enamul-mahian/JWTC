<?php

namespace App\Models\Dashboard;

use App\Models\Job;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function jobs()
    {
        return $this->hasMany(Job::class)->whereNull('deleted_at')->where('application_deadline', '>', now());
    }
}
