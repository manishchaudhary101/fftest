<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class HealthEntry extends Model
{
    
    protected $table = 'HealthEntry';
    public $timestamps = false;


    protected $casts = [
        'start_time_utc' => 'datetime:'.DEFAULT_DATE_INPUT_FORMAT,
        
    ];
}
