<?php

/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 16-04-2018
 * Time: 18:03
 */
namespace App\Http\Controllers\Common;

use Aws\S3\S3Client;
use Illuminate\Support\Facades\Storage;
use \Illuminate\Support\Facades\Validator;
use \Illuminate\Http\Request;

class Appconfig
{

       public function checkFirmwareUpdate(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'version' => 'required | integer'
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
            //Create a S3Client
            $s3 = new S3Client([
                                   'version' => 'latest',
                                   'region' => 'us-west-2',
                                   'credentials' => [
                                       'key' => AWS_KEY,
                                       'secret' => AWS_SECRET,
                                   ],
                               ]);
							
   /*       
			//temp fix for mandatory version
            $MANDATORY_VERSION = "266";

            if($request->input('version') < $MANDATORY_VERSION)
                {
                    //send mandatory version

                    $command = $s3->getCommand('GetObject',
                                               [
                                                   'Bucket' => AWS_S3_BUCKET,
                                                   'Key' => 'firmware_mandatory/FrontierX_266_040520.ff',
                                               ]
                    );


                    $url = (string)$s3->createPresignedRequest($command,"+60 minutes")->getUri();

                        return response()->json(
                            array(
                                'status' => true,
                                'code' => RESPONSE_CODE_SUCCESS_OK,
                                'data' => array('version' => $MANDATORY_VERSION, 'url' => $url),
                                'message' => 'A new version is available!'
                            ), RESPONSE_CODE_SUCCESS_OK
                        );


                }
            else
                {

*/
                    $objects = $s3->listObjectsV2([
                                                      'Bucket' => AWS_S3_BUCKET,
                                                      'Prefix' => AWS_S3_PREFIX_FIRMWARE
                                                  ]);



                    if(isset($objects['Contents']))
                        {
                            $fimrwareVersionsAvailable = array();
                            foreach ($objects['Contents']  as $object) {

                                $fversion = str_ireplace(AWS_S3_PREFIX_FIRMWARE,'',$object['Key']);
                                if(!empty($fversion))
                                    {

                                        if(stripos($fversion,'_') !==false)
                                            {
                                                $fimrwareVersionsAvailable[] = $fversion;
                                            }
                                    }
                            }

                            arsort($fimrwareVersionsAvailable);
                            if(!empty($fimrwareVersionsAvailable))
                                {
                                    $latestFirmware = $fimrwareVersionsAvailable[0];
                                    $latestFirmware = str_ireplace('FrontierX_','',$latestFirmware);
                                    $latestFirmware = substr($latestFirmware,0,stripos($latestFirmware,'_'));


                                    $command = $s3->getCommand('GetObject',
                                                               [
                                                                   'Bucket' => AWS_S3_BUCKET,
                                                                   'Key' => AWS_S3_PREFIX_FIRMWARE.$fimrwareVersionsAvailable[0],
                                                               ]
                                    );


                                    $url = (string)$s3->createPresignedRequest($command,"+60 minutes")->getUri();
                                    if($latestFirmware > $request->input('version'))
                                        {
                                            return response()->json(
                                                array(
                                                    'status' => true,
                                                    'code' => RESPONSE_CODE_SUCCESS_OK,
                                                    'data' => array('version' => $latestFirmware, 'url' => $url),
                                                    'message' => 'A new version is available!'
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
                                                    'message' => 'You are already using the latest version '.$latestFirmware
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
                                            'message' => 'Unable to read firmware version from s3'
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
                                    'message' => 'No firmware files found'
                                ), RESPONSE_CODE_ERROR_BAD
                            );
                        }
  //              }





        }
    }

    public function forceUpdateApp(Request $request)
    {
        try {
            $file = Storage::disk('s3')->get(AWS_S3_APK_VERSION_FILE);
            return response($file,200)->header('Content-Type','application/json');
        }
       catch(\Illuminate\Contracts\Filesystem\FileNotFoundException $exception)
       {
           abort(404);
       }

    }
}