<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class FB_Health_Category extends Model
{
    protected $table = 'Health_Category_Enum';
    public $timestamps = false;

    
    public function tags()
    {
        return $this->hasMany('\App\Models\Health_Tags_Enum','category_id','id');
    }
    
}
