<?php
/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 21-05-2019
 * Time: 11:21
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * Class FE_LocationData
 * @property int id
 * @property \Datetime deleted_at
 * @property int FB_Workout_id
 * @property double latitude
 * @property double longitude
 * @property double altitude
 * @property integer speed
 * @property integer distance
 * @property integer timestamp_epoch
 * @property string timestamp_local
 */
class FE_LocationData extends Model{

    protected $table = 'FE_LocationData';

    public function hasWorkout()
    {
        return $this->belongsTo('App\Models\FB_Workout','FB_Workout_id','id');
    }
}
