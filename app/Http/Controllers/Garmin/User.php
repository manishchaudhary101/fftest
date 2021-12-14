<?php

namespace App\Http\Controllers\Garmin;

use App\Http\Controllers\Controller;
use App\Models\FB_User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class User extends Controller
{

    public function registerGarmin(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'garmin_userId' => 'required',
            'garmin_userAccessToken' => 'required',
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

            $currentUser->garmin_userId = $request->input('garmin_userId');
            $currentUser->garmin_userAccessToken = $request->input('garmin_userAccessToken');
            $currentUser->garmin_registered_at = new \DateTime();
            $currentUser->update();


            return response()->json(
                array(
                    'status' => true,
                    'code' => RESPONSE_CODE_SUCCESS_OK,
                    'data' => $currentUser->garmin_userId,
                    'message' => 'User Garmin information updated successfully.',
                    'errors' => null,
                ), RESPONSE_CODE_SUCCESS_OK
            );
        }
    }


}
