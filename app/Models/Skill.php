<?php

namespace App\Models;

use App\Models\Dashboard\JobCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function jobCategory()
    {
        return $this->belongsTo(JobCategory::class);
    }
}