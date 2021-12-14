<?php
/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 23-05-2019
 * Time: 15:11
 */

namespace App\Http\Middleware;

use App\Models\FB_User;
use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CheckToken
{
    public function handle($request, Closure $next)
    {
        $validator = Validator::make($request->all(), [
            'access_key' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(
                array(
                    'status' => false,
                    'code' => RESPONSE_CODE_ERROR_MISSINGDATA,
                    'data' => null,
                    'message' => 'Required Field Missing',
                    'errors' => $validator->errors(),
                ), RESPONSE_CODE_ERROR_MISSINGDATA
            );
        } else {
            $user = FB_User::where('id', '=', $request->user_id)->where('status_enum', ENUM_STATUS_ACTIVE)->first();

            if (!empty($user)) {

                //testing
                if ($user->api_token == $request->access_key) {
                    $tokenTime_db = new \DateTime($user->api_token_expiry);
                    $current_time = new \DateTime();
                    $time = $current_time->diff($tokenTime_db);
                    /* token expiry is commented as per Aarsh's suggestion - Prakhar */
//                    if ($time->i <= 500) {

//                        if ($time->i > 5) {
                            $user->api_token_expiry = $current_time;
                            $user->save();
//                        }

                        return $next($request);
//                    } else {
//                        return response()
//                            ->json(
//                                array(
//                                    'status' => false,
//                                    'code' => RESPONSE_CODE_ERROR_TIMEOUT,
//                                    'data' => null,
//                                    'message' => 'Session expire, Please login again.',
//                                    'errors' => null,
//                                ), RESPONSE_CODE_ERROR_TIMEOUT
//                            );
//                    }
                } else {
                    return response()
                        ->json(
                            array(
                                'status' => false,
                                'code' => RESPONSE_CODE_ERROR_UNAUTHORIZED,
                                'data' => 'empty',
                                'message' => 'Session expire, Please login again.',
                                'errors' => null,
                            ), RESPONSE_CODE_ERROR_UNAUTHORIZED
                        );
                }


            } else {
                return response()
                    ->json(
                        array(
                            'status' => false,
                            'code' => RESPONSE_CODE_ERROR_BAD,
                            'data' => null,
                            'message' => 'Something went wrong',
                            'errors' => null,
                        ), RESPONSE_CODE_ERROR_BAD
                    );
            }


        }
    }
}
