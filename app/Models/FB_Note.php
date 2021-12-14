<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/***
 * Class FB_Note
 *
 * @package App\Models
 * @property integer id
 * @property \DateTime created_at
 * @property \DateTime updated_at
 * @property \DateTime deleted_at
 * @property integer FB_User_id
 * @property integer FB_Workout_id
 * @property string note
 */
class FB_Note extends Model
{
    protected $table = 'FB_Note';

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
