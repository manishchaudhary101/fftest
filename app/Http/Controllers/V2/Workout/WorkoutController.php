<?php
/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 24-05-2019
 * Time: 10:50
 */

namespace App\Http\Controllers\V2\Workout;

use App\Models\FB_Insight;
use App\Models\FB_User;
use App\Models\FB_Workout;
use App\Models\FE_DerivedData;
use \App\Models\FB_Community_post;
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
                    //generated values for backward compatibility
                    $startTime = clone $startTimeLocal;
                    $endTime = clone $endTimeUTC;
                    $endTime->setTimezone($startTime->getTimezone());


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
                                    $workout->time_zone_utc_offset = $startTimeLocal->getOffset();

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

//                                /removed in V2
//                                $workout->biostrip_macid = $request->biostrip_macid;
//                                $workout->firmware_version = $request->firmware_version;

                                if(isset($WKData['biostrip_macid']))
                                    $workout->biostrip_macid = $WKData['biostrip_macid'];
                                if(isset($WKData['firmware_version']))
                                    $workout->firmware_version = $WKData['firmware_version'];

                                if(isset($WKData['effort_alert_setpoint']))
                                    $workout->effort_alert_setpoint = $WKData['effort_alert_setpoint'];
                                if(isset($WKData['strain_alert_setpoint']))
                                    $workout->strain_alert_setpoint = $WKData['strain_alert_setpoint'];


                                $workout->save();
                                $totalWorkoutsAdded++;
                                $createdWorkoutIds[] = array(
                                    'startTime_UTC' => $workout->start_time_utc->format(DEFAULT_DATE_INPUT_FORMAT),
                                    'workoutId' => $workout->id,
                                    'biostrip_macid' => $workout->biostrip_macid,
                                );

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


                            }
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editWorkout(Request $request)
    {


        $validation = Validator::make($request->all(), [
//            'biostrip_macid' => 'required',
//            'firmware_version' => 'required',
            'workoutData' => 'required',
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

            $realWorkoutsDataArray = json_decode($request->input('workoutData'), true);
            $totalWorkoutsUpdated = 0;
            $updatedWorkoutIds = array();
            if (!empty($currentUser)) {

                foreach ($realWorkoutsDataArray as $WKData) {

                    $workout = null;

                    if(!empty($WKData['workoutID']) )
                    {

                        /** @var \App\Models\FB_Workout $workout */
                        $workout = FB_Workout::where('id', '=', $WKData['workoutID'])
                            ->where('created_by', '=', $currentUser->id)
                            ->where('status_enum', '=', ENUM_STATUS_ACTIVE)
                            ->first();

                        //Edit workout that was found
                        if (!empty($workout)) {


                                $workout->title = $WKData['title'];

                                if (isset($WKData['activity_type']))
                                    $workout->activity_type = $WKData['activity_type'];

                                $workout->modified_by = $currentUser->id;
                                $workout->updated_at = new \DateTime();
                                $workout->is_synced_with_app = false;

                                $workout->updated_source = $request->input('source',0);
                                $workout->update();

                                $currentUser->notify(new WorkoutEdited($workout));
                                Log::debug('---notified---');
                                $totalWorkoutsUpdated++;
                                array_push($updatedWorkoutIds,$workout->id);


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
                        'data' => $updatedWorkoutIds,
                        'message' => 'Workouts Updated:' . $totalWorkoutsUpdated,
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteWorkout(Request $request)
    {


        $validation = Validator::make($request->all(), [
            'biostrip_macid' => 'required',
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

            $totalWorkoutsDeleted = 0;
            $deletedWorkoutIds = array();
            if (!empty($currentUser)) {

                foreach ($realWorkoutsDataArray as $WKData) {

                    //default values
                    $startTimeUTC = null;
                    $startTime = null;

                    if(isset($WKData['startTime_UTC']))
                        $startTimeUTC = \DateTime::createFromFormat(DEFAULT_DATE_INPUT_FORMAT, $WKData['startTime_UTC']);

                    if(isset($WKData['startTime']))
                        $startTime = \DateTime::createFromFormat(DEFAULT_DATE_INPUT_FORMAT, $WKData['startTime']);


                    $workout = null;
                    if(!empty($startTimeUTC) )
                    {

                        $workout = FB_Workout::where('biostrip_macid', '=', $request->biostrip_macid)
                            ->where('start_time_utc', '=', $startTimeUTC)
                            ->where('created_by', '=', $currentUser->id)
                            ->where('status_enum', '=', ENUM_STATUS_ACTIVE)
                            ->first();

                        //Edit workout that was found
                        if (!empty($workout)) {

                            array_push($deletedWorkoutIds,$workout->id);
                            $workout->delete();
                            $totalWorkoutsDeleted++;

                        }


                    }
                    else if(!empty($startTime)) //for backward compatibility
                    {
                        $workout = FB_Workout::where('biostrip_macid', '=', $request->biostrip_macid)
                            ->where('start_time', '=', $startTime)
                            ->where('created_by', '=', $currentUser->id)
                            ->where('status_enum', '=', ENUM_STATUS_ACTIVE)
                            ->first();

                        //Edit workout that was found
                        if (!empty($workout)) {
                            array_push($deletedWorkoutIds,$workout->id);
                            $workout->delete();
                            $totalWorkoutsDeleted++;


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


    public function viewWorkout(Request $request)
    {
        // prakhar - add validation of input fields?
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
                    'time_zone_utc_offset','total_distance','avg_pace','activity_type')
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
                if(intval($request->input('showbinsync')) == 1)
                {
                    $workoutQuery->addSelect('has_bin_sync');
                }
                if ($request->filled('deriveddata')) {
                    if(intval($request->input('deriveddata')) == 1)
                        $workoutQuery->with(['hasDerivedData']);
                }else
                {
                    $workoutQuery->with(['hasDerivedData']);
                }

                if ($request->filled('id')) {

                    $workoutQuery->where('id',$request->id);
                }

                if ($request->filled('title')) {
                    $workoutQuery->where('title', 'like', '%' . $request->title . '%');
                }
                if ($request->filled('id') || !empty('created_by')) {

                    $workoutQuery->addSelect('arr_normal');
                    $workoutQuery->addSelect('arr_afib');
                    $workoutQuery->addSelect('arr_others');
                    $workoutQuery->addSelect('arr_noise');
                    $workoutQuery->addSelect('arr_details');
                }

                if ($request->filled('start_time')) {

                    $startTime = \DateTime::createFromFormat(DEFAULT_DATE_INPUT_FORMAT, $request->start_time);
                    if(!empty($startTime))
                    {
                        $startTime->setTime(0,0,0,1);
                        $workoutQuery->where('start_time_utc', '>=', $startTime);
                    }
                }

                if ($request->filled('end_time')) {

                    $endTime = \DateTime::createFromFormat(DEFAULT_DATE_INPUT_FORMAT, $request->end_time);
                    if(!empty($endTime))
                    {
                        $endTime->setTime(23,59,59);
                        $workoutQuery->where('end_time_utc', '>=', $endTime);
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
            $last_2_week_start_date = null;
            $last_3_week_start_date = null;
            $last_4_week_start_date = null;
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
                $last_2_week_start_date = clone $start_date;
                $last_2_week_start_date->modify('14 days ago');
                $last_3_week_start_date = clone $start_date;
                $last_3_week_start_date->modify('21 days ago');
                $last_4_week_start_date = clone $start_date;
                $last_4_week_start_date->modify('28 days ago');
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

            $last_2_week_training_load = FB_Workout::selectRaw('sum(training_load) as weekly_training_load')
                ->where('created_by',$currentUser->id)
                ->whereBetween('start_time',[$last_2_week_start_date,$last_week_start_date]) //local
                ->get()->pluck('weekly_training_load')->first();

            $last_3_week_training_load = FB_Workout::selectRaw('sum(training_load) as weekly_training_load')
                ->where('created_by',$currentUser->id)
                ->whereBetween('start_time',[$last_3_week_start_date,$last_2_week_start_date]) //local
                ->get()->pluck('weekly_training_load')->first();

            $last_4_week_training_load = FB_Workout::selectRaw('sum(training_load) as weekly_training_load')
                ->where('created_by',$currentUser->id)
                ->whereBetween('start_time',[$last_4_week_start_date,$last_3_week_start_date]) //local
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

            $TTL1 = max((int)$last_week_training_load, DEFAULT_TRAINING_GOAL );
            $TTL2 = max((int)$last_2_week_training_load, DEFAULT_TRAINING_GOAL );
            $TTL3 = max((int)$last_3_week_training_load, DEFAULT_TRAINING_GOAL );
            $TTL4 = max((int)$last_4_week_training_load, DEFAULT_TRAINING_GOAL );

            $TTL = array_sum([$TTL1, $TTL2, $TTL3, $TTL4]) / 4;
            if($TTL > DEFAULT_TRAINING_GOAL)
            {
            $TTL *= 1.1;
            }
            $TTL = round($TTL);
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

    /****
     * Added on 14th Aug 2020, Carl
     * https://fourthfrontier.atlassian.net/jira/software/projects/MD/boards/36?selectedIssue=MD-1169
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendUpdatedWorkouts(Request $request)
{
$validation = Validator::make($request->all(), [
    'workoutids' => 'required',
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


    if (!empty($currentUser)) {
        $syncedWorkoutIds = json_decode($request->input('workoutids',null));

        if(!empty($syncedWorkoutIds) && is_array($syncedWorkoutIds))
        {
            $result = \App\Models\FB_Workout::withTrashed()->whereIn('id',$syncedWorkoutIds)
                ->where('created_by',$currentUser->id)
                ->update(['is_synced_with_app' => true,'last_synced_timestamp' => new \DateTime()]);

            return response()->json(
                array(
                    'status' => true,
                    'code' => RESPONSE_CODE_SUCCESS_OK,
                    'data' => $result,
                    'message' => 'Sync Complete.',
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
                    'message' => 'Invalid workout Id format.',
                    'errors' => null,
                ), RESPONSE_CODE_ERROR_BAD
            );
        }
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
    public function getUpdatedWorkouts(Request $request)
    {
        $currentUser = FB_User::where('id', $request->user_id)
                              ->where('status_enum', ENUM_STATUS_ACTIVE)
                              ->first();
        
        
        if (!empty($currentUser)) {
            $workoutQuery = FB_Workout::withTrashed()
                                      ->select('id','title','activity_type','deleted_at')
                                      ->where('updated_source', DATASYNC_SOURCE_WEB)
                                      ->where('is_synced_with_app', false)
                                      ->where('created_by', $currentUser->id)
                                      ->where('status_enum', ENUM_STATUS_ACTIVE);
            
            $returnarray = array();
            foreach($workoutQuery->get() as $workout)
            {
                if($workout->trashed())
                {
                    $returnarray[] = array(
                        'workoutID' => $workout->id,
                        'action' => DATASYNC_ACTION_DELETE,
                        'newTitle' => null,
                        'newActivityType' => null,
                    );
                }
                else
                {
                    $returnarray[] = array(
                        'workoutID' => $workout->id,
                        'action' => DATASYNC_ACTION_EDIT,
                        'newTitle' => $workout->title,
                        'newActivityType' => $workout->activity_type,
                    );
                }
                
            }
            
            if(!empty($returnarray))
            {
                return response()->json(
                    array(
                        'status' => true,
                        'code' => RESPONSE_CODE_SUCCESS_OK,
                        'data' => $returnarray,
                        'message' => 'Updated Workouts',
                        'errors' => null,
                    ), RESPONSE_CODE_SUCCESS_OK
                );
            }
            else
            {
                return response()->json(
                    array(
                        'status' => false,
                        'code' => RESPONSE_CODE_SUCCESS_OK,
                        'data' => null,
                        'message' => 'No workouts found',
                        'errors' => null,
                    ), RESPONSE_CODE_SUCCESS_OK
                );
            }
            
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
    public function getHomeStats(Request $request)
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
            $last_2_week_start_date = null;
            $last_3_week_start_date = null;
            $last_4_week_start_date = null;
            if($request->filled('user_timezone_offset') && is_numeric($request->input('user_timezone_offset')))
            {

                $offsetInSeconds = $request->input('user_timezone_offset');
                // if($offsetInMin > 0)
                // {   $sign= '+';
                //     $offsetInMin = $sign.$offsetInMin;
                // }
                $offsetInMin = intval($offsetInSeconds) / 60;
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
                $last_2_week_start_date = clone $start_date;
                $last_2_week_start_date->modify('14 days ago');
                $last_3_week_start_date = clone $start_date;
                $last_3_week_start_date->modify('21 days ago');
                $last_4_week_start_date = clone $start_date;
                $last_4_week_start_date->modify('28 days ago');
                $last_5_week_start_date = clone $start_date;
                $last_5_week_start_date->modify('30 days ago');
            }
            $end_date = new \DateTime('tomorrow');

            //all time stats
           $workoutStats = FB_Workout::selectRaw('count(distinct(start_time_utc)) as activities, ROUND(sum(distinct(time_to_sec(timediff(end_time,start_time ))) / 60)) as active_minutes, ROUND(sum(distinct(training_load))) as training_load')
                ->where('created_by',$currentUser->id)
                ->whereBetween('start_time',[$last_5_week_start_date,$end_date]) //local 
                ->whereNull('deleted_at')
                ->get()->first();
            //this week stats
//            $start_date = new \DateTime('last sunday 11:59:59PM'); //UTC
            
            $recommendation = FB_Insight::select('id','created_at','created_by','FC_Insight_category_id','title','content')
            ->where('created_by',$currentUser->id)
            ->where('FC_Insight_category_id', 10)
            ->orderBy('created_at', 'desc')
            ->get()->first();

            $analysis = FB_Workout::selectRaw('FB_Insight.id,DATE(FB_Insight.created_at) AS created_at,DATE(FB_Insight.updated_at) AS updated_id,FB_Insight.created_by,FB_Insight.FC_Insight_category_id,FB_Insight.title,FB_Insight.content,FB_Workout.id')
                ->join('FR_Insight_has_workouts','FB_Workout.id',"=",'FR_Insight_has_workouts.FB_Workout_id')
                ->join('FB_Insight','FR_Insight_has_workouts.FB_Insight_id',"=",'FB_Insight.id')
                ->where('FB_Workout.created_by',$currentUser->id)
                ->where('FB_Workout.has_bin_sync',1)
                ->where('FB_Insight.FC_Insight_category_id',0)
                ->orderBy('FB_Workout.start_time_utc', 'desc')
                ->get()->first();
            
            $blog = FB_Community_post::selectRaw('id,created_at,updated_at,deleted_at,title,published_on,content,url,views,author_id,thumbnail_uri')
                  ->where('deleted_at', null)
                  ->orderBy('updated_at', 'desc')
                  ->limit(3)
                  ->get();

            $training_load = FB_Workout::selectRaw('sum(training_load) as weekly_training_load')
                ->where('created_by',$currentUser->id)
                ->whereBetween('start_time',[$start_date,$end_date]) //local
                ->whereNull('deleted_at')
                ->get()->pluck('weekly_training_load')->first();

            $last_week_training_load = FB_Workout::selectRaw('sum(training_load) as weekly_training_load')
                ->where('created_by',$currentUser->id)
                ->whereBetween('start_time',[$last_week_start_date,$start_date]) //local
                ->whereNull('deleted_at')
                ->get()->pluck('weekly_training_load')->first();

            $last_2_week_training_load = FB_Workout::selectRaw('sum(training_load) as weekly_training_load')
                ->where('created_by',$currentUser->id)
                ->whereBetween('start_time',[$last_2_week_start_date,$last_week_start_date]) //local
                ->whereNull('deleted_at')
                ->get()->pluck('weekly_training_load')->first();

            $last_3_week_training_load = FB_Workout::selectRaw('sum(training_load) as weekly_training_load')
                ->where('created_by',$currentUser->id)
                ->whereBetween('start_time',[$last_3_week_start_date,$last_2_week_start_date]) //local
                ->whereNull('deleted_at')
                ->get()->pluck('weekly_training_load')->first();

            $last_4_week_training_load = FB_Workout::selectRaw('sum(training_load) as weekly_training_load')
                ->where('created_by',$currentUser->id)
                ->whereBetween('start_time',[$last_4_week_start_date,$last_3_week_start_date]) //local
                ->whereNull('deleted_at')
                ->get()->pluck('weekly_training_load')->first();

            $TTL1 = max((int)$last_week_training_load, DEFAULT_TRAINING_GOAL );
            $TTL2 = max((int)$last_2_week_training_load, DEFAULT_TRAINING_GOAL );
            $TTL3 = max((int)$last_3_week_training_load, DEFAULT_TRAINING_GOAL );
            $TTL4 = max((int)$last_4_week_training_load, DEFAULT_TRAINING_GOAL );

            $TTL = array_sum([$TTL1, $TTL2, $TTL3, $TTL4]) / 4;
            if($TTL > DEFAULT_TRAINING_GOAL)
            {
            $TTL *= 1.1;
            }
            $TTL = round($TTL);
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
                        'last_30_days' =>$workoutStats,
                        'recommendation' => $recommendation,
                        'analysis' => $analysis,
                        'this_week' =>array(
                                        'training_load' => $WTL,
                                        'training_goal' => $TTL,
                                        'dialogue' => $dialogue,
                                        ),
                        'blog'=> $blog,               
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
    public function getTraningloadStats(Request $request)
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
            $last_2_week_start_date = null;
            $last_3_week_start_date = null;
            $last_4_week_start_date = null;
            if($request->filled('user_timezone_offset') && is_numeric($request->input('user_timezone_offset')))
            {

                $offsetInSeconds = $request->input('user_timezone_offset');
                // if($offsetInMin > 0)
                // {   $sign= '+';
                //     $offsetInMin = $sign.$offsetInMin;
                // }
                $offsetInMin = intval($offsetInSeconds) / 60;
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
                $last_2_week_start_date = clone $start_date;
                $last_2_week_start_date->modify('14 days ago');
                $last_3_week_start_date = clone $start_date;
                $last_3_week_start_date->modify('21 days ago');
                $last_4_week_start_date = clone $start_date;
                $last_4_week_start_date->modify('28 days ago');
                $last_5_week_start_date = clone $start_date;
                $last_5_week_start_date->modify('30 days ago');
            }
            $end_date = new \DateTime('tomorrow');

            //all time stats
            $workoutStats = FB_Workout::selectRaw('count(distinct(start_time_utc)) as activities, ROUND(sum(distinct(time_to_sec(timediff(end_time,start_time ))) / 60)) as active_minutes, ROUND(sum(distinct(training_load))) as training_load')
            ->where('created_by',$currentUser->id)
                ->whereBetween('start_time',[$last_5_week_start_date,$end_date]) //local 
                ->whereNull('deleted_at')
                ->get()->first();
            //this week stats
//            $start_date = new \DateTime('last sunday 11:59:59PM'); //UTC


                /* day ttl */
                $sDate = $start_date =  new \DateTime('now', new \DateTimeZone($sign.$offsetTimeObj));
                $last2WeekStartDate = clone $sDate;
                $last2WeekStartDate->modify('13 days ago');
                $tdtl = FB_Workout::selectRaw('DATE(start_time_local) AS date, SUM(training_load) AS points')
                ->where('created_by',$currentUser->id)
                ->where('start_time_local','>=',$last2WeekStartDate) //local
                ->whereNull('deleted_at')
                ->groupBy('date')
                ->get();

                $tmptdtl = array();
                for($i=0;$i<14;$i++){
                    $tmpDate = clone $sDate;
                    $tmpDate->modify($i.' days ago');
                    $rowDate = $tmpDate->format('Y-m-d');
                    $data = 0;
                    foreach($tdtl as $row) {
                        if($row['date'] == $rowDate) {
                            $data = $row;
                            break;
                        }
                    }
                    if ($data) {
                        array_push($tmptdtl, $data);
                    } else {
                        array_push($tmptdtl, ['date' => $rowDate,'points' => "0"]);
                    }
                }
                $tdtl = $tmptdtl;
                $tmptdate = array();
                $totalTrainingLoad = 0;
                $tdtlStartDate = $last2WeekStartDate->format('Y-m-d');
                $tdtlEndDate = clone $sDate;
                $tdtlEndDate = $tdtlEndDate->format('Y-m-d');
                foreach ($tdtl as $key => $row) {
                    $tmptdate[$key] = $row['date'];
                    $totalTrainingLoad += $row['points'];
                }
                $tmpArray = $tdtl;
                array_multisort($tmptdate, SORT_DESC, $tmpArray);
                $tdtl = array();
                $tdtl['total_points'] = $totalTrainingLoad;
                $tdtl['start_date'] = $tdtlStartDate;
                $tdtl['end_date'] = $tdtlEndDate;
                $tdtl['data_points'] = $tmpArray;
                

                /* week ttl */
                $weekStartDate = clone $sDate;
                $weekStartDate->modify("monday this week");
                $weekStartDate->modify('11 weeks ago');
                $twtl = FB_Workout::selectRaw('WEEK(start_time_local, 3) AS week_number_epoch, sum(training_load) as points')
                ->where('created_by',$currentUser->id)
                ->where('start_time_local','>=',$weekStartDate) //local
                ->orderBy('start_time_local')
                ->whereNull('deleted_at')
                ->groupBy('week_number_epoch')
                ->get();

                $tmptwtl = array();
                for($i=0;$i<12;$i++){
                    $tmpDate = clone $sDate;
                    $tmpDate->modify("monday this week");
                    $tmpDate->modify($i.' weeks ago');
                    $week_number = intval($tmpDate->format('W'));
                    $data = null;
                    foreach($twtl as $row) {
                        if($row['week_number_epoch'] == $week_number) {
                            $data = $row;
                            break;
                        }
                    }
                    if ($data) {
                        array_push($tmptwtl, $data);
                    } else {
                        array_push($tmptwtl, ['week_number_epoch' => $week_number,'points' => "0"]);
                    }
                }
                $twtl = $tmptwtl;
                $tmptwdate = array();
                $totalwTrainingLoad = 0;
                $tdtlwStartDate = $weekStartDate->format('Y-m-d');
                $tdtlwEndDate = clone $sDate;
                $tdtlwEndDate = $tdtlwEndDate->format('Y-m-d');
                foreach ($twtl as $key => $row) {
                    $tmptwdate[$key] = $row['week_number_epoch'];
                    $totalwTrainingLoad += $row['points'];
                }
                $tmpwArray = $twtl;
                $twtl = array();
                $twtl['total_points'] = $totalwTrainingLoad;
                $twtl['start_date'] = $tdtlwStartDate;
                $twtl['end_date'] = $tdtlwEndDate;
                $twtl['data_points'] = $tmpwArray;

                /* month ttl */
                $monthStartDate = clone $sDate;
                $monthStartDate->modify('first day of this month');
                $monthStartDate->modify('11 months ago');
                $tmtl = FB_Workout::selectRaw('MONTHNAME(start_time_local) AS month, sum(training_load) as points')
                ->where('created_by',$currentUser->id)
                ->where('start_time_local','>=',$monthStartDate) //local
                ->orderBy('start_time_local')
                ->whereNull('deleted_at')
                ->groupBy('month')
                ->get();

                $tmptmtl = array();

                for($i=0;$i<12;$i++){
                    $tmpDate = clone $sDate;
                    $tmpDate->modify('first day of this month');
                    $tmpDate->modify($i.' months ago');
                    $month = $tmpDate->format('F');
                    $data = null;
                    foreach($tmtl as $row) {
                        if($row['month'] == $month) {
                            $data = $row;
                            break;
                        }
                    }
                    if ($data) {
                        array_push($tmptmtl, $data);
                    } else {
                        array_push($tmptmtl,['month' => $month,'points' => "0"]);
                    }
                }

                $tmtl = $tmptmtl;
                $tmptmdate = array();
                $totalmTrainingLoad = 0;
                $tmtlmStartDate = $monthStartDate->format('Y-m-d');
                $tmtlmEndDate = clone $sDate;
                $tmtlmEndDate = $tmtlmEndDate->format('Y-m-d');
                foreach ($tmtl as $key => $row) {
                    $tmptmdate[$key] = $row['month'];
                    $totalmTrainingLoad += $row['points'];
                }
                $tmpmArray = $tmtl;
                $tmtl = array();
                $tmtl['total_points'] = $totalmTrainingLoad;
                $tmtl['start_date'] = $tmtlmStartDate;
                $tmtl['end_date'] = $tmtlmEndDate;
                $tmtl['data_points'] = $tmpmArray;
          
                return response()->json(
                    array(
                        'status' => true,
                        'code' => RESPONSE_CODE_SUCCESS_OK,
                        'data' => array(
                            'graph_data' => array(
                                'days_training_load' => $tdtl,
                                'weeks_training_load' => $twtl,
                                'months_training_load' => $tmtl,
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