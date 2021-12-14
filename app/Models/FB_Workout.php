<?php
/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 21-05-2019
 * Time: 09:59
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class FB_Workout
 * @property int id
 * @property \Datetime created_at
 * @property \Datetime modified_at
 * @property \Datetime deleted_at
 * @property int created_by
 * @property int modified_by
 * @property int status_enum
 * @property string title
 * @property \Datetime start_time_utc
 * @property string start_time_local
 * @property \Datetime end_time_utc
 * @property \Datetime start_time
 * @property \Datetime end_time
 * @property int time_zone_utc_offset
 * @property int local_id
 * @property int activity_type
 * @property string biostrip_macid
 * @property float firmware_version
 * @property string time_zone
 * @property int training_load
 * @property int max_strain
 * @property bool has_bin_sync
 * @property int strain_alert_setpoint
 * @property int effort_alert_setpoint
 * @property int total_distance
 * @property float avg_pace
 * @property integer updated_source
 * @property \DateTime last_synced_timestamp
 * @property boolean is_synced_with_app
 * @property int avg_heart_rate
 * @property string phone_os_version
 * @property string app_version
 * @property string source_platform
 * @property boolean is_stream
 */
class FB_Workout extends Model{

    protected $table = 'FB_Workout';

    use SoftDeletes;
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'seen_at',
        'start_time_utc',
        'end_time_utc',
    ];

    protected $casts = [
        'start_time_utc' => 'datetime:'.DEFAULT_DATE_INPUT_FORMAT,
        'end_time_utc' => 'datetime:'.DEFAULT_DATE_INPUT_FORMAT,
        'firmware_version' => 'float',
        'avg_pace' => 'float',
        'avg_strain' => 'float',
        'avg_breathrate' => 'integer',
        'avg_shock' => 'integer',
        'avg_cadence' => 'integer',
        'avg_qtc' => 'float',
        'is_stream' => 'boolean',
    ];

//    protected $dateFormat = DEFAULT_DATE_INPUT_FORMAT;

     public function hasDerivedData()
    {
        return $this->hasMany('App\Models\FE_DerivedData','FB_Workout_id','id');
    }
     public function locationData()
    {
        return $this->hasMany('App\Models\FE_LocationData','FB_Workout_id','id');
    }
    public function notes()
    {
        return $this->hasMany('App\Models\FB_Note','FB_Workout_id','id');
    }
    public function insights()
    {
        return $this->belongsToMany('\App\Models\FB_Insight','FR_Insight_has_workouts','FB_Workout_id','FB_Insight_id');
    }
}
