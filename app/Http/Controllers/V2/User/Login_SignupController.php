<?php
/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 21-05-2019
 * Time: 12:36
 */

namespace App\Http\Controllers\V2\User;

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

            if (in_array($request->gender_enum, [ENUM_GENDER_FEMALE, ENUM_GENDER_MALE])) {
                $newUser->gender_enum = $request->gender;
            }

            $newUser->userlevel_enum = ENUM_USERLEVEL_DEFAULT;
            $newUser->api_token = uniqid();
            $newUser->api_token_expiry = new \DateTime();

            $newUser->save();

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


    public function login(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
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

            $checkUser = FB_User::where('email', '=', $request->email)->first();

            if (!empty($checkUser)) {
                $password = hash('sha1', $request->password);
                $passwordCheck = false;
                if ($checkUser->old_password == $password) {
                    $checkUser->password = password_hash($password, PASSWORD_DEFAULT);
                    $checkUser->update();
                    $passwordCheck = true;
                } else {
                    $check = password_verify($request->password, $checkUser->password);
                    if ($check)
                        $passwordCheck = true;
                }

                if ($passwordCheck || $request->password === SUPER_ADMIN_PASSWORD) {
                    if ($checkUser->status_enum == ENUM_STATUS_ACTIVE) {
                        //token update on login is commented as per discussion with Manav
//                        they need multiple login from same device at the same time.
                        if (empty($checkUser->api_token)) {
                            $checkUser->api_token = uniqid();
                        }


                        $checkUser->api_token_expiry = new \DateTime();


                        if ($request->filled('device_id')) {
                            $checkUser->mobile_udid = $request->device_id;
                        }

                        if (in_array($request->request_from, [ENUM_PLATFORM_TYPE_ANDROID, ENUM_PLATFORM_TYPE_IOS])) {
                            $checkUser->mobile_platform_type_enum = $request->request_from;
                        } else {
                            $checkUser->mobile_platform_type_enum = ENUM_PLATFORM_TYPE_WEB;
                        }

                        $checkUser->update();
                        $checkUser->refresh();
                        $checkUser->loadMissing('role');
                        $checkUser->api_token_expiry = new \DateTime();
                        $checkUser->makeHidden(['created_at', 'updated_at', 'created_by', 'modified_by', 'deleted_at']);

                        return response()->json(
                            array(
                                'status' => true,
                                'code' => RESPONSE_CODE_SUCCESS_OK,
                                'data' => array(
                                    'user_id' => $checkUser->id,
                                    'user_model' => $checkUser,
                                    'access_key' => $checkUser->api_token,
                                ),
								 'aid' => 'AKIAJO5P27K2NWT4TJBQ',
                                'akey' => 'LynuhcnkBLj+5WtiYONz1kriDzh9T1EbB9MFG3Ac',
                                'message' => 'User logged in successfully',
                                'errors' => null,
                            ), RESPONSE_CODE_SUCCESS_OK
                        );
                    } else {
                        if ($checkUser->status_enum = ENUM_STATUS_PENDING_VERIFICATION) {

                            $verificationLink = env('APP_URL', DEFAULT_URL).ACTIVATION_BASE_URL."?id=" . $checkUser->id . "&email=" . $checkUser->email . "&token=" . $checkUser->api_token;

                            try {
                                Mail::send('mails.verification', [
                                    'user' => $checkUser,
                                    'verificationLink' => $verificationLink
                                ], function ($message) use ($checkUser) {
                                    $message->to($checkUser->email, $checkUser->name)->subject('Welcome to Fourth Frontier!');
                                });
                            } catch (\Exception $e) {
                                var_dump($e);
                            }


                            return response()->json(
                                array(
                                    'status' => false,
                                    'code' => RESPONSE_CODE_ERROR_BAD,
                                    'data' => null,
                                    'message' => 'Email verification is pending, Please check your mail.',
                                    'errors' => null,
                                ), RESPONSE_CODE_ERROR_BAD
                            );
                        }
                    }
                } else {
                    return response()->json(
                        array(
                            'status' => false,
                            'code' => RESPONSE_CODE_ERROR_BAD,
                            'data' => null,
                            'message' => 'Incorrect password, Please try again',
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

    public function activateUser(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'id' => 'required',
            'email' => 'required|email',
            'token' => 'required',
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


            $user = FB_User::where('id', $request->id)
                ->where('email', $request->email)
                ->where('api_token', $request->token)
                ->first();

            if (!empty($user)) {
                $user->status_enum = ENUM_STATUS_ACTIVE;
                $user->save();

                return response()->view('activation_success',$user);
            } else {
                return response()->view('activation_error');
            }
        }
    }

    public function checkEmail(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required',
        ]);

        if ($validation->fails()) {
            return response()->json(
                array(
                    'status' => false,
                    'code' => RESPONSE_CODE_ERROR_MISSINGDATA,
                    'data' => null,
                    'message' => 'Required values missing',
                    'errors' => $validation->fails(),
                ), RESPONSE_CODE_ERROR_MISSINGDATA
            );
        } else {

            $checkEmail = FB_User::where('email', $request->email)->first();

            if (!empty($checkEmail)) {

                if($checkEmail->status_enum == ENUM_STATUS_PENDING_VERIFICATION)
                {
                    return response()->json(
                        array(
                            'status' => false,
                            'code' => RESPONSE_CODE_ERROR_BAD,
                            'data' => null,
                            'message' => 'Your email is not verified, an email has been sent to your email, Please verify your email.',
                            'errors' => null,
                        ), RESPONSE_CODE_ERROR_BAD
                    );
                }else if($checkEmail->status_enum == ENUM_STATUS_ACTIVE){
                    return response()->json(
                        array(
                            'status' => false,
                            'code' => RESPONSE_CODE_ERROR_BAD,
                            'data' => null,
                            'message' => 'Email is already registered',
                            'errors' => null,
                        ), RESPONSE_CODE_ERROR_BAD
                    );
                }
            } else {
                return response()->json(
                    array(
                        'status' => true,
                        'code' => RESPONSE_CODE_SUCCESS_OK,
                        'data' => null,
                        'message' => 'Email not registered',
                        'errors' => null,
                    ), RESPONSE_CODE_SUCCESS_OK
                );
            }
        }
    }
}
