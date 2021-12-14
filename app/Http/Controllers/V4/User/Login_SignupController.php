<?php
/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 21-05-2019
 * Time: 12:36
 */

namespace App\Http\Controllers\V4\User;

use App\Models\FB_User;
use App\Models\FC_Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Runner\Exception;

class Login_SignupController
{
    public function registerUser(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name' => 'required|',
            'email' => 'required|email|unique:FB_User',
            'password' => 'required',
            'confirm_password' => 'required',
            'country' => 'required|integer',
            'gender_enum' => 'required|integer',
            'height' => 'required',
            'weight' => 'required',
            'dob' => 'required|date_format:Y-m-d',
            'policy_id'=> 'required',   
            'selected_unit'=> 'required|integer'

        ]);


        if ($validation->fails()) {
            return response()->json(
                array(
                    'status' => false,
                    'code' => RESPONSE_CODE_ERROR_MISSINGDATA,
                    'data' => null,
                    'message' => 'Required fields missing',
                    'errors' => $validation->errors(),
                ), RESPONSE_CODE_ERROR_MISSINGDATA
            );
        } else {

            if(empty(json_decode($request->input('policy_id'))))
                {
                    return response()->json(
                        array(
                            'status' => false,
                            'code' => RESPONSE_CODE_ERROR_MISSINGDATA,
                            'data' => null,
                            'message' => 'Policy id must be a json array',
                            'errors' => $validation->errors(),
                        ), RESPONSE_CODE_ERROR_MISSINGDATA
                    );
                }
            else
                {
                    $newUser = new FB_User();

                    $newUser->name = $request->name;
                    $newUser->email = $request->email;
                    $newUser->status_enum = ENUM_STATUS_PENDING_VERIFICATION;
                    if ($request->confirm_password == $request->password)
                        $newUser->password = password_hash($request->password, PASSWORD_DEFAULT);


                    $checkCountry = FC_Country::where('id', $request->country)->first();
                    if (!empty($checkCountry))
                        $newUser->FC_Country_id = $checkCountry->id;

                    $newUser->height = $request->height;
                    $newUser->weight = $request->weight;
                    
                    $dateOfBirth = \DateTime::createFromFormat('Y-m-d', $request->dob);
                    $newUser->dob = $dateOfBirth;
                    $newUser->selected_unit = $request->selected_unit;

                    if (in_array($request->gender_enum, [ENUM_GENDER_FEMALE, ENUM_GENDER_MALE])) {
                        $newUser->gender_enum = $request->gender_enum;
                    }

                    $newUser->api_token = uniqid();
                    $newUser->api_token_expiry = new \DateTime();

                    $newUser->save();
                    $newUser->updatePolicyAcceptance(json_decode($request->input('policy_id')));

                    $verificationLink = env('APP_URL', DEFAULT_URL).ACTIVATION_BASE_URL."?id=" . $newUser->id . "&email=" . $newUser->email . "&token=" . $newUser->api_token;

                    Mail::send('mails.verification', [
                        'user' => $newUser,
                        'verificationLink' => $verificationLink
                    ], function ($message) use ($newUser) {
                        $message->to($newUser->email, $newUser->name)->subject('Welcome to Fourth Frontier!');
                    });

                    if (!empty($newUser)) {
                        return response()->json(
                            array(
                                'status' => true,
                                'code' => RESPONSE_CODE_SUCCESS_OK,
                                'data' => null,
                                'message' => 'User created successfully',
                                'errors' => null,
                            ), RESPONSE_CODE_SUCCESS_OK
                        );
                    } else {
                        return response()->json(
                            array(
                                'status' => false,
                                'code' => RESPONSE_CODE_ERROR_BAD,
                                'data' => null,
                                'message' => 'Something went wrong, Please try again',
                                'errors' => null,
                            ), RESPONSE_CODE_ERROR_BAD
                        );
                    }
                }


        }
    }

}
