<?php

namespace App\Models;

use App\Models\Dashboard\DegreeLavel;
use App\Models\Dashboard\DegreeName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    use HasFactory;

    protected $table = 'education';

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function degreeLevel()
    {
        return $this->belongsTo(DegreeLavel::class);
    }

    // Define the relationship with DegreeName model
    public function degreeName()
    {
        return $this->belongsTo(DegreeName::class);
    }
}