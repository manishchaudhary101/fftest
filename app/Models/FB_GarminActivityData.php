<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/***
 * Class FB_GarminActivityData
 * @package App
 * @property integer id
 * @property integer FB_GarminActivitySummary_id
 * @property \DateTime created_at
 * @property \DateTime modified_at
 * @property \DateTime deleted_at
 * @property string summaryId
 * @property integer startTimeInSeconds
 * @property float latitudeInDegree
 * @property float longitudeInDegree
 * @property float elevationInMeters
 * @property float airTemperatureCelcius
 * @property integer heartRate
 * @property float speedMetersPerSecond
 * @property float stepsPerMinute
 * @property float totalDistanceInMeters
 * @property integer timerDurationInSeconds
 * @property integer clockDurationInSeconds
 * @property integer movingDurationInSeconds
 * @property float powerInWatts
 * @property float bikeCadenceInRPM
 * @property float swimCadenceInStrokesPerMinute
 * @property integer FB_User_id
 * @property integer FB_Workout_id
 *
 */
class FB_GarminActivityData extends Model
{
    protected $table = 'FB_GarminActivityData';
    public function createFromActivitySample(Array $activityDetail)
    {
        $thisClassProperties = DB::getSchemaBuilder()->getColumnListing($this->table);
        foreach($activityDetail as $key => $value)
        {
            if(in_array($key,$thisClassProperties))
            {
                switch (DB::getSchemaBuilder()->getColumnType($this->table,$key))
                {
                    case 'bigint':
                    case 'integer':
                        $value = (int) $value;
                        break;
                    case 'decimal':
                        if(stripos($key,'Degree') !== false)
                        {
                            $value = round($value,7);
                        }
                        else
                        {
                            $value = round($value,2);
                        }

                        break;
                }
                $this->setAttribute($key,$value);
            }
        }
    }
}
