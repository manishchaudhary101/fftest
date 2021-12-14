<?php

namespace App\Http\Controllers\V7\Workout;

use App\Models\FB_Insight;
use App\Models\FB_User;
use App\Models\FB_Workout;
use App\Models\FE_DerivedData;
use App\Models\FE_LocationData;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
class WorkoutController extends Controller
{
     /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createWorkout(Request $request)
    {


        $validation = Validator::make($request->all(), [
//            'biostrip_macid' => 'required',
//            'firmware_version' => 'required',
            'workoutsdata' => 'required',
        ]);

        if ($validation->fails()) {
            return response()->json(
                array(
                    'status' => false,
                    'code' => RESPONSE_CODE_ERROR_BAD,
                    'data' => null,
                    'message' => 'Required fields missing',
                    'errors' => $validation->errors(),
                ), RESPONSE_CODE_ERROR_BAD
            );
        } else {


            $currentUser = FB_User::where('id', $request->user_id)
                ->where('status_enum', ENUM_STATUS_ACTIVE)
                ->first();

            $realWorkoutsDataArray = json_decode($request->input('workoutsdata'), true);
            if (isset($realWorkoutsDataArray['workoutsdata'])) {
                $realWorkoutsDataArray = $realWorkoutsDataArray['workoutsdata'];

            } else {
                return response()->json(
                    array(
                        'status' => false,
                        'code' => RESPONSE_CODE_ERROR_BAD,
                        'data' => null,
                        'message' => 'Invalid workoutsdata structure',
                        'errors' => $validation->errors(),
                    ), RESPONSE_CODE_ERROR_BAD
                );
            }

            $totalWorkoutsAdded = 0;
            $totalWorkoutsUpdated = 0;
            $createdWorkoutIds = array();
            if (!empty($currentUser)) {

                foreach ($realWorkoutsDataArray as $WKData) {

                    $startTimeUTC = \DateTime::createFromFormat(DEFAULT_DATE_INPUT_FORMAT, $WKData['startTime_UTC']);
                    $endTimeUTC = \DateTime::createFromFormat(DEFAULT_DATE_INPUT_FORMAT, $WKData['endTime_UTC']);
                    $startTimeLocal = \DateTime::createFromFormat(DEFAULT_DATE_INPUT_FORMAT, $WKData['startTime_Local']);

                    if(empty($startTimeLocal))
                    {
                        Log::debug('----------- incorrect startTime_Local format -----------');

                        Log::debug($WKData['startTime_Local']);
                        Log::debug($startTimeLocal);
                        Log::debug($WKData);

                        $startTime = null;
                        $endTime = null;

                    }
                    else
                    {
                        //generated values for backward compatibility
                        $startTime = clone $startTimeLocal;
                        $endTime = clone $endTimeUTC;
                        $endTime->setTimezone($startTime->getTimezone());
                    }


                    $workout = new FB_Workout();

                    if(!empty($startTimeUTC) && !empty($endTimeUTC) && $endTimeUTC > $startTimeUTC)
                    {

                        //check for duplicate
                        $duplicateCheck = FB_Workout::where('biostrip_macid', '=', $WKData['biostrip_macid'])
//                            ->where('start_time', '=', $startTime) //TODO: Make backward compatible
                            ->where('created_by', '=', $currentUser->id)
                            ->where('start_time_utc', '=', $startTimeUTC)
                            ->where('end_time_utc', '=', $endTimeUTC)
                            ->where('status_enum', '=', ENUM_STATUS_ACTIVE)
                            ->first();

                        //only add a new record if theres no duplicate
                        if ((empty($duplicateCheck) )) {


                            if (!empty($workout) ) {

                                $workout->title = $WKData['title'];
                                $workout->start_time_utc = $startTimeUTC;
                                $workout->start_time_local = $WKData['startTime_Local'];
                                $workout->end_time_utc = $endTimeUTC;

                                $workout->start_time = $startTime;
                                $workout->end_time = $endTime;

                                if(isset($WKData['time_zone_UTC_Offset']))
                                    $workout->time_zone_utc_offset = (int)$WKData['time_zone_UTC_Offset'];
                                else
                                {
                                    if(!empty($startTimeLocal))
                                    $workout->time_zone_utc_offset = $startTimeLocal->getOffset();
                                }

                                if(!empty($startTimeLocal))
                                    $workout->time_zone = $startTimeLocal->getTimezone()->getName();

                                $workout->created_by = $currentUser->id;
                                $workout->created_at = new \DateTime();
                                $workout->status_enum = ENUM_STATUS_ACTIVE;

                                if (isset($WKData['local_id']))
                                    $workout->local_id = $WKData['local_id'];
                                if (isset($WKData['rated_perceived_exertion']))
                                    $workout->rated_perceived_exertion = $WKData['rated_perceived_exertion'];

                                if (isset($WKData['activity_type']))
                                    $workout->activity_type = $WKData['activity_type'];

                                if (isset($WKData['avg_pace']))
                                {
                                    if (intval($WKData['avg_pace']) > 60){
                                        $workout->avg_pace = 0;
                                    }else{
                                        $workout->avg_pace = $WKData['avg_pace'];
                                    }
                                }               

                                if (isset($WKData['total_distance']))
                                    $workout->total_distance = $WKData['total_distance'];

                                if (isset($WKData['web_link']))
                                    $workout->web_link = $WKData['web_link'];

                                if(isset($WKData['firmware_version']))
                                    $workout->firmware_version = $WKData['firmware_version'];

                                if(isset($WKData['biostrip_macid']))
                                    $workout->biostrip_macid = $WKData['biostrip_macid'];

                                //v3 new fields start here

                                if(isset($WKData['source_platform']))
                                    $workout->source_platform = $WKData['source_platform'];

                                if(isset($WKData['app_version']))
                                    $workout->app_version = $WKData['app_version'];

                                if(isset($WKData['phone_os_version']))
                                    $workout->phone_os_version = $WKData['phone_os_version'];

                                if(isset($WKData['training_load']))
                                    $workout->training_load = $WKData['training_load'];

                                if(isset($WKData['max_strain']))
                                    $workout->max_strain = $WKData['max_strain'];

                                if(isset($WKData['avg_heart_rate']))
                                    $workout->avg_heart_rate = $WKData['avg_heart_rate'];

                                if(isset($WKData['avg_hrv']))
                                    $workout->avg_hrv = $WKData['avg_hrv'];    
                                //end v3 new fields
                                
                                //v4 fields
                                if(isset($WKData['avg_strain']))
                                    $workout->avg_strain = $WKData['avg_strain'];
                                if(isset($WKData['avg_breathrate']))
                                    $workout->avg_breathrate = $WKData['avg_breathrate'];
                                if(isset($WKData['avg_shock']))
                                    $workout->avg_shock = $WKData['avg_shock'];
                                if(isset($WKData['avg_cadence']))
                                    $workout->avg_cadence = $WKData['avg_cadence'];
                                if(isset($WKData['avg_qtc']))
                                    $workout->avg_qtc = $WKData['avg_qtc'];
                                //end v4 fields

                                                                
                                //v6 fields
                                if(isset($WKData['double_buzz_param']))
                                    $workout->double_buzz_param = $WKData['double_buzz_param'];
                                if(isset($WKData['single_buzz_param']))
                                    $workout->single_buzz_param = $WKData['single_buzz_param'];
                                if(isset($WKData['double_buzz_limit']))
                                    $workout->double_buzz_limit = $WKData['double_buzz_limit'];
                                if(isset($WKData['single_buzz_limit']))
                                    $workout->single_buzz_limit = $WKData['single_buzz_limit'];
                                if(isset($WKData['double_buzz_val']))
                                    $workout->double_buzz_val = $WKData['double_buzz_val'];
                                if(isset($WKData['single_buzz_val']))
                                    $workout->single_buzz_val = $WKData['single_buzz_val'];
                                if(isset($WKData['phone_model']))
                                $workout->phone_model = $WKData['phone_model'];
                                //end v6 fields
    
    
    //hotfix setpoints
                                if(isset($WKData['effort_alert_setpoint']))
                                    $workout->effort_alert_setpoint = $WKData['effort_alert_setpoint'];
                                if(isset($WKData['strain_alert_setpoint']))
                                    $workout->strain_alert_setpoint = $WKData['strain_alert_setpoint'];
    //end hotfix
    
                                $workout->save();
                                $totalWorkoutsAdded++;
                                $createdWorkoutIds[] = array(
                                    'startTime_UTC' => $workout->start_time_utc->format(DEFAULT_DATE_INPUT_FORMAT),
                                    'workoutId' => $workout->id,
                                    'biostrip_macid' => $workout->biostrip_macid,
                                    'startTime_epoch' => $startTimeUTC->format('U')*1000,  
                                    'endTime_epoch' => $endTimeUTC->format('U')*1000,
                                );
    
                                if(isset($WKData['notes']))
                                {
                                    if(is_array($WKData['notes']))
                                    {
                                        foreach($WKData['notes'] as $note)
                                        {
                                            $FB_Note = new \App\Models\FB_Note();
                                            $FB_Note->FB_User_id = $currentUser->id;
                                            $FB_Note->FB_Workout_id = $workout->id;
                                            $FB_Note->note = $note;
                                            $FB_Note->save();
                                        }
                                    }
                                    else if(is_string($WKData['notes']))
                                    {
                                        $FB_Note = new \App\Models\FB_Note();
                                        $FB_Note->FB_User_id = $currentUser->id;
                                        $FB_Note->FB_Workout_id = $workout->id;
                                        $FB_Note->note = trim($WKData['notes']);
                                        $FB_Note->save();
                                    }
                                }

                                if (!empty($WKData['derived_data'])) {
                                    foreach ($WKData['derived_data'] as $data) {

                                        $deriveData = new FE_DerivedData();
                                        if (isset($data['dtype'])) {
                                            $deriveData->dtype = $data['dtype'];
                                        } else {
                                            $deriveData->dtype = 0;
                                        }

                                        if (isset($data['dtime'])) {
                                            try {
                                                $dTime = \DateTime::createFromFormat(DEFAULT_DATE_INPUT_FORMAT, $data['dtime']);
                                                $deriveData->dtime = $dTime;
                                            } catch (\Exception $e) {
                                                $deriveData->dtime = new \DateTime();
                                            }
                                        } else {
                                            $deriveData->dtime = new \DateTime();
                                        }

                                        if (isset($data['dvalue'])) {
                                            $deriveData->dvalue = $data['dvalue'];
                                        } else {
                                            $deriveData->dvalue = 0;
                                        }

                                        //calculate heartpoint
                                        if ($deriveData->dtype == DERIVED_DTYPE_BREATHRATE) {
                                            $heartpoint = 0.0;
                                            if ($deriveData->dvalue >= 0 && $deriveData->dvalue < 15) {
                                                $heartpoint += 0.0;
                                            } else if ($deriveData->dvalue >= 15 && $deriveData->dvalue < 20) {
//                                                $heartpoint += (1.0);
                                                $heartpoint += 0.0;
                                            } else if ($deriveData->dvalue >= 20  && $deriveData->dvalue < 25) {
//                                                $heartpoint += (2.0);
                                                $heartpoint += (0.0);
                                            } else if ($deriveData->dvalue >= 25  && $deriveData->dvalue < 30) {
                                                $heartpoint += (3.0);
                                            } else if ($deriveData->dvalue >= 30  && $deriveData->dvalue < 35) {
                                                $heartpoint += (4.0);
                                            } else if ($deriveData->dvalue >= 35 && $deriveData->dvalue < 40) {
                                                $heartpoint += (5.0);
                                            } else if ($deriveData->dvalue >= 40 && $deriveData->dvalue < 45) {
                                                $heartpoint += (6.0);
                                            } else if ($deriveData->dvalue >= 45 && $deriveData->dvalue < 50) {
                                                $heartpoint += (7.0);
                                            } else if ($deriveData->dvalue >= 50 && $deriveData->dvalue < 55) {
                                                $heartpoint += (8.0);
                                            } else if ($deriveData->dvalue >= 55 && $deriveData->dvalue < 60) {
                                                $heartpoint += (9.0);
                                            } else if ($deriveData->dvalue >= 60) {
                                                $heartpoint += (10.0);
                                            }

                                            $deriveData->heartpoint = $heartpoint;
                                        }

                                        $deriveData->hasWorkout()->associate($workout);
                                        $deriveData->save();

                                    }
                                }

                                if (!empty($WKData['location_data'])) {
                                    foreach ($WKData['location_data'] as $data) {

                                        $locationata = new FE_LocationData();

                                        if (isset($data['latitude'])) {
                                            $locationata->latitude = round($data['latitude'],NUMBER_OF_DECIMALS_ROUNDOFF_LOCATION);
                                        } else {
                                            $locationata->latitude = 0;
                                        }
                                        if (isset($data['longitude'])) {
                                            $locationata->longitude = round($data['longitude'],NUMBER_OF_DECIMALS_ROUNDOFF_LOCATION);
                                        } else {
                                            $locationata->longitude = 0;
                                        }
                                        if (isset($data['altitude'])) {
                                            $locationata->altitude = round($data['altitude'],NUMBER_OF_DECIMALS_ROUNDOFF_LOCATION);
                                        } else {
                                            $locationata->altitude = 0;
                                        }
                                        if (isset($data['speed'])) {
                                            $locationata->speed = $data['speed'];
                                        } else {
                                            $locationata->speed = 0;
                                        }
                                        if (isset($data['distance'])) {
                                            $locationata->distance = $data['distance'];
                                        } else {
                                            $locationata->distance = 0;
                                        }
                                        if (isset($data['timestamp_epoch'])) {
                                            $locationata->timestamp_epoch = $data['timestamp_epoch'];
                                        } else {
                                            $locationata->timestamp_epoch = null;
                                        }
                                        if (isset($data['timestamp_local'])) {
                                            $locationata->timestamp_local = $data['timestamp_local'];
                                        } else {
                                            $locationata->timestamp_local = null;
                                        }

                                        $locationata->hasWorkout()->associate($workout);
                                        $locationata->save();

                                    }
                                }



                            }
                        }
                        else
                        {
                            $createdWorkoutIds[] = array(
                                'startTime_UTC' => $duplicateCheck->start_time_utc->format(DEFAULT_DATE_INPUT_FORMAT),
                                'workoutId' => $duplicateCheck->id,
                                'biostrip_macid' => $duplicateCheck->biostrip_macid,
                                'startTime_epoch' => $startTimeUTC->format('U')*1000,  
                                'endTime_epoch' => $endTimeUTC->format('U')*1000,
                                );
                        }

                    }

                }


            } else {
                return response()->json(
                    array(
                        'status' => false,
                        'code' => RESPONSE_CODE_ERROR_BAD,
                        'data' => null,
                        'message' => 'User not found.',
                        'errors' => null,
                    ), RESPONSE_CODE_ERROR_BAD
                );
            }


            if (!empty($realWorkoutsDataArray)) {
                return response()->json(
                    array(
                        'status' => true,
                        'code' => RESPONSE_CODE_SUCCESS_OK,
                        'data' => $createdWorkoutIds,
                        'message' => 'New Workouts Added:' . $totalWorkoutsAdded ,
                        'errors' => null,
                    ), RESPONSE_CODE_SUCCESS_OK
                );
            } else {
                return response()->json(
                    array(
                        'status' => false,
                        'code' => RESPONSE_CODE_ERROR_BAD,
                        'data' => null,
                        'message' => 'something went wrong, Please try again',
                        'errors' => null,
                    ), RESPONSE_CODE_ERROR_BAD
                );
            }


        }
        
    }
}
