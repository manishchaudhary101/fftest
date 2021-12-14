<?php
/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 24-05-2019
 * Time: 10:50
 */

namespace App\Http\Controllers\V3\Workout;

use App\Models\FB_User;
use App\Models\FB_Workout;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;


class Awss3
{

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSignedUrl(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'filename' => 'required',
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
                //Create a S3Client
                $s3 = new S3Client([
                    'version' => 'latest',
                    'region' => env('AWS_DEFAULT_REGION','us-west-2'),
                    'credentials' => [
                        'key' => AWS_KEY,
                        'secret' => AWS_SECRET,
                    ],
                ]);

                $command = $s3->getCommand('PutObject',
                    [
                        'Bucket' => AWS_S3_BUCKET,
                        'Key' => AWS_S3_PREFIX_USERDATA . '/' . $currentUser->id . '/' . $request->filename . '.bin',
                    ]
                );

                try {
                    $req = $s3->createPresignedRequest($command, '+60 minutes');

                    return response()->json(
                        array(
                            'status' => true,
                            'code' => RESPONSE_CODE_SUCCESS_OK,
                            'data' => (string)$req->getUri(),
                            'message' => null,
                            'errors' => null,
                        ), RESPONSE_CODE_SUCCESS_OK
                    );
                } catch (S3Exception $e) {
                    return response()->json(
                        array(
                            'status' => false,
                            'code' => RESPONSE_CODE_ERROR_BAD,
                            'data' => null,
                            'message' => $e->getMessage(),
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

    public function getFile(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'workout_id' => 'required',
            'file_type' => 'required',
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
                    $utc_time = new \DateTime($workoutQuery->start_time, new \DateTimeZone($workoutQuery->time_zone));;
                    if ($workoutQuery->time_zone != 'UTC'){
                        $utc_time->setTimezone(new \DateTimeZone('UTC'));

                    }
                    $biostripmac_id = str_replace(':','',$workoutQuery->biostrip_macid);

                    $filename =  $utc_time->format('Ymd_His')."_".strtolower($biostripmac_id).'/'.$request->file_type.'.csv';

//                    $filename = '20190507_071550_f8f005a1705b_dd.csv';
                    $s3 = new S3Client([
                        'version' => 'latest',
                        'region' => env('AWS_DEFAULT_REGION','us-west-2'),
                        'credentials' => [
                            'key' => AWS_KEY,
                            'secret' => AWS_SECRET,
                        ],
                    ]);


                    try{
                        $result = $s3->getObject([
                            'Bucket'                     => env('AWS_BUCKET_WEBVIEW','4f-webview'),
                            'Key'                        => $currentUser->id.'/'.$filename,
//                            'ResponseContentType'        => 'text/plain',
//                            'ResponseContentLanguage'    => 'en-US',
                            'ResponseContentDisposition' => 'attachment; filename='.str_ireplace('/','_',$filename),
                            'ResponseCacheControl'       => 'No-cache',
                            'ResponseExpires'            => gmdate(DATE_RFC2822, time() + 3600),
                        ]);

                    }catch (\Exception $e){
                        return response('File not found '.$filename, 404);
                    }

//                    header('Content-Type:', '.csv');
//                    header('Content-Length: ' . strlen($result));
//                    header('Content-Disposition: attachment; filename="' . $filename . '"');
//                    header('Cache-Control: public, must-revalidate, max-age=0');
//                    header('Pragma: public');
//                    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
//                    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
//                    readfile($result);
//
                    return response($result['Body'], 200)->withHeaders([
                        'Content-Type'        => $result['ContentType'],
                        'Content-Length'      => $result['ContentLength'],
                        'Content-Disposition' => 'inline; filename="' . $filename . '"'
                    ]);

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
