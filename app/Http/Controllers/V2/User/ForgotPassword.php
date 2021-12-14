<?php
/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 22-05-2019
 * Time: 10:15
 */

namespace App\Http\Controllers\V2\User;


use App\Models\FB_User;
use App\Models\FE_PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ForgotPassword
{

    public function forgotPassword(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required',
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

            $currentUser = FB_User::where('email', $request->email)->first();
            $currentDate = new \DateTime();
            if (!empty($currentUser)) {
                $forgotPasswordRequest = FE_PasswordReset::where('FB_User_id', $currentUser->id)
                    ->where('token_expiry', '>=', $currentDate)->first();


                if (!empty($forgotPasswordRequest)) {
                    Mail::send('mails.resetPassword', ['user' => $currentUser, 'resetTokenData' => $forgotPasswordRequest], function ($message) use ($currentUser) {
                        $message->to($currentUser->email, $currentUser->name)->subject('Change Password Request - '.str_ireplace('https://api.','',env('APP_URL')));
                    });

                    return response()->json(
                        array(
                            'status' => true,
                            'code' => RESPONSE_CODE_SUCCESS_OK,
                            'data' => null,
                            'message' => 'An email has been sent to your email id.',
                            'errors' => null,
                        ), RESPONSE_CODE_SUCCESS_OK
                    );

                } else {

                    $saveToken = new FE_PasswordReset();

                    $saveToken->token_expiry = new \DateTime('tomorrow');
                    $saveToken->reset_token = uniqid();
                    $saveToken->FB_User_id = $currentUser->id;
                    $saveToken->save();

                    Mail::send('mails.resetPassword', [
                        'user' => $currentUser,
                        'resetTokenData' => $saveToken
                    ], function ($message) use ($currentUser) {
                        $message->to($currentUser->email, $currentUser->name)->subject('Change Password Request - '.str_ireplace('https://api.','',env('APP_URL')));
                    });

                    return response()->json(
                        array(
                            'status' => true,
                            'code' => RESPONSE_CODE_SUCCESS_OK,
                            'data' => null,
                            'message' => 'An email has been sent to your email id, Please check your mail.',
                            'errors' => null,
                        ), RESPONSE_CODE_SUCCESS_OK
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

    public function changePassword(Request $request)
    {

        if ($request->filled('old_password')) {
            $validation = Validator::make($request->all(), [
                'user_id' => 'required',
                'token' => 'required',
                'password' => 'required',
            ]);
        } else {
            $validation = Validator::make($request->all(), [
                'user_id' => 'required',
                'token' => 'required',
                'password' => 'required',
                'retype_password' => 'required',
            ]);
        }


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

            if ($request->filled('old_password')) {
                $userQuery = FB_User::where('id',$request->user_id)
                    ->where('api_token',$request->token)
                    ->first();

                if(!empty($userQuery))
                {
                    $oldPassword1 = hash('sha1', $request->password);
                    if($userQuery->password == $oldPassword1 || password_verify($request->old_password,$userQuery->password))
                    {
                        $userQuery->password = password_hash($request->password,PASSWORD_DEFAULT);
                        $userQuery->old_password = password_hash($request->password,PASSWORD_DEFAULT);
                        $userQuery->update();

                        return response()->json(
                            array(
                                'status' => true,
                                'code' => RESPONSE_CODE_SUCCESS_OK,
                                'data' => null,
                                'message' => 'Password changed successfully',
                                'errors' => null,
                            ), RESPONSE_CODE_SUCCESS_OK
                        );
                    }else{
                        return response()->json(
                            array(
                                'status' => false,
                                'code' => RESPONSE_CODE_ERROR_BAD,
                                'data' => null,
                                'message' => 'Incorrect Password',
                                'errors' => null,
                            ), RESPONSE_CODE_ERROR_BAD
                        );
                    }
                }else{
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
            } else {
                $checkForgotPasswordRequest = FE_PasswordReset::where('FB_User_id', $request->user_id)
                    ->where('reset_token', $request->token)->first();

                if (!empty($checkForgotPasswordRequest)) {
                    $currentTime = new \DateTime();
                    $tokenTime = new \DateTime($checkForgotPasswordRequest->token_expiry);
                    if ($tokenTime >= $currentTime) {
                        $currentUser = FB_User::where('id', $request->user_id)->first();
                        $currentUser->password = password_hash($request->password, PASSWORD_DEFAULT);
                        $currentUser->old_password = password_hash($request->password, PASSWORD_DEFAULT);
                        $currentUser->status_enum = ENUM_STATUS_ACTIVE;
                        $currentUser->api_token = uniqid();
                        $currentUser->api_token_expiry = new \DateTime();
                        $currentUser->update();


                        if (!empty($currentUser)) {
                            FE_PasswordReset::where('FB_User_id', $request->user_id)->delete();
                            return response()->json(
                                array(
                                    'status' => true,
                                    'code' => RESPONSE_CODE_SUCCESS_OK,
                                    'data' => null,
                                    'message' => 'Password changed successfully',
                                    'errors' => null,
                                ), RESPONSE_CODE_SUCCESS_OK
                            );
                        }
                    } else {
                        return response()->json(
                            array(
                                'status' => false,
                                'code' => RESPONSE_CODE_SUCCESS_OK,
                                'data' => null,
                                'message' => 'Token expire',
                                'errors' => null,
                            ), RESPONSE_CODE_SUCCESS_OK
                        );
                    }
                } else {
                    return response()->json(
                        array(
                            'status' => false,
                            'code' => RESPONSE_CODE_SUCCESS_OK,
                            'data' => null,
                            'message' => 'Invalid reset link',
                            'errors' => null,
                        ), RESPONSE_CODE_SUCCESS_OK
                    );
                }
            }
        }
    }
}
