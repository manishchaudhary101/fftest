<?php

/**
 * Created by PhpStorm.
 * User: Carl Abraham
 */

namespace App\Http\Controllers\Common;

use Aws\S3\S3Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use \Illuminate\Support\Facades\Validator;
use \Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Policies
{
    
    /***
     * Gets the policy acceptance status for the current user
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPolicyAcceptance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'access_key' => Rule::requiredIf(function () use ($request) {
                return (boolean)$request->input("user_id",0);
            }),
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
        } else
        {
            /** @var \App\Models\FB_User $currentUser */
            $currentUser = \App\Models\FB_User::find($request->user_id);
            
            if(!empty($currentUser))
            {
                if($currentUser->api_token != $request->access_key)
                {
                    return response()
                        ->json(
                            array(
                                'status' => false,
                                'code' => RESPONSE_CODE_ERROR_UNAUTHORIZED,
                                'data' => 'empty',
                                'message' => 'Session expired, Please login again.',
                                'errors' => null,
                            ), RESPONSE_CODE_ERROR_UNAUTHORIZED
                        );
                }
            }
            
            $overallStatus = true;
    
            $latestPolicies = \App\Models\FC_Policy::
            select('id')
                                                   ->select('policy_id')
                                                   ->selectRaw('max(version)')
                                                   ->selectRaw('max(created_at)')
                                                   ->selectRaw('max(id)')
                                                   ->groupBy('policy_id')->get();
    
            //generate the list of latest accepted policies
            $userPolicyAcceptanceStatus = [];
            foreach ($latestPolicies as $latestPolicy)
            {
                $thePolicy = \App\Models\FC_Policy::find($latestPolicy['max(id)']);
                $acceptedStatus = false;
                $acceptedOn = null;
                if (!empty($currentUser->acceptedPolicyRecords))
                {
                    $acceptanceRecord = $currentUser->acceptedPolicyRecords->where(
                        'policy_id',
                        $latestPolicy['policy_id']
                    )
                                                                           ->sortByDesc('id')->first();
                    if (!empty($acceptanceRecord))
                    {
                        if ($acceptanceRecord->FC_Policy_id == $latestPolicy['max(id)'])
                        {
                            $acceptedStatus = true;
                            $acceptedOn = $acceptanceRecord->created_at->format(DEFAULT_DATE_INPUT_FORMAT);
                        }
                    }
                }
        
                if ($acceptedStatus == false && $thePolicy->is_mandatory == true)
                {
                    $overallStatus = false;
                }
        
                $userPolicyAcceptanceStatus[] = [
                    'policy_id' => $latestPolicy['policy_id'],
                    'version' => $latestPolicy['max(version)'],
                    'is_accepted' => $acceptedStatus,
                    'accepted_on' => $acceptedOn,
                    'name' => $thePolicy->name,
                    'description' => $thePolicy->getDescriptionRefDictionary(),
                    'is_displayed' => $thePolicy->is_displayed,
                    'is_mandatory' => $thePolicy->is_mandatory,
//                'policy_object'    => $thePolicy
                ];
            }
    
            return response()
                ->json(
                    array(
                        'status' => $overallStatus,
                        'code' => RESPONSE_CODE_SUCCESS_OK,
                        'data' => $userPolicyAcceptanceStatus,
                        'message' => 'User policy acceptance list',
                    ),
                    RESPONSE_CODE_SUCCESS_OK
                );
        }
    }
    
    /***
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePolicyAcceptance(Request $request)
    {
        //Validate for user id and token
        $validator = Validator::make(
            $request->all(),
            [
                'policy_id' => 'required',
            ]
        );
        
        if ($validator->fails())
        {
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
        }
        else
        {
            /** @var \App\Models\FB_User $currentUser */
            $currentUser = \App\Models\FB_User::find($request->user_id);
            $currentUser->updatePolicyAcceptance(json_decode($request->input('policy_id')));
            
            return response()
                ->json(
                    array(
                        'status' => true,
                        'code' => RESPONSE_CODE_SUCCESS_OK,
                        'data' => $currentUser->getLatestAcceptedPolicyIds(),
                        'message' => 'User policy acceptance list',
                    ),
                    RESPONSE_CODE_SUCCESS_OK
                );
        }
    }
    
    
    //for testing only
    public function addNewPolicy(Request $request)
    {
        /** @var \App\Models\FB_User $currentUser */
        $currentUser = \App\Models\FB_User::find($request->user_id);
        if (in_array($currentUser->email, explode(',', env('ADMIN_EMAILS'))))
        {
            //Validate for user id and token
            $validator = Validator::make(
                $request->all(),
                [
                    'policy_id' => 'required|min:1',
                    'name' => 'required|min:4',
                    'content' => 'string',
                    'description' => 'string',
                    'is_displayed' => 'boolean',
                    'is_mandatory' => 'boolean',
                    'url' => 'string',
                    'version' => 'required|numeric|min:1',
                ]
            );
            
            if ($validator->fails())
            {
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
            }
            else
            {
                //check latest version of existing policy id
                $existingPolicy = \App\Models\FC_Policy::where('policy_id', $request->input('policy_id'))
                                                       ->orderBy('version', 'DESC')
                                                       ->first();
                if (!empty($existingPolicy))
                {
                    if ($existingPolicy->version >= $request->input('version'))
                    {
                        return response()
                            ->json(
                                array(
                                    'status' => false,
                                    'code' => RESPONSE_CODE_ERROR_NOPERMISSION,
                                    'data' => null,
                                    'message' => 'A version equal or higher already exists!',
                                ),
                                RESPONSE_CODE_ERROR_NOPERMISSION
                            );
                    }
                }
                
                $newPolicy = new \App\Models\FC_Policy();
                $newPolicy->policy_id = $request->input('policy_id');
                $newPolicy->name = $request->input('name');
                $newPolicy->content = $request->input('content');
                $newPolicy->description = $request->input('description');
                $newPolicy->is_displayed = $request->input('is_displayed',true);
                $newPolicy->is_mandatory = $request->input('is_mandatory',false);
                $newPolicy->url = $request->input('url');
                $newPolicy->version = $request->input('version');
                $newPolicy->save();
                
                return response()
                    ->json(
                        array(
                            'status' => true,
                            'code' => RESPONSE_CODE_SUCCESS_OK,
                            'data' => $newPolicy,
                            'message' => 'Policy created!',
                        ),
                        RESPONSE_CODE_SUCCESS_OK
                    );
            }
        }
        else
        {
            return response()
                ->json(
                    array(
                        'status' => false,
                        'code' => RESPONSE_CODE_ERROR_UNAUTHORIZED,
                        'data' => null,
                        'message' => 'You dont have permission to perform this action',
                    ),
                    RESPONSE_CODE_ERROR_UNAUTHORIZED
                );
        }
    }
    
    public function removePolicy(Request $request)
    {
        /** @var \App\Models\FB_User $currentUser */
        $currentUser = \App\Models\FB_User::find($request->user_id);
        if (in_array($currentUser->email, explode(',', env('ADMIN_EMAILS'))))
        {
            //Validate for user id and token
            $validator = Validator::make(
                $request->all(),
                [
                    'policy_id' => 'required|exists:FC_Policy,policy_id',
                    'remove_all_versions' => 'boolean'
                ]
            );
            
            if ($validator->fails())
            {
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
            }
            else
            {
                //check latest version of existing policy id
                /** @var \App\Models\FC_Policy $existingPolicy */
                $existingPolicy = \App\Models\FC_Policy::where('policy_id', $request->input('policy_id'))
                                                       ->orderBy('version', 'DESC')
                                                       ->first();
                if (!empty($existingPolicy))
                {
                    if($existingPolicy->acceptedByUsers->count() > 0)
                    {
                        return response()
                            ->json(
                                array(
                                    'status' => false,
                                    'code' => RESPONSE_CODE_ERROR_NOPERMISSION,
                                    'data' => ['version' => $existingPolicy->version,'user_ids' =>$existingPolicy->acceptedByUsers->pluck('FB_User_id')],
                                    'message' => 'This policy has been accepted by users and cannot be deleted',
                                    'errors' => null
                                ),
                                RESPONSE_CODE_ERROR_NOPERMISSION
                            );
                    }
                    else
                    {
                        if($request->input('remove_all_versions') == true)
                        {
                            //not coded yet
                            echo 'This option is not available yet';
                        }
                        else
                        {
                            $existingPolicy->delete();
                            return response()
                                ->json(
                                    array(
                                        'status' => false,
                                        'code' => RESPONSE_CODE_ERROR_NOPERMISSION,
                                        'data' => ['policy_id'=> $existingPolicy->policy_id, 'version'  => $existingPolicy->version],
                                        'message' => 'Policy version deleted',
                                        'errors' => null,
                                ),
                                    RESPONSE_CODE_ERROR_NOPERMISSION
                                );
                        }
                        
                    }
                }
                else
                {
                    return response()
                        ->json(
                            array(
                                'status' => false,
                                'code' => RESPONSE_CODE_ERROR_NOTFOUND,
                                'data' => null,
                                'message' => 'No policy found',
                            ),
                            RESPONSE_CODE_ERROR_NOTFOUND
                        );
                }
            }
        }
        else
        {
            return response()
                ->json(
                    array(
                        'status' => false,
                        'code' => RESPONSE_CODE_ERROR_UNAUTHORIZED,
                        'data' => null,
                        'message' => 'You dont have permission to perform this action',
                    ),
                    RESPONSE_CODE_ERROR_UNAUTHORIZED
                );
        }
    }
    
    public function getUserAcceptance(Request $request)
    {
        /** @var \App\Models\FB_User $currentUser */
        $currentUser = \App\Models\FB_User::find($request->user_id);
        if (in_array($currentUser->email, explode(',', env('ADMIN_EMAILS'))))
        {
            //Validate for user id and token
            $validator = Validator::make(
                $request->all(),
                [
                    'user_id_to_get' => 'required|exists:FB_User,id',
                    'remove_all_versions' => 'boolean'
                ]
            );
            
            if ($validator->fails())
            {
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
            }
            else
            {
                $overallStatus = true;
                $userToGet = \App\Models\FB_User::find($request->user_id_to_get);
                $latestPolicies = \App\Models\FC_Policy::
                select('id')
                                                       ->select('policy_id')
                                                       ->selectRaw('max(version)')
                                                       ->selectRaw('max(created_at)')
                                                       ->selectRaw('max(id)')
                                                       ->groupBy('policy_id')->get();
    
                //generate the list of latest accepted policies
                $userPolicyAcceptanceStatus = [];
                foreach ($latestPolicies as $latestPolicy)
                {
                    $acceptedStatus = false;
                    $acceptedOn = null;
                    if (!empty($userToGet->acceptedPolicyRecords))
                    {
                        $acceptanceRecord = $userToGet->acceptedPolicyRecords->where('policy_id', $latestPolicy['policy_id'])
                                                                               ->sortByDesc('id')->first();
                        if (!empty($acceptanceRecord))
                        {
                            if ($acceptanceRecord->FC_Policy_id == $latestPolicy['max(id)'])
                            {
                                $acceptedStatus = true;
                                $acceptedOn = $acceptanceRecord->created_at->format(DEFAULT_DATE_INPUT_FORMAT);
                            }
                        }
                    }
        
                    if ($acceptedStatus == false)
                    {
                        $overallStatus = false;
                    }
                    $userPolicyAcceptanceStatus[] = [
                        'policy_id' => $latestPolicy['policy_id'],
                        'version' => $latestPolicy['max(version)'],
                        'status' => $acceptedStatus,
                        'accepted_on' => $acceptedOn,
                    ];
                }
    
                return response()
                    ->json(
                        array(
                            'status' => $overallStatus,
                            'code' => RESPONSE_CODE_SUCCESS_OK,
                            'data' => $userPolicyAcceptanceStatus,
                            'message' => 'User policy acceptance list',
                        ),
                        RESPONSE_CODE_SUCCESS_OK
                    );
            }
        }
        else
        {
            return response()
                ->json(
                    array(
                        'status' => false,
                        'code' => RESPONSE_CODE_ERROR_UNAUTHORIZED,
                        'data' => null,
                        'message' => 'You dont have permission to perform this action',
                    ),
                    RESPONSE_CODE_ERROR_UNAUTHORIZED
                );
        }
    }
    
    public function removeUserAcceptance(Request $request)
    {
        /** @var \App\Models\FB_User $currentUser */
        $currentUser = \App\Models\FB_User::find($request->user_id);
        if (in_array($currentUser->email, explode(',', env('ADMIN_EMAILS'))))
        {
            //Validate for user id and token
            $validator = Validator::make(
                $request->all(),
                [
                    'policy_id' => 'required|exists:FC_Policy,policy_id',
                    'user_id_to_remove' => 'required|exists:FB_User,id',
                    'remove_all_versions' => 'boolean'
                ]
            );
            
            if ($validator->fails())
            {
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
            }
            else
            {
                //check latest version of existing policy id
                /** @var \App\Models\FE_UserPolicyAcceptance $existingAcceptanceRecord */
                $existingAcceptanceRecord = \App\Models\FE_UserPolicyAcceptance::where('policy_id', $request->input('policy_id'))
                                                       ->where('FB_User_id', $request->input('user_id_to_remove'))
                                                       ->orderBy('id', 'DESC')
                                                       ->first();
                if (!empty($existingAcceptanceRecord))
                {
                   $existingAcceptanceRecord->delete();
                    return response()
                        ->json(
                            array(
                                'status' => false,
                                'code' => RESPONSE_CODE_ERROR_NOPERMISSION,
                                'data' => ['policy_id'=> $existingAcceptanceRecord->policy->policy_id, 'version'  => $existingAcceptanceRecord->policy->version],
                                'message' => 'Acceptance record for latest policy version deleted',
                                'errors' => null,
                            ),
                            RESPONSE_CODE_ERROR_NOPERMISSION
                        );
                }
                else
                {
                    return response()
                        ->json(
                            array(
                                'status' => false,
                                'code' => RESPONSE_CODE_ERROR_NOTFOUND,
                                'data' => null,
                                'message' => 'No record found',
                            ),
                            RESPONSE_CODE_ERROR_NOTFOUND
                        );
                }
            }
        }
        else
        {
            return response()
                ->json(
                    array(
                        'status' => false,
                        'code' => RESPONSE_CODE_ERROR_UNAUTHORIZED,
                        'data' => null,
                        'message' => 'You dont have permission to perform this action',
                    ),
                    RESPONSE_CODE_ERROR_UNAUTHORIZED
                );
        }
    }
}
