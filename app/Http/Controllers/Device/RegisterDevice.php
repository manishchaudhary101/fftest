<?php


namespace App\Http\Controllers\Device;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RegisterDevice extends Controller
{
    function registerdevice(Request $request)
    {

        //Validate for user id and token
        $validator = Validator::make($request->all(),
                                     [
                                         'user_id' => 'required',
                                         'biostrip_macid' => 'required',
                                         'serial_number' => 'required',
                                     ]
        );

        if ($validator->fails()) {
            return response()
                ->json(
                    array(
                        'status' => false,
                        'code' => RESPONSE_CODE_ERROR_MISSINGDATA,
                        'data' => null,
                        'message' => 'Mandatory fields are missing',
                        'errors' => $validator->errors()
                    ),
                    RESPONSE_CODE_ERROR_MISSINGDATA
                );
        } else {

            try
                {

                    switch ($request->input('source_platform',0))
                        {
                        case 0://web
                                DB::table('FR_User_has_Device')->insert([
                                                                            'created_at'    => new \DateTime(),
                                                                            'FB_User_id' => $request->input('user_id'),
                                                                            'biostrip_macid' => $request->input('biostrip_macid'),
                                                                            'serial_number' => $request->input('serial_number'),
                                                                        ]);
                                break;
                        case 1:
                                DB::table('FR_User_has_Device')->insert([
                                                                            'created_at'    => new \DateTime(),
                                                                            'last_sync_on_android'    => new \DateTime(),
                                                                            'FB_User_id' => $request->input('user_id'),
                                                                            'biostrip_macid' => $request->input('biostrip_macid'),
                                                                            'serial_number' => $request->input('serial_number'),
                                                                        ]);
                                break;

                        case 2:
                                DB::table('FR_User_has_Device')->insert([
                                                                            'created_at'    => new \DateTime(),
                                                                            'last_sync_on_ios'    => new \DateTime(),
                                                                            'FB_User_id' => $request->input('user_id'),
                                                                            'biostrip_macid' => $request->input('biostrip_macid'),
                                                                            'serial_number' => $request->input('serial_number'),
                                                                        ]);
                                break;
                        }

                    $existingDevice = \App\Models\FC_Device::where('biostrip_macid',$request->input('biostrip_macid'))->first();
                    if(empty($existingDevice))
                    {
                        $newDevice = new \App\Models\FC_Device();
                        $newDevice->biostrip_macid = $request->input('biostrip_macid');
                        $newDevice->save();
                    }
                    return response()
                        ->json(
                            array(
                                'status' => true,
                                'code' => RESPONSE_CODE_SUCCESS_OK,
                                'message' => 'Device Added!',
                            ),
                            RESPONSE_CODE_SUCCESS_OK
                        );
                }catch (\Exception $e)
                {
                    Log::error($e);
                    return response()
                        ->json(
                            array(
                                'status' => false,
                                'code' => RESPONSE_CODE_ERROR_BAD,
                                'message' => 'Couldnt add the record',
                                'errors' => $e->getMessage()
                            ),
                            RESPONSE_CODE_ERROR_BAD
                        );
                }




        }
    }
}
