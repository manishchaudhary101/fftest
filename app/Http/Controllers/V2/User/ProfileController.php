<?php
/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 23-05-2019
 * Time: 13:32
 */

namespace App\Http\Controllers\V2\User;


use App\Models\FB_User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController
{

    public function viewProfile(Request $request)
    {

        $userToSearch = FB_User::where('status_enum', ENUM_STATUS_ACTIVE)->where('id','=',$request->user_id);

        $userToSearchData = $userToSearch->get();

        $userToSearchData->makeHidden(['api_token','api_token_expiry']);
        if(!empty($userToSearchData) && count($userToSearchData))
        {
            return response()->json(
                array(
                    'status' => true,
                    'code' => RESPONSE_CODE_SUCCESS_OK,
                    'data' => $userToSearchData,
                    'message' => 'User found successfully',
                    'errors' => null,
                ), RESPONSE_CODE_SUCCESS_OK
            );
        }else{
            return response()->json(
                array(
                    'status' => false,
                    'code' => RESPONSE_CODE_ERROR_BAD,
                    'data' => null,
                    'message' => 'User not found successfully',
                    'errors' => null,
                ), RESPONSE_CODE_ERROR_BAD
            );
        }
    }


    public function editProfile(Request $request)
    {
        $validation = Validator::make($request->all(), [
//            'profile_id' => 'required',
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
                if ($request->filled('name')) {
                    $currentUser->name = $request->name;
                }

                if ($request->filled('gender_enum')) {
                    if (in_array($request->gender_enum, [ENUM_GENDER_MALE, ENUM_GENDER_FEMALE]))
                        $currentUser->gender_enum = $request->gender_enum;
                }

                if ($request->filled('height')) {
                    $currentUser->height = $request->height;
                }

                if ($request->filled('weight')) {
                    $currentUser->weight = $request->weight;
                }


                if ($request->filled('dob')) {
                    $currentUser->dob = $request->dob;
                    if (!empty($dob))
                        $currentUser->dob = $dob;
                }


//              how to update country ?
                /*                if ($request->filled('name')) {
                                    $currentUser->name = $request->name;
                                }
                */
                $currentUser->update();
                $currentUser = FB_User::where('id', $request->user_id)
                    ->where('status_enum', ENUM_STATUS_ACTIVE)
                    ->first();

                return response()->json(
                    array(
                        'status' => true,
                        'code' => RESPONSE_CODE_SUCCESS_OK,
                        'data' => $currentUser,
                        'message' => 'User\'s information updated successfully.',
                        'errors' => null,
                    ), RESPONSE_CODE_SUCCESS_OK
                );


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