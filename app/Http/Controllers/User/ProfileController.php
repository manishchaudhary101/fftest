<?php
/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 23-05-2019
 * Time: 13:32
 */

namespace App\Http\Controllers\User;


use App\Models\FB_User;
use App\Models\FR_User_has_Authorized_Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Guzzle\Http\Exception\ClientErrorResponseException;
use GuzzleHttp\Exception\RequestException;
use function GuzzleHttp\json_encode;

class ProfileController 
{
    public function viewProfile(Request $request)
    {
        $userToSearch = FB_User::where('status_enum', ENUM_STATUS_ACTIVE);

        if ($request->filled('profile_id')) {
            $userToSearch->where('id', $request->profile_id);
        }

        $userToSearchData = $userToSearch->get();

        $userToSearchData->makeHidden(['api_token','api_token_expiry']);
        if (!empty($userToSearchData) && count($userToSearchData)) {
            return response()->json(
                array(
                    'status' => true,
                    'code' => RESPONSE_CODE_SUCCESS_OK,
                    'data' => $userToSearchData,
                    'message' => 'User found successfully',
                    'errors' => null,
                ),
                RESPONSE_CODE_SUCCESS_OK
            );
        } else {
            return response()->json(
                array(
                    'status' => false,
                    'code' => RESPONSE_CODE_ERROR_BAD,
                    'data' => null,
                    'message' => 'User not found successfully',
                    'errors' => null,
                ),
                RESPONSE_CODE_ERROR_BAD
            );
        }
    }
    public function addDocUser(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'Email' => 'required|email',
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
            $currentUser = FB_User::select('id')->where('email', $request->Email)->get()->first();
            if (!empty($currentUser)) {
               $authorizedUserFromDB = FR_User_has_Authorized_Users::where('FB_Authorized_User_id', '=', $currentUser->id)
               ->where('FB_User_id', '=', $request->user_id)->get();
                if(empty(json_decode($authorizedUserFromDB,true))){
                    
                    $authorizedUser = new FR_User_has_Authorized_Users();
                    $authorizedUser->FB_User_id = $request->user_id;
                    $authorizedUser->FB_Authorized_User_id = $currentUser->id;
                    $authorizedUser->save();
                    return response()->json(
                        array(
                            'status' => true,
                            'code' => RESPONSE_CODE_SUCCESS_OK,
                            'data' => null,
                            'message' => 'User Authorized',
                            'errors' => null,
                        ), RESPONSE_CODE_SUCCESS_OK
                    );
                } else {
                    return response()->json(
                        array(
                            'status' => false,
                            'code' => RESPONSE_CODE_CONFLICT,
                            'data' => null,
                            'message' => 'User already authorized',
                            'errors' => null,
                        ), RESPONSE_CODE_CONFLICT
                    );
             }

            } else {
                return response()->json(
                    array(
                        'status' => false,
                        'code' => RESPONSE_CODE_ERROR_NOTFOUND,
                        'data' => null,
                        'message' => 'Email ids not found',
                        'errors' => null,
                    ), RESPONSE_CODE_ERROR_NOTFOUND
                );
            }


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
                ),
                RESPONSE_CODE_ERROR_BAD
            );
        } else {
            $currentUser = FB_User::where('id', $request->user_id)
                ->where('status_enum', ENUM_STATUS_ACTIVE)
                ->first();

            if (!empty($currentUser)) {
                if ($request->filled('name')) {
                    $currentUser->name = $request->name;
                }

                if ($request->filled('gender')) {
                    if (in_array($request->gender, [ENUM_GENDER_MALE, ENUM_GENDER_FEMALE])) {
                        $currentUser->gender_enum = $request->gender;
                    }
                }

                if ($request->filled('height')) {
                    $currentUser->height = $request->height;
                }

                if ($request->filled('weight')) {
                    $currentUser->weight = $request->weight;
                }


                if ($request->filled('dob')) {
                    $dob = \DateTime::createFromFormat('d-m-Y', $request->dob);
                    if (!empty($dob)) {
                        $currentUser->dob = $dob;
                    }
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
                    ),
                    RESPONSE_CODE_SUCCESS_OK
                );
            } else {
                return response()->json(
                    array(
                        'status' => false,
                        'code' => RESPONSE_CODE_ERROR_BAD,
                        'data' => null,
                        'message' => 'User not found',
                        'errors' => null,
                    ),
                    RESPONSE_CODE_ERROR_BAD
                );
            }
        }
    }

    public function registerFCM(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'fcm_deviceToken' => 'required',
        ]);

        if ($validation->fails()) {
            return response()->json(
                array(
                    'status' => false,
                    'code' => RESPONSE_CODE_ERROR_BAD,
                    'data' => null,
                    'message' => 'Required fields missing',
                    'errors' => $validation->errors(),
                ),
                RESPONSE_CODE_ERROR_BAD
            );
        } else {
            $currentUser = FB_User::where('id', $request->user_id)
                ->where('status_enum', ENUM_STATUS_ACTIVE)
                ->first();

            if (!empty($currentUser)) {
                if ($request->filled('fcm_deviceToken')) {
                    $currentUser->fcm_deviceToken = $request->fcm_deviceToken;
                }
                $currentUser->save();
                return response()->json(
                    array(
                        'status' => true,
                        'code' => RESPONSE_CODE_SUCCESS_OK,
                        'data' => $currentUser,
                        'message' => 'User\'s information updated successfully.',
                        'errors' => null,
                    ),
                    RESPONSE_CODE_SUCCESS_OK
                );
            } else {
                return response()->json(
                    array(
                        'status' => false,
                        'code' => RESPONSE_CODE_ERROR_BAD,
                        'data' => null,
                        'message' => 'User not found',
                        'errors' => null,
                    ),
                    RESPONSE_CODE_ERROR_BAD
                );
            }
        }
    }
    public function jwtAuth(Request $request)
    {
        $userToSearch = FB_User::where('id', $request->user_id)->first();
        $dbJwtToken = $userToSearch->jwt_token;
        $validJWTToken = '';
        $validFailed = false;
        $auth= 'FF@123456$';
        $wordpress_url = 'https://www.frontierheartforum.com/?rest_route=/simple-jwt-login/v1/';
        if (!empty($dbJwtToken) && $dbJwtToken != null) {
            try {
                $client = new Client();
                $registerUrl="https://www.frontierheartforum.com/?rest_route=/simple-jwt-login/v1/auth/validate&JWT=YOUR_JWT";
                $registerUrl = str_replace('YOUR_JWT', $dbJwtToken, $registerUrl);
                $res = $client->request('GET', $registerUrl)->getStatusCode();
            } catch (RequestException $e) {
                $res = 200;
                $validFailed = false;
            }
            if (200 === $res) {
                $validFailed = false;
                $validJWTToken = $dbJwtToken;
            } else {
                $validFailed = true;
            }

            $resp = true;
            if($validFailed){
                try {
                    $client = new Client();
                    $registerUrl="https://www.frontierheartforum.com/?rest_route=/simple-jwt-login/v1/auth/refresh&JWT=YOUR_JWT";
                    $registerUrl = str_replace('YOUR_JWT', $dbJwtToken, $registerUrl);
                    $res = $client->request('GET', $registerUrl)->getStatusCode();
                } catch (RequestException $e) {
                    $resp = false;
                }

                if(!empty($res) && $resp){
                    $res = json_decode($res);
                    $validJWTToken = $res['data'][0]['jwt'];
                    // insert to db
                    $userToSearch->jwt_token = $request->input('jwt_token',$validJWTToken);
                    $userToSearch->update();
                    $userToSearch->save();
                    return response()->json(
                        array(
                            'status' => true,
                            'code' => RESPONSE_CODE_SUCCESS_OK,
                            'data' => array(
                                    'jwt' => $validJWTToken,
                                    'auth' => $auth,
                                    'wordpress_url' => $wordpress_url,
                                        ),
                            
                            'message' => 'JWt Validate Success Refresh',
                            'errors' => null,
                        ),
                        RESPONSE_CODE_SUCCESS_OK
                    );
                } else {
                    // registeration api
                    return response()->json(
                        array(
                            'status' => true,
                            'code' => RESPONSE_CODE_ERROR_UNAUTHORIZED,
                            'data' => array('jwt' => $dbJwtToken,
                            'auth' => $auth,
                            'wordpress_url' => $wordpress_url,
                            ),
                        'message' => 'JWt Validate Failed Refresh',
                            'errors' => null,
                        ),
                        RESPONSE_CODE_ERROR_UNAUTHORIZED
                    );
                }

            } else {
                return response()->json(
                    array(
                        'status' => true,
                        'code' => RESPONSE_CODE_SUCCESS_OK,
                        'data' => array(
                            'jwt' => $validJWTToken,
                        'auth' => $auth,
                        'wordpress_url' => $wordpress_url,
                        ),
                        'message' => 'JWt Validate Success Validate',
                        'errors' => null,
                    ),
                    RESPONSE_CODE_SUCCESS_OK
                );
            }
        } else {
            $userToEmail = FB_User::where('id', $request->user_id);
            $dbEmail = $userToEmail->get()->pluck('email');
            $userName = FB_User::where('id', $request->user_id);
            $dbName = $userName->get()->pluck('name');
            $passWordGenerated = $dbName[0].'@123$';
            
            $client = new Client();
            $registerUrl="https://www.frontierheartforum.com/?rest_route=/simple-jwt-login/v1/users&email=NEW_USER_EMAIL&password=Password_auth&AUTH_KEY=FF@123456$&display_name=User_Replace";
            $registerUrl = str_replace('NEW_USER_EMAIL', $dbEmail[0], $registerUrl);
            $registerUrl = str_replace('Password_auth', $passWordGenerated, $registerUrl);
            $registerUrl = str_replace('User_Replace', $dbName[0], $registerUrl);
            $res = $client->request('POST', $registerUrl)->getStatusCode();
            
            if ($res == 200){
                $client = new Client();
                $registerUrl="https://www.frontierheartforum.com/?rest_route=/simple-jwt-login/v1/auth&email=Email_auth&password=Password_auth";
                $registerUrl = str_replace('Email_auth', $dbEmail[0], $registerUrl);
                $registerUrl = str_replace('Password_auth', $passWordGenerated, $registerUrl);
                $authRes = $client->request('POST', $registerUrl);
                if (json_decode($authRes->getStatusCode()) == 200) {
                    $res = json_decode($authRes->getbody());
                    $validJWTToken = $res->data->jwt;
                    $userToSearch->jwt_token = $validJWTToken;
                    $userToSearch->jwt_password = $passWordGenerated;
                    $userToSearch->update();
                    return response()->json(
                        array(
                        'status' => true,
                        'code' => RESPONSE_CODE_SUCCESS_OK,
                        'data' => array('jwt' => $validJWTToken,
                        'auth' => $auth,
                        'wordpress_url' => $wordpress_url,
                        ),
                        'message' => 'JWt Auth Success',
                        'errors' => null,
                    ),
                        RESPONSE_CODE_SUCCESS_OK
                    );
                } else {
                    return response()->json(
                        array(
                            'status' => false,
                            'code' => RESPONSE_CODE_ERROR_UNAUTHORIZED,
                            'data' => array(),
                            'message' => 'Authentication failed',
                            'errors' => null,
                        ),
                        RESPONSE_CODE_ERROR_UNAUTHORIZED
                    );    
                }
            } else {
                return response()->json(
                    array(
                        'status' => false,
                        'code' => RESPONSE_CODE_ERROR_UNAUTHORIZED,
                        'data' => array(),
                        'message' => 'Authentication failed',
                        'errors' => null,
                    ),
                    RESPONSE_CODE_ERROR_UNAUTHORIZED
                );
            }
            
        }
    }
    // new registeration function
}