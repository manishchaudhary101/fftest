<?php
/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 27-06-2019
 * Time: 10:50
 */

namespace App\Http\Controllers;


use App\Models\FB_User;
use App\Models\FB_Workout;
use App\Models\FC_UserOldData;
use App\Models\FE_DerivedData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Cron
{

    public function mapOldUserIdToNewUserId()
    {
        $getAllUserQuery = FB_User::select('id', 'email')
            ->orderBy('email', 'ASC')->get();

//        return json_encode($getAllUserQuery);

        foreach ($getAllUserQuery as $user) {
            $oldUserData = FC_UserOldData::where('user_email_old', $user->email)->first();

            if (!empty($oldUserData)) {
                $oldUserData->user_id_new = $user->id;
                $oldUserData->save();
            }

        }
        return response()->json('data mapped successfully');
    }

    public  function calculateTrainingStrain()
    {
//
//        //temp
//        $derivedDataWithBreathingRate = FE_DerivedData::where('dtype',DERIVED_DTYPE_BREATHRATE)->whereNull('heartpoint')->take(5000)->get();
//        foreach($derivedDataWithBreathingRate as $deriveData)
//        {
//            $heartpoint = 0.0;
//                                            if ($deriveData->dvalue >= 0 && $deriveData->dvalue < 15) {
//                                                $heartpoint += 0.0;
//                                            } else if ($deriveData->dvalue >= 15 && $deriveData->dvalue < 20) {
//                                                $heartpoint += (1.0);
//                                            } else if ($deriveData->dvalue >= 20  && $deriveData->dvalue < 25) {
//                                                $heartpoint += (2.0);
//                                            } else if ($deriveData->dvalue >= 25  && $deriveData->dvalue < 30) {
//                                                $heartpoint += (3.0);
//                                            } else if ($deriveData->dvalue >= 30  && $deriveData->dvalue < 35) {
//                                                $heartpoint += (4.0);
//                                            } else if ($deriveData->dvalue >= 35 && $deriveData->dvalue < 40) {
//                                                $heartpoint += (5.0);
//                                            } else if ($deriveData->dvalue >= 40 && $deriveData->dvalue < 45) {
//                                                $heartpoint += (6.0);
//                                            } else if ($deriveData->dvalue >= 45 && $deriveData->dvalue < 50) {
//                                                $heartpoint += (7.0);
//                                            } else if ($deriveData->dvalue >= 50 && $deriveData->dvalue < 55) {
//                                                $heartpoint += (8.0);
//                                            } else if ($deriveData->dvalue >= 55 && $deriveData->dvalue < 60) {
//                                                $heartpoint += (9.0);
//                                            } else if ($deriveData->dvalue >= 60) {
//                                                $heartpoint += (10.0);
//                                            }
//
//                                            $deriveData->heartpoint = $heartpoint;
//
//            $deriveData->heartpoint = $heartpoint;
//            $deriveData->save();
////            var_dump($deriveData->id);
//        }

//die();
        //get all workout ids where max_strain or training loads are null
        $timeEndToCheck = new \DateTime('-2 minutes');
        $timeStartToCheck = new \DateTime('-2 hours');
        $workoutIds = FB_Workout::select('id')->whereNull('max_strain')->orWhereNull('training_load')->orderBy('created_at','DESC')->take(3000)->pluck('id')->toArray();

        Log::debug('Workouts found with max_strain or training_load as null '.count($workoutIds));
//        Log::debug(json_encode($workoutIds));

        //for max strain
        $deriveddatastats = \App\Models\FE_DerivedData::selectRaw('max(dvalue) as maxstrain, FB_Workout_id')
            ->whereIn('FB_Workout_id',$workoutIds)
            ->where('dtype',DERIVED_DTYPE_STRAIN)
            ->groupBy('FB_Workout_id')
            ->get();

        if($deriveddatastats->count() > 0)
        {
            $fbWorkout = new FB_Workout();
            $updateQueryMaxStrain = "UPDATE ".$fbWorkout->getTable()." SET max_strain = CASE ";
            foreach($deriveddatastats->toArray() as $mdata)
            {
                if(floatval($mdata['maxstrain']) > 0 && floatval($mdata['maxstrain']) < 65500) //mysql limit
                    $updateQueryMaxStrain.= " WHEN id=".$mdata['FB_Workout_id'].' THEN '.$mdata['maxstrain'];
                else
                    $updateQueryMaxStrain.= " WHEN id=".$mdata['FB_Workout_id'].' THEN 0';
            }
            $updateQueryMaxStrain.=' END ';
            $updateQueryMaxStrain.=' WHERE id in ('.implode(',',$workoutIds).');';

            Log::debug('Calculating max strain using  '.count($deriveddatastats).' workout records');
//            Log::debug($updateQueryMaxStrain);
//            var_dump($updateQueryMaxStrain);
            $result = DB::select($updateQueryMaxStrain);
//            var_dump($result);
            unset($deriveddatastats);
        }


        //for training load

        $deriveddatastats = \App\Models\FE_DerivedData::selectRaw('sum(heartpoint) as training_load, FB_Workout_id')
            ->whereIn('FB_Workout_id',$workoutIds)
            ->where('dtype',DERIVED_DTYPE_BREATHRATE)
            ->groupBy('FB_Workout_id')
            ->get();

//        var_dump($deriveddatastats);
        if($deriveddatastats->count() > 0) {

            $updateQueryTrainingLoad = "UPDATE " . $fbWorkout->getTable() . " SET training_load = CASE ";
            foreach ($deriveddatastats->toArray() as $mdata) {
                if (floatval($mdata['training_load']) > 0 && floatval($mdata['training_load']) < 65500) //mysql limit
                    $updateQueryTrainingLoad .= " WHEN id=" . $mdata['FB_Workout_id'] . ' THEN ' . round($mdata['training_load']/3);
                else
                    $updateQueryTrainingLoad .= " WHEN id=" . $mdata['FB_Workout_id'] . ' THEN 0';
            }
            $updateQueryTrainingLoad .= ' END ';
            $updateQueryTrainingLoad .= ' WHERE id in (' . implode(',', $workoutIds) . ');';

            Log::debug('Calculating training_load using  '.count($deriveddatastats).' workout records');
//            Log::debug($updateQueryTrainingLoad);
//            var_dump($updateQueryTrainingLoad);
            $result = DB::select($updateQueryTrainingLoad);
//            var_dump($result);
        }



    }

    public function makePostThumbnails()
    {
        $postWithoutThumbnails = \App\Models\FB_Community_post::whereNull('thumbnail_uri')->take(5)->get();
        foreach($postWithoutThumbnails as $post)
        {
            if(empty($post)) //autocomplete
                $post = new \App\Models\FB_Community_post();

            $post->getMetaUrlData();
            sleep(1);
            if(!empty($post->thumbnail_uri))
            {
                if(empty($post->published_on))
                    $post->published_on = new \DateTime();
                $post->save();
            }

        }
    }

    public function generateWorkoutTime()
    {
        $fbWorkouts = \App\Models\FB_Workout::whereNull('start_time_local')->get();

        foreach($fbWorkouts as $fb)
        {
            if(empty($fb))
                $fb = new FB_Workout();

            echo '<br><b>'.$fb->id.' .'.$fb->title.'</b> '.$fb->start_time.' | '.$fb->end_time.' | '.$fb->time_zone.' | ';
            try {

                $tz = new \DateTimeZone( $fb->time_zone);

                $startTimeObj = \DateTime::createFromFormat('Y-m-d H:i:s',$fb->start_time,$tz);
                $startTimeUTC = clone $startTimeObj;
                $startTimeUTC->setTimezone(new \DateTimeZone('UTC'));
                echo ' | '.$startTimeUTC->format(DATE_ISO8601);

                $endTimeObj = \DateTime::createFromFormat('Y-m-d H:i:s',$fb->end_time,$tz);
                $endTimeUTC = clone $endTimeObj;
                $endTimeUTC->setTimezone(new \DateTimeZone('UTC'));
                echo ' | '.$endTimeUTC->format(DATE_ISO8601);

                $fb->start_time_local = $startTimeObj->format(DATE_ISO8601);
                $fb->start_time_utc = $startTimeUTC;
                $fb->end_time_utc = $endTimeUTC;
                $fb->save();

            }catch (\Exception $e)
            {
                echo ' invalid TZ';
            }
        }

    }
}
