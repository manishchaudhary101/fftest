<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/***
 * Class FB_GarminActivitySummary
 * @package App\Models
 * @property integer id
 * @property \DateTime created_at
 * @property \DateTime modified_at
 * @property \DateTime deleted_at
 * @property string userId
 * @property string userAccessToken
 * @property string summaryId
 * @property integer durationInSeconds
 * @property float averageBikeCadenceInRoundsPerMinute
 * @property integer startTimeInSeconds
 * @property integer startTimeOffsetInSeconds
 * @property string activityType
 * @property integer averageHeartRateInBeatsPerMinute
 * @property float averageRunCadenceInStepsPerMinute
 * @property float averageSpeedInMetersPerSecond
 * @property float averagePaceInMinutesPerKilometer
 * @property integer activeKilocalories
 * @property string deviceName
 * @property float distanceInMeters
 * @property float maxBikeCadenceInRoundsPerMinute
 * @property float maxHeartRateInBeatsPerMinute
 * @property float maxPaceInMinutesPerKilometer
 * @property float maxRunCadenceInStepsPerMinute
 * @property float maxSpeedInMetersPerSecond
 * @property integer numberOfActiveLengths
 * @property float startingLatitudeInDegree
 * @property float startingLongitudeInDegree
 * @property integer steps
 * @property float totalElevationGainInMeters
 * @property float totalElevationLossInMeters
 * @property boolean isParent
 * @property string parentSummaryId
 * @property boolean manual
 */
class FB_GarminActivitySummary extends Model
{
    protected $table = 'FB_GarminActivitySummary';
    public function createFromactivitySummary(Array $activitySummary)
    {
        $thisClassProperties = DB::getSchemaBuilder()->getColumnListing($this->table);
        foreach($activitySummary as $key => $value)
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
