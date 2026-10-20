<?php

namespace App\Models\Dashboard;

use App\Models\Education;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DegreeLavel extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function degreeName()
    {
        return $this->hasMany(DegreeName::class);
    }

    public function educations()
    {
        return $this->hasMany(Education::class);
    }
}