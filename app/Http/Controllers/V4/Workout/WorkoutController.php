<?php
/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 24-05-2019
 * Time: 10:50
 */

namespace App\Http\Controllers\V4\Workout;

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
                            ->where('start_time_utc', '=', $startTimeUTC)
                            ->where('created_by', '=', $currentUser->id)
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

                                if (isset($WKData['activity_type']))
                                    $workout->activity_type = $WKData['activity_type'];

                                if (isset($WKData['avg_pace']))
                                    $workout->avg_pace = $WKData['avg_pace'];

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
                selectRaw('id,created_at,created_by,status_enum,local_id,title,start_time,end_time,
                start_time_utc,end_time_utc,start_time_local,biostrip_macid,firmware_version,time_zone,
                time_zone_utc_offset,total_distance,avg_pace,if(activity_type=5,NULL,activity_type) AS activity_type,
                updated_source,last_synced_timestamp,is_synced_with_app,
                avg_heart_rate, phone_os_version, app_version, source_platform,
                avg_strain, avg_breathrate, avg_shock, avg_cadence, avg_qtc'
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

    public function deleteWorkout(Request $request)
    {


        $validation = Validator::make($request->all(), [
            'workoutIDs' => 'required',
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

            $totalWorkoutsDeleted = 0;
            $deletedWorkoutIds = array();
            if (!empty($currentUser)) {
                $workoutIdsToDelete = json_decode($request->input('workoutIDs'));
                if(!empty($workoutIdsToDelete))
                {
                    foreach($workoutIdsToDelete as $wrkid)
                    {
                        $workout = FB_Workout::where('id', '=', $wrkid)
                            ->where('created_by', '=', $currentUser->id)
                            ->where('status_enum', '=', ENUM_STATUS_ACTIVE)
                            ->first();

                        //Edit workout that was found
                        if (!empty($workout)) {

                            array_push($deletedWorkoutIds,$workout->id);
                            $workout->source_platform = $request->input('source_platform');
                            if($request->source_platform == 0){
                                $workout->is_synced_with_app = 0;   
                            }
                            if($request->source_platform == 0){
                                $workout->source_platform = 0;   
                            }
                            $workout->updated_source = $request->source_platform;
                            $workout->update();
                            $workout->delete();
                            $currentUser->notify(new WorkoutDeleted($workout));
                            Log::debug('---notified---');
                            $totalWorkoutsDeleted++;

                        }
                    }

                }else {
                    return response()->json(
                        array(
                            'status' => false,
                            'code' => RESPONSE_CODE_ERROR_BAD,
                            'data' => null,
                            'message' => 'Bad workoutIDs structure',
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


            if (!empty($workoutIdsToDelete)) {
                return response()->json(
                    array(
                        'status' => true,
                        'code' => RESPONSE_CODE_SUCCESS_OK,
                        'data' => $deletedWorkoutIds,
                        'message' => 'Workouts Deleted:' . $totalWorkoutsDeleted,
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

    // new getttl workout  ttl =totla training load  sum workout aof training load
    public function viewTTLWorkout(Request $request)
    {
        $currentUser = FB_User::where('id', $request->user_id)
        ->where('status_enum', ENUM_STATUS_ACTIVE)
        ->first();
        $ttlQuery = FB_Workout::selectRaw('sum(training_load) as count, max(training_load) as max, date(start_time_local) as Date')
             ->groupBy('Date')
             ->where('created_by',$currentUser->id)
             ->orderBy('Date', 'DESC')
             ->whereNull('deleted_at')
             ->get();
             if($request->filter==="true"){
                $start = $request->time[0];
                $end = $request->time[1];
                $ttlQuery = $ttlQuery->whereBetween('start_time_local',[$start,$end]);
            }
        $ttlQueryData = $ttlQuery;
        if (!empty($ttlQueryData)) {
            return response()->json(
                array(
                    'status' => true,
                    'code' => RESPONSE_CODE_SUCCESS_OK,
                    'data' => $ttlQueryData,
                    'message' => 'TTL Data found successfully',
                    'errors' => null,
                ), RESPONSE_CODE_SUCCESS_OK
            );
        }
    }
    // api ends hear

    public function getWeeksStats(Request $request)
    {
        $currentUser = FB_User::where('id', $request->user_id)
            ->where('status_enum', ENUM_STATUS_ACTIVE)
            ->first();

        if(!empty($currentUser))
        {
            $offsetInMin = '';
            $offsetTimeObj = '';
            $start_date = new \DateTime();
            $last_week_start_date = null;
            $insightsCount = FB_Insight::where('created_by',$request->user_id)->count();

            if($request->filled('user_timezone_offset') && is_numeric($request->input('user_timezone_offset')))
            {

                $offsetInMin = $request->input('user_timezone_offset');
                $offsetTimeObj = date('Hi',mktime(0,intval($offsetInMin)));
                $sign = '-';
                if(substr($offsetInMin,0,1) == '-' || intval($offsetInMin) < 0) //invert because javascript gives  UTC - Local in minutes, whilew we need local - UTC
                {
                    $sign = '+';
                    $offsetTimeObj = date('Hi',mktime(0,intval($offsetInMin) * -1));
                }

                try
                {
                    $start_date =  new \DateTime('now', new \DateTimeZone($sign.$offsetTimeObj));
                    $start_date->modify('last Sunday, 11:59:59PM');
                }catch(\Exception $e)
                {
                    Log::debug($e->getMessage());
                    Log::debug($e->getTraceAsString());
                }

            }
            if(!empty($start_date))
            {
                $last_week_start_date = clone $start_date;
                $last_week_start_date->modify('7 days ago');
            }


            //all time stats
            $workoutStats = FB_Workout::selectRaw('count(id) as activities, ROUND(sum(time_to_sec(timediff(end_time,start_time )) / 3600)) as active_minutes')
                ->where('created_by',$currentUser->id)
                ->get()->first();
            //this week stats
//            $start_date = new \DateTime('last sunday 11:59:59PM'); //UTC
            $end_date = new \DateTime('tomorrow');


            $training_load = FB_Workout::selectRaw('sum(training_load) as weekly_training_load')
                ->where('created_by',$currentUser->id)
                ->whereBetween('start_time',[$start_date,$end_date]) //local
                ->get()->pluck('weekly_training_load')->first();

            $last_week_training_load = FB_Workout::selectRaw('sum(training_load) as weekly_training_load')
                ->where('created_by',$currentUser->id)
                ->whereBetween('start_time',[$last_week_start_date,$start_date]) //local
                ->get()->pluck('weekly_training_load')->first();

            $workoutIdsInThisWeek = FB_Workout::select('id')
                ->where('created_by',$currentUser->id)
                ->whereBetween('start_time',[$start_date,$end_date])
                ->get()->pluck('id')->toArray();

            $derivedDataInThisWeek_Strain = FE_DerivedData::where('dtype',DERIVED_DTYPE_STRAIN)
                ->whereIn('FB_Workout_id',$workoutIdsInThisWeek)
                ->get();

            $derivedDataInThisWeek_Effort = FE_DerivedData::where('dtype',DERIVED_DTYPE_BREATHRATE)
                ->whereIn('FB_Workout_id',$workoutIdsInThisWeek)
                ->get();

            //counts
            $strainDistribution = array(
                DISTRIBUTION_COLOR_BLUE1 => 0,
                DISTRIBUTION_COLOR_BLUE2 => 0,
                DISTRIBUTION_COLOR_GREEN1 => 0,
                DISTRIBUTION_COLOR_GREEN2 => 0,
                DISTRIBUTION_COLOR_YELLOW1 => 0,
                DISTRIBUTION_COLOR_YELLOW2 => 0,
                DISTRIBUTION_COLOR_ORANGE1 => 0,
                DISTRIBUTION_COLOR_ORANGE2 => 0,
                DISTRIBUTION_COLOR_RED1 => 0,
                DISTRIBUTION_COLOR_RED2 => 0,
            );

            $effortDistribution = array(
                DISTRIBUTION_COLOR_BLUE1 => 0,
                DISTRIBUTION_COLOR_BLUE2 => 0,
                DISTRIBUTION_COLOR_GREEN1 => 0,
                DISTRIBUTION_COLOR_GREEN2 => 0,
                DISTRIBUTION_COLOR_YELLOW1 => 0,
                DISTRIBUTION_COLOR_YELLOW2 => 0,
                DISTRIBUTION_COLOR_ORANGE1 => 0,
                DISTRIBUTION_COLOR_ORANGE2 => 0,
                DISTRIBUTION_COLOR_RED1 => 0,
                DISTRIBUTION_COLOR_RED2 => 0,
            );
            $totalStrainValue = 0;
            $totalStrainCount = 0;
            $totalEffortValue = 0;
            $totalEffortCount = 0;

            $distribution_color_blue1 = 0;
            $distribution_color_blue2 = 0;
            $distribution_color_green1 = 0;
            $distribution_color_green2 = 0;
            $distribution_color_yellow1 = 0;
            $distribution_color_yellow2 = 0;
            $distribution_color_orange1 = 0;
            $distribution_color_orange2 = 0;
            $distribution_color_red1 = 0;
            $distribution_color_red2 = 0;

            foreach($derivedDataInThisWeek_Strain as $ddata)
            {
                if(empty($ddata)) //for autocomplete only
                    $ddata = new FE_DerivedData();

               if($ddata->dtype == DERIVED_DTYPE_STRAIN)
                {
                    $totalStrainValue += $ddata->dvalue;
                    $totalStrainCount++;
                    if ($ddata->dvalue < 25) {
                        $strainDistribution[DISTRIBUTION_COLOR_BLUE1]++;
                    } else if ($ddata->dvalue >= 25 && $ddata->dvalue < 50) {
                        $strainDistribution[DISTRIBUTION_COLOR_BLUE2]++;
                    } else if ($ddata->dvalue >= 50 && $ddata->dvalue < 75) {
                        $strainDistribution[DISTRIBUTION_COLOR_GREEN1]++;
                    } else if ($ddata->dvalue >= 75 && $ddata->dvalue < 100) {
                        $strainDistribution[DISTRIBUTION_COLOR_GREEN2]++;
                    } else if ($ddata->dvalue >= 100 && $ddata->dvalue < 125) {
                        $strainDistribution[DISTRIBUTION_COLOR_YELLOW1]++;
                    } else if ($ddata->dvalue >= 125 && $ddata->dvalue < 150) {
                        $strainDistribution[DISTRIBUTION_COLOR_YELLOW2]++;
                    } else if ($ddata->dvalue >= 150 && $ddata->dvalue < 175) {
                        $strainDistribution[DISTRIBUTION_COLOR_ORANGE1]++;
                    } else if ($ddata->dvalue >= 175 && $ddata->dvalue < 200) {
                        $strainDistribution[DISTRIBUTION_COLOR_ORANGE2]++;
                    } else if ($ddata->dvalue >= 200 && $ddata->dvalue < 225) {
                        $strainDistribution[DISTRIBUTION_COLOR_RED1]++;
                    } else if ($ddata->dvalue >= 225) {
                        $strainDistribution[DISTRIBUTION_COLOR_RED2]++;
                    }
                }
            }
            foreach($derivedDataInThisWeek_Effort as $ddata)
            {
                if(empty($ddata)) //for autocomplete only
                    $ddata = new FE_DerivedData();

                if($ddata->dtype == DERIVED_DTYPE_BREATHRATE)
                {
                    $totalEffortValue += $ddata->dvalue;
                    $totalEffortCount++;

                    if ($ddata->dvalue < 15) {
                        $effortDistribution[DISTRIBUTION_COLOR_BLUE1]++;
                    } else if ($ddata->dvalue >= 15 && $ddata->dvalue < 20) {
                        $effortDistribution[DISTRIBUTION_COLOR_BLUE2]++;
                    } else if ($ddata->dvalue >= 20 && $ddata->dvalue < 25) {
                        $effortDistribution[DISTRIBUTION_COLOR_GREEN1]++;
                    } else if ($ddata->dvalue >= 25 && $ddata->dvalue < 30) {
                        $effortDistribution[DISTRIBUTION_COLOR_GREEN2]++;
                    } else if ($ddata->dvalue >= 30 && $ddata->dvalue < 35) {
                        $effortDistribution[DISTRIBUTION_COLOR_YELLOW1]++;
                    } else if ($ddata->dvalue >= 35&& $ddata->dvalue < 40) {
                        $effortDistribution[DISTRIBUTION_COLOR_YELLOW2]++;
                    } else if ($ddata->dvalue >= 40 && $ddata->dvalue < 45) {
                        $effortDistribution[DISTRIBUTION_COLOR_ORANGE1]++;
                    } else if ($ddata->dvalue >= 45 && $ddata->dvalue < 50) {
                        $effortDistribution[DISTRIBUTION_COLOR_ORANGE2]++;
                    } else if ($ddata->dvalue >= 50 && $ddata->dvalue < 55) {
                        $effortDistribution[DISTRIBUTION_COLOR_RED1]++;
                    } else if ($ddata->dvalue >= 55) {
                        $effortDistribution[DISTRIBUTION_COLOR_RED2]++;
                    }
                }

            }

            $averageEffort = 0;
            $averageStrain = 0;
            if($totalStrainValue > 0 && $totalStrainCount > 0)
            {
                $averageStrain = round($totalStrainValue / $totalStrainCount,0,PHP_ROUND_HALF_DOWN);
            }
            if($totalEffortValue > 0 && $totalEffortCount > 0)
            {
                $averageEffort = round($totalEffortValue / $totalEffortCount,0,PHP_ROUND_HALF_DOWN);
            }

            $TTL = ((int)$last_week_training_load > DEFAULT_TRAINING_GOAL ? (int)$last_week_training_load : DEFAULT_TRAINING_GOAL);
            $WTL = intval($training_load);
            $dialogue = '';

            if(!empty($TTL))
            {
                //more than 1 week
                if((int)$last_week_training_load > 0)
                {
                    if($WTL < 0.8 * $TTL)
                    {
                        $dialogue = TRAINING_LOAD_DIALOGUE_2WEEK['blue'];
                    }
                    elseif ($WTL >= (0.8 * $TTL) && $WTL < $TTL)
                    {
                        $dialogue = TRAINING_LOAD_DIALOGUE_2WEEK['green'];
                    }
                    elseif ($WTL >= $TTL && $WTL < (1.1 * $TTL))
                    {
                        $dialogue = TRAINING_LOAD_DIALOGUE_2WEEK['darkgreen'];
                    }
                    elseif ($WTL >= (1.1 * $TTL) && $WTL < (1.3 * $TTL))
                    {
                        $dialogue = TRAINING_LOAD_DIALOGUE_2WEEK['yellow'];
                    }
                    elseif ($WTL >= (1.3 * $TTL) && $WTL < (1.5 * $TTL))
                    {
                        $dialogue = TRAINING_LOAD_DIALOGUE_2WEEK['orange'];
                    }
                    elseif ($WTL >= (1.5 * $TTL))
                    {
                        $dialogue = TRAINING_LOAD_DIALOGUE_2WEEK['red'];
                    }
                }
                else //less than 1 week
                {
                    if($WTL < 0.8 * $TTL)
                    {
                        $dialogue = TRAINING_LOAD_DIALOGUE_1WEEK['blue'];
                    }
                    elseif ($WTL >= (0.8 * $TTL) && $WTL < $TTL)
                    {
                        $dialogue = TRAINING_LOAD_DIALOGUE_1WEEK['green'];
                    }
                    elseif ($WTL >= $TTL && $WTL < (1.1 * $TTL))
                    {
                        $dialogue = TRAINING_LOAD_DIALOGUE_1WEEK['darkgreen'];
                    }
                    elseif ($WTL >= (1.1 * $TTL) && $WTL < (1.3 * $TTL))
                    {
                        $dialogue = TRAINING_LOAD_DIALOGUE_1WEEK['yellow'];
                    }
                    elseif ($WTL >= (1.3 * $TTL) && $WTL < (1.5 * $TTL))
                    {
                        $dialogue = TRAINING_LOAD_DIALOGUE_1WEEK['orange'];
                    }
                    elseif ($WTL >= (1.5 * $TTL))
                    {
                        $dialogue = TRAINING_LOAD_DIALOGUE_1WEEK['red'];
                    }
                }
            }

            return response()->json(
                array(
                    'status' => true,
                    'code' => RESPONSE_CODE_SUCCESS_OK,
                    'data' => array(
                        'all_time_stats' =>$workoutStats,
                        'insights_count' =>$insightsCount,
                        'this_week' =>array(
                                        'user_time' => $start_date,
                                        'intval($offsetInMin)' => intval($offsetInMin),
                                        '$offsetTimeObj' => $offsetTimeObj,
                                        'start_date' => $start_date,
                                        'end_date' => $end_date,
                                        'training_load' => $WTL,
                                        'training_goal' => $TTL,
                                        'dialogue' => $dialogue,
                                        'insights_percent' => 50,
                                        'effort_total' => $totalEffortValue,
                                        'strain_total' => $totalStrainValue,
                                        'effort_count' => $totalEffortCount,
                                        'strain_count' => $totalStrainCount,
                                        'effort_avg' => $averageEffort,
                                        'strain_avg' => $averageStrain,
                                        'effort_distribution' => array(
                                            array(
                                                'label' => $effortDistribution[DISTRIBUTION_COLOR_BLUE1],
                                                'backgroundColor' => DISTRIBUTION_COLOR_BLUE1,
                                                'data' => array($effortDistribution[DISTRIBUTION_COLOR_BLUE1]),
                                            ),
                                            array(
                                                'label' => $effortDistribution[DISTRIBUTION_COLOR_BLUE2],
                                                'backgroundColor' => DISTRIBUTION_COLOR_BLUE2,
                                                'data' => array($effortDistribution[DISTRIBUTION_COLOR_BLUE2]),
                                            ),
                                            array(
                                                'label' => $effortDistribution[DISTRIBUTION_COLOR_GREEN1],
                                                'backgroundColor' => DISTRIBUTION_COLOR_GREEN1,
                                                'data' => array($effortDistribution[DISTRIBUTION_COLOR_GREEN1]),
                                            ),
                                            array(
                                                'label' => $effortDistribution[DISTRIBUTION_COLOR_GREEN2],
                                                'backgroundColor' => DISTRIBUTION_COLOR_GREEN2,
                                                'data' => array($effortDistribution[DISTRIBUTION_COLOR_GREEN2]),
                                            ),
                                            array(
                                                'label' => $effortDistribution[DISTRIBUTION_COLOR_YELLOW1],
                                                'backgroundColor' => DISTRIBUTION_COLOR_YELLOW1,
                                                'data' => array($effortDistribution[DISTRIBUTION_COLOR_YELLOW1]),
                                            ),
                                            array(
                                                'label' => $effortDistribution[DISTRIBUTION_COLOR_YELLOW2],
                                                'backgroundColor' => DISTRIBUTION_COLOR_YELLOW2,
                                                'data' => array($effortDistribution[DISTRIBUTION_COLOR_YELLOW2]),
                                            ),
                                            array(
                                                'label' => $effortDistribution[DISTRIBUTION_COLOR_ORANGE1],
                                                'backgroundColor' => DISTRIBUTION_COLOR_ORANGE1,
                                                'data' => array($effortDistribution[DISTRIBUTION_COLOR_ORANGE1]),
                                            ),
                                            array(
                                                'label' => $effortDistribution[DISTRIBUTION_COLOR_ORANGE2],
                                                'backgroundColor' => DISTRIBUTION_COLOR_ORANGE2,
                                                'data' => array($effortDistribution[DISTRIBUTION_COLOR_ORANGE2]),
                                            ),
                                            array(
                                                'label' => $effortDistribution[DISTRIBUTION_COLOR_RED1],
                                                'backgroundColor' => DISTRIBUTION_COLOR_RED1,
                                                'data' => array($effortDistribution[DISTRIBUTION_COLOR_RED1]),
                                            ),
                                            array(
                                                'label' => $effortDistribution[DISTRIBUTION_COLOR_RED2],
                                                'backgroundColor' => DISTRIBUTION_COLOR_RED2,
                                                'data' => array($effortDistribution[DISTRIBUTION_COLOR_RED2]),
                                            ),

                                        ),
                                        'strain_distribution' => array(
                                            array(
                                                'label' => $strainDistribution[DISTRIBUTION_COLOR_BLUE1],
                                                'backgroundColor' => DISTRIBUTION_COLOR_BLUE1,
                                                'data' => array($strainDistribution[DISTRIBUTION_COLOR_BLUE1]),
                                            ),
                                            array(
                                                'label' => $strainDistribution[DISTRIBUTION_COLOR_BLUE2],
                                                'backgroundColor' => DISTRIBUTION_COLOR_BLUE2,
                                                'data' => array($strainDistribution[DISTRIBUTION_COLOR_BLUE2]),
                                            ),
                                            array(
                                                'label' => $strainDistribution[DISTRIBUTION_COLOR_GREEN1],
                                                'backgroundColor' => DISTRIBUTION_COLOR_GREEN1,
                                                'data' => array($strainDistribution[DISTRIBUTION_COLOR_GREEN1]),
                                            ),
                                            array(
                                                'label' => $strainDistribution[DISTRIBUTION_COLOR_GREEN2],
                                                'backgroundColor' => DISTRIBUTION_COLOR_GREEN2,
                                                'data' => array($strainDistribution[DISTRIBUTION_COLOR_GREEN2]),
                                            ),
                                            array(
                                                'label' => $strainDistribution[DISTRIBUTION_COLOR_YELLOW1],
                                                'backgroundColor' => DISTRIBUTION_COLOR_YELLOW1,
                                                'data' => array($strainDistribution[DISTRIBUTION_COLOR_YELLOW1]),
                                            ),
                                            array(
                                                'label' => $strainDistribution[DISTRIBUTION_COLOR_YELLOW2],
                                                'backgroundColor' => DISTRIBUTION_COLOR_YELLOW2,
                                                'data' => array($strainDistribution[DISTRIBUTION_COLOR_YELLOW2]),
                                            ),
                                            array(
                                                'label' => $strainDistribution[DISTRIBUTION_COLOR_ORANGE1],
                                                'backgroundColor' => DISTRIBUTION_COLOR_ORANGE1,
                                                'data' => array($strainDistribution[DISTRIBUTION_COLOR_ORANGE1]),
                                            ),
                                            array(
                                                'label' => $strainDistribution[DISTRIBUTION_COLOR_ORANGE2],
                                                'backgroundColor' => DISTRIBUTION_COLOR_ORANGE2,
                                                'data' => array($strainDistribution[DISTRIBUTION_COLOR_ORANGE2]),
                                            ),
                                            array(
                                                'label' => $strainDistribution[DISTRIBUTION_COLOR_RED1],
                                                'backgroundColor' => DISTRIBUTION_COLOR_RED1,
                                                'data' => array($strainDistribution[DISTRIBUTION_COLOR_RED1]),
                                            ),
                                            array(
                                                'label' => $strainDistribution[DISTRIBUTION_COLOR_RED2],
                                                'backgroundColor' => DISTRIBUTION_COLOR_RED2,
                                                'data' => array($strainDistribution[DISTRIBUTION_COLOR_RED2]),
                                            ),

                                        ),
                                        ),
                        'last_week' => array(
                            'start_date' => $last_week_start_date,
                            'end_date' => $start_date,
                            'training_load' => (int)$last_week_training_load
                        ),
                        ),
                    'message' => 'Stats loaded',
                    'errors' => null,
                ), RESPONSE_CODE_SUCCESS_OK
            );
        }
        else
        {
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
