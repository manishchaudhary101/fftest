<?php

namespace App\Http\Controllers\Admin\Workout;

use App\Models\FB_Insight;
use App\Models\FB_User;
use App\Models\FB_Workout;
use App\Models\FE_DerivedData;
use App\Notifications\V1\Workout\WorkoutEdited;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WorkoutController extends Controller
{
    public function viewUsersWorkout(FB_User $viewableUser, Request $request)
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

            /** @var FB_User $currentUser */
            $currentUser = FB_User::where('id', $request->user_id)
                ->where('status_enum', ENUM_STATUS_ACTIVE)
                ->first();

            if($currentUser->authorizedUsers()->where('id',$viewableUser->id)->count() > 0 || $currentUser->userlevel_enum == ENUM_USERLEVEL_ADMIN)
            {
                if (!empty($viewableUser)) {
                    $workoutQuery = FB_Workout::
                    select('id','created_at','created_by','status_enum','local_id','title','start_time','end_time',
                    'start_time_utc','end_time_utc','start_time_local','biostrip_macid','firmware_version','time_zone',
                    'time_zone_utc_offset','total_distance','avg_pace','activity_type',
                    'updated_source','last_synced_timestamp','is_synced_with_app',
                    'avg_heart_rate', 'phone_os_version', 'app_version', 'source_platform',
                    'avg_strain', 'avg_breathrate', 'avg_shock', 'avg_cadence', 'avg_qtc' ,'avg_qtc' ,'avg_hrv',
                    'double_buzz_param',  'single_buzz_param', 'double_buzz_limit' ,'single_buzz_limit' , 'double_buzz_val' ,  'single_buzz_val' , 'is_ecg_pdf_generated', 'phone_model', 'ecg_pdf_url'
                )
                        ->where('created_by', $viewableUser->id)
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
                        $workoutQuery->where(function ($subQuery) use ($request)
                        {
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
            else
            {
                return response()->json(
                    array(
                        'status' => false,
                        'code' => RESPONSE_CODE_ERROR_NOPERMISSION,
                        'data' => null,
                        'message'   => 'You are not authorized to view this user'
                    ),
                    RESPONSE_CODE_ERROR_NOPERMISSION);
            }


        }
    }

    public function getUsersWeeksStats(FB_User $viewableUser, Request $request)
    {

        if(!empty($viewableUser))
        {
            $offsetInMin = '';
            $offsetTimeObj = '';
            $start_date = new \DateTime();
            $last_week_start_date = null;
            $insightsCount = FB_Insight::where('created_by',$viewableUser->id)->count();

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
                                      ->where('created_by',$viewableUser->id)
                                      ->get()->first();
            //this week stats
//            $start_date = new \DateTime('last sunday 11:59:59PM'); //UTC
            $end_date = new \DateTime('tomorrow');


            $training_load = FB_Workout::selectRaw('sum(training_load) as weekly_training_load')
                                       ->where('created_by',$viewableUser->id)
                                       ->whereBetween('start_time',[$start_date,$end_date]) //local
                                       ->get()->pluck('weekly_training_load')->first();

            $last_week_training_load = FB_Workout::selectRaw('sum(training_load) as weekly_training_load')
                                                 ->where('created_by',$viewableUser->id)
                                                 ->whereBetween('start_time',[$last_week_start_date,$start_date]) //local
                                                 ->get()->pluck('weekly_training_load')->first();

            $workoutIdsInThisWeek = FB_Workout::select('id')
                                              ->where('created_by',$viewableUser->id)
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
    
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editWorkout(FB_User $viewableUser, Request $request)
    {
        /** @var FB_User $currentUser */
        $currentUser = FB_User::where('id', $request->user_id)
                              ->where('status_enum', ENUM_STATUS_ACTIVE)
                              ->first();
        
        if($currentUser->userlevel_enum == ENUM_USERLEVEL_DOCTOR && $currentUser->authorizedUsers->where('id',$viewableUser->id)->count() > 0)
        {
            $validation = Validator::make($request->all(), [
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
            }
            else {
        
        
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
                                                 ->where('created_by', '=', $viewableUser->id)
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
                            else{
                                return response()->json(
                                    array(
                                        'status' => false,
                                        'code' => RESPONSE_CODE_ERROR_NOPERMISSION,
                                        'data' => null,
                                        'message'   => 'You are not authorized to edit this workout '.$WKData['workoutID']
                                    ),
                                    RESPONSE_CODE_ERROR_NOPERMISSION);
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
        else
        {
            return response()->json(
                array(
                    'status' => false,
                    'code' => RESPONSE_CODE_ERROR_NOPERMISSION,
                    'data' => null,
                    'message'   => 'You are not authorized to edit this workout'
                ),
                RESPONSE_CODE_ERROR_NOPERMISSION);
        }
        
        
    }
    
}