<?php

namespace App\Models\Dashboard;

use App\Models\Education;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DegreeName extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function degreeLavel () {
        return $this->belongsTo(DegreeLavel::class);
    }

    public function educations()
    {
        return $this->hasMany(Education::class);
    }
}