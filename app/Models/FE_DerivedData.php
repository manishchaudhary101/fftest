<?php
/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 21-05-2019
 * Time: 10:32
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

/**
 * Class FE_DerivedData
 * @property int id
 * @property \Datetime deleted_at
 * @property int FB_Workout_id
 * @property \Datetime dtime
 * @property int dtype
 * @property float dvalue
 * @property float heartpoint

 */

class FE_DerivedData extends Model{

    protected $table = 'FE_DerivedData';

    public $timestamps = false;
    protected $dates = [
        'dtime',
    ];

//    protected $casts = [
//        'dtime' => 'datetime:'.DEFAULT_DATE_INPUT_FORMAT,
//    ];
    protected $hidden = ['heartpoint'];

    public function hasWorkout()
    {
        return $this->belongsTo('App\Models\FB_Workout','FB_Workout_id','id');
    }

    public function getDtimeAttribute($value)
    {
        if(stripos(Request::url(),'v1/')===false)
        {
            if(empty($value))
                return null;
            else
            {
                $dtimeobj = \DateTime::createFromFormat('Y-m-d H:i:s',$value);
                return $dtimeobj->format(DEFAULT_DATE_INPUT_FORMAT);
            }
        }
        else
        {
            return $value;
        }
    }
}
