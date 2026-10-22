<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployerInformation extends Model
{
    use HasFactory;

    protected $table = 'employer_information';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
