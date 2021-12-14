<?php
/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 24-05-2019
 * Time: 10:50
 */

namespace App\Http\Controllers\V6\Workout;

use App\Models\FB_Insight;
use App\Models\FB_User;
use App\Models\FB_Workout;
use App\Models\FE_DerivedData;
use App\Models\FE_LocationData;
use App\Notifications\V1\Workout\WorkoutDeleted;
use App\Notifications\V1\Workout\WorkoutEdited;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

//use mysql_xdevapi\Exception;


class WorkoutController
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

    public function viewWorkout(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'start_time' => 'date_format:'.DEFAULT_DATE_INPUT_FORMAT,
            'end_time' => 'date_format:'.DEFAULT_DATE_INPUT_FORMAT,
        ]);

        if($validation->fails()){
            return response()->json(
                array(
                    'status' => false,
                    'code' => RESPONSE_CODE_ERROR_BAD,
                    'data' => null,
                    'message' => 'Required fields missing',
                    'errors' => $validation->errors(),
                ), RESPONSE_CODE_ERROR_BAD
            );
        }else{
            $currentUser = FB_User::where('id', $request->user_id)
                ->where('status_enum', ENUM_STATUS_ACTIVE)
                ->first();


            if (!empty($currentUser)) {
                $workoutQuery = FB_Workout::
                select('id','created_at','created_by','status_enum','local_id','title','start_time','end_time',
                    'start_time_utc','end_time_utc','start_time_local','biostrip_macid','firmware_version','time_zone',
                    'time_zone_utc_offset','total_distance','avg_pace','activity_type',
                    'updated_source','last_synced_timestamp','is_synced_with_app',
                    'avg_heart_rate', 'phone_os_version', 'app_version', 'source_platform',
                    'avg_strain', 'avg_breathrate', 'avg_shock', 'avg_cadence', 'avg_qtc' ,'avg_qtc' ,
                     'double_buzz_param',  'single_buzz_param', 'double_buzz_limit' ,'single_buzz_limit' , 'double_buzz_val' ,  'single_buzz_val' , 'is_ecg_pdf_generated', 'phone_model', 'ecg_pdf_url','record_type',
                     'avg_hrv'
                )
                    ->where('created_by', $currentUser->id)
                    ->where('status_enum', ENUM_STATUS_ACTIVE);
                   if($request->filter==="true"){
                        $start = $request->time[0];
                        $end = $request->time[1];
                        $workoutQuery = $workoutQuery->whereBetween('start_time_local',[$start,$end]);
                    }

                if(intval($request->input('showtp_st')) == 1)
                {
                    $workoutQuery->addSelect('training_load');
                    $workoutQuery->addSelect('max_strain');
                    $workoutQuery->addSelect('strain_alert_setpoint');
                    $workoutQuery->addSelect('effort_alert_setpoint');
                }
                 if ($request->filled('id') || !empty('created_by')) {

                    $workoutQuery->addSelect('arr_normal');
                    $workoutQuery->addSelect('arr_afib');
                    $workoutQuery->addSelect('arr_others');
                    $workoutQuery->addSelect('arr_noise');
                    $workoutQuery->addSelect('arr_details');
                }
                if(intval($request->input('showbinsync')) == 1)
                {
                    $workoutQuery->addSelect('has_bin_sync');
                }
                if ($request->filled('deriveddata')) {
                    if(intval($request->input('deriveddata')) == 1)
                        $workoutQuery->with(['hasDerivedData']);
                }
                if ($request->filled('locationdata')) {
                    if(intval($request->input('locationdata')) == 1)
                        $workoutQuery->with(['LocationData']);
                }
                if ($request->filled('notes')) {
                    if(intval($request->input('notes')) == 1)
                        $workoutQuery->with(['notes']);
                }
                if ($request->filled('insights')) {
                    if(intval($request->input('insights')) == 1)
                        $workoutQuery->with(['insights']);
                }

                if ($request->filled('id')) {

                    $workoutQuery->where('id',$request->id);
                }
                if ($request->filled('title')) {
                        //$workoutQuery->where('title', 'like', '%' . $request->title . '%');
                        $workoutQuery->where(function ($subQuery) use ($request){
                           $subQuery->where('title', 'like', '%' . $request->title . '%');
                           $subQuery->orWhereHas('notes',function ($notesQuery)  use ($request) {
                              $notesQuery->where('note','like','%'. $request->title . '%');
                           });
                        });
                    }

                if ($request->filled('start_time')) {

                    $startTime = \DateTime::createFromFormat(DEFAULT_DATE_INPUT_FORMAT, $request->start_time);
                    if(!empty($startTime))
                    {
                        $startTime->setTime(0,0,0,1);
                        $workoutQuery->where('start_time_local', '>=', $startTime);
                    }
                }

                if ($request->filled('end_time')) {

                    $endTime = \DateTime::createFromFormat(DEFAULT_DATE_INPUT_FORMAT, $request->end_time);
                    if(!empty($endTime))
                    {
                        $endTime->setTime(23,59,59);
                        $workoutQuery->where('start_time_local', '>=', $endTime);
                    }
                }

                $itemsPerView = PAGINATE_CONSTANTS;
                if ($request->filled('items_per_page') && (int)$request->input('items_per_page') > 0) {
                    $itemsPerView = (int)$request->input('items_per_page');
                }

                $workoutQueryData = $workoutQuery->orderBy('start_time', 'DESC')->paginate($itemsPerView);

                if (!empty($workoutQueryData)) {
                    return response()->json(
                        array(
                            'status' => true,
                            'code' => RESPONSE_CODE_SUCCESS_OK,
                            'data' => $workoutQueryData,
                            'message' => 'Workouts found successfully',
                            'errors' => null,
                        ), RESPONSE_CODE_SUCCESS_OK
                    );
                } else {
                    return response()->json(
                        array(
                            'status' => false,
                            'code' => RESPONSE_CODE_ERROR_BAD,
                            'data' => null,
                            'message' => 'No workout find for you.',
                            'errors' => null,
                        ), RESPONSE_CODE_ERROR_BAD
                    );
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
        }
    }
}