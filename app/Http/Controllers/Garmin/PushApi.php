<?php

namespace App\Http\Controllers\Garmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class PushApi extends Controller
{

    public function receiveActivitiesSummaryPush(Request $request)
    {
//        $thisClassProperties = DB::getSchemaBuilder();
//        dd(get_class_methods($thisClassProperties));
        $thisClassProperties = DB::getSchemaBuilder()->getColumnListing('FB_GarminActivitySummary');

        echo 'Thanks!';
        Log::info($request->all());


    }

    public function receiveActivitiesDetailsPush(Request $request)
    {
        echo 'Here';
//        dd($request);
        Log::info('-----GARMIN ACTIVITIES DETAIL PUSH API--------');
        Log::info($request->all());
        if($request->has('activityDetails') )
        {
            $activityDetailsArr = $request->input('activityDetails');
            if(is_array($activityDetailsArr))
            {
                foreach($activityDetailsArr as $activityDetail)
                {
                    if(is_array($activityDetail))
                    {
                        $newGarminActivitySummary = null;
                        $mapped4fUser = null;
                        if(isset($activityDetail['summary']))
                        {
                            $newGarminActivitySummary = new \App\Models\FB_GarminActivitySummary();
                            $newGarminActivitySummary->summaryId = $activityDetail['summaryId'];
                            $newGarminActivitySummary->userAccessToken = $activityDetail['userAccessToken'];
                            $newGarminActivitySummary->userId = $activityDetail['userId'];
                            $newGarminActivitySummary->createFromactivitySummary($activityDetail['summary']);
                            $newGarminActivitySummary->save();
                            Log::info('Garmin Activity Summary ID: '.$newGarminActivitySummary->summaryId.' Recorded for user '.$activityDetail['userId']);

                            if(!empty($newGarminActivitySummary->userId ))
                            {
                                $mapped4fUser = \App\Models\FB_User::where('garmin_userId',$newGarminActivitySummary->userId)->first();
                            }

                        }else
                        {
                            Log::error('ActivityDetail didnt have a summary - '.($activityDetail));
                        }

                        if(isset($activityDetail['samples']))
                        {
                            foreach($activityDetail['samples'] as $sampleArr)
                            {
                                $newGarminActivityDetail = new \App\Models\FB_GarminActivityData();

                                if(!empty($newGarminActivitySummary) && isset($newGarminActivitySummary->id))
                                    $newGarminActivityDetail->FB_GarminActivitySummary_id =  $newGarminActivitySummary->id;
                                if(!empty($newGarminActivitySummary) && isset($newGarminActivitySummary->summaryId))
                                    $newGarminActivityDetail->summaryId =  $newGarminActivitySummary->summaryId;
                                //mapping the 4f user id to the garmin activity detail
                                if(!empty($mapped4fUser) && isset($newGarminActivitySummary->userId))
                                {
                                    $newGarminActivityDetail->FB_User_id = $mapped4fUser->id;
                                }
                                $newGarminActivityDetail->createFromActivitySample($sampleArr);
                                $newGarminActivityDetail->save();
                                Log::info('Garmin Activity Detail for Summary ID: '.$newGarminActivitySummary->summaryId.' Recorded for user '.$activityDetail['userId']);

                            }
                        }
                        else
                        {
                            Log::error('ActivityDetail didnt have samples - '.($activityDetail));
                        }
                    }
                }
            }else
            {
                Log::error('Activities was not an array - '.gettype($activityDetailsArr));
            }
        }
    }

    public function receiveDeregistration(Request $request)
    {
        echo 'Thanks!';
        Log::info('-----GARMIN DEREGISTRATION PUSH API--------');
        Log::info($request->all());

        if($request->has('deregistrations') )
        {
            $deRegistrations = $request->input('deregistrations');
            if(is_array($deRegistrations))
            {
                foreach($deRegistrations as $garminDeregistrationData)
                {
                    if(is_array($garminDeregistrationData))
                    {

                        if(isset($garminDeregistrationData['userId']))
                        {
                            $mapped4fUser = \App\Models\FB_User::where('garmin_userId',$garminDeregistrationData['userId'])->first();
                            if(!empty($mapped4fUser))
                            {
                                $mapped4fUser->garmin_revoked_at = new \DateTime();
                                $mapped4fUser->garmin_userAccessToken = null;
                                $mapped4fUser->save();
                            }else
                            {
                                Log::error('No 4f user mapped - '.($garminDeregistrationData));
                            }

                        }else
                        {
                            Log::error('No garmin user id - '.($garminDeregistrationData));
                        }


                    }
                }
            }else
            {
                Log::error('deregistrations was not an array - '.gettype($deRegistrations));
            }
        }
    }
}
