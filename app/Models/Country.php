<?php

namespace App\Models;

use App\Enum\StatusType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    // protected $fillable = [
    //     'name', 'code'
    // ];

    protected $guarded = [];

    public function jobs()
    {
        return $this->hasMany(Job::class)
            ->where('status', StatusType::ACTIVE)
            ->where('application_deadline', '>', Carbon::now());
    }
}
