<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class FB_UserFiles extends Model
{
    protected $table = 'FB_UserFiles';

    use SoftDeletes;
    
    protected $hidden = ['deleted_at','updated_at'];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
//    protected $casts = [
//        'created_at' => 'datetime:U',
//
//    ];

public function getCreatedAtAttribute($created_at)
    {
        if(!empty($created_at))
        {
            $date = \DateTime::createFromFormat('Y-m-d H:i:s',$created_at);
            return $date->getTimestamp();
        }
        else
        {
            return null;
        }
        
    }
    
    
}
