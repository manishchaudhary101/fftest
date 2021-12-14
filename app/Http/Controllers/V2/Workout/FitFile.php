<?php
/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 24-05-2019
 * Time: 10:50
 */

namespace App\Http\Controllers\V2\Workout;

use App\Models\FB_User;
use App\Models\FB_Workout;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;


class FitFile
{


    public function getFile(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'workout_id' => 'required',
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
                $workoutQuery = FB_Workout::where('created_by', $currentUser->id)
                    ->where('status_enum', ENUM_STATUS_ACTIVE)
                    ->where('id', $request->workout_id)
                    ->first();

                if (!empty($workoutQuery)) {
                    $ret =  exec('cd ..; ./encode '.$workoutQuery->id.' '.$workoutQuery->id.'.fit',$output);
                    if(!empty($ret) || !empty($output))
                    {
                        return response()->download(storage_path($workoutQuery->id.'.fit'))->deleteFileAfterSend(true);
                    }
                    else
                    {
                        echo "Oops! This didnt go as planned. Try again later?";
                    }


                } else {
                    return response()->json(
                        array(
                            'status' => false,
                            'code' => RESPONSE_CODE_ERROR_BAD,
                            'data' => null,
                            'message' => 'Workout not found.',
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
                        'message' => 'User not found',
                        'errors' => null,
                    ), RESPONSE_CODE_ERROR_BAD
                );
            }
        }
    }


}
