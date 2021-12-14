<?php

namespace App\Http\Controllers\Admin;

use App\Models\FB_User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class Users extends Controller
{
    function getAllUserList(Request $request)
    {
        /** @var FB_User $currentUser */
        $currentUser = FB_User::where('id', $request->user_id)
            ->where('status_enum', ENUM_STATUS_ACTIVE)
            ->first();

        $allUsers = FB_User::with('role');
        if($currentUser->userlevel_enum < ENUM_USERLEVEL_ADMIN)
        {
            if($currentUser->authorizedUsers()->count() > 0 )
            {
                $allUsers->whereIn('id',$currentUser->authorizedUsers->pluck('id')->toArray());

            }
            else
            {
                return response()->json(
                    array(
                        'status' => false,
                        'code' => RESPONSE_CODE_ERROR_NOPERMISSION,
                        'data' => null,
                        'message'   => 'You dont have any authorized users'
                    ),
                    RESPONSE_CODE_ERROR_NOPERMISSION);
            }
        }

        if($request->filled('search'))
        {
            $allUsers->where('name','like','%'.$request->input('search').'%')->orWhere('email','like','%'. $request->input('search').'%');
        }
        
        return response()->json(
            array(
                'status' => true,
                'code' => RESPONSE_CODE_SUCCESS_OK,
                'data' => $allUsers->paginate(50)
            ),
            RESPONSE_CODE_SUCCESS_OK);

    }

    function editUser(FB_User $editableUser, Request $request)
    {
        /** @var FB_User $currentUser */
        $currentUser = FB_User::where('id', $request->user_id)
            ->where('status_enum', ENUM_STATUS_ACTIVE)
            ->first();

            $editableUser->loadMissing('role');
            if ($request->filled('name')) {
                $editableUser->name = $request->name;
            }

            if ($request->filled('role_id')) {
                if (in_array($request->role_id, [ENUM_USERLEVEL_ADMIN, ENUM_USERLEVEL_PREMIUM, ENUM_USERLEVEL_FHP_PREMIUM, ENUM_USERLEVEL_DOCTOR,ENUM_USERLEVEL_FHP_DOCTOR, ENUM_USERLEVEL_DEFAULT]))
                    $editableUser->userlevel_enum = $request->role_id;
            }

            if ($request->filled('gender_enum')) {
                if (in_array($request->gender, [ENUM_GENDER_MALE, ENUM_GENDER_FEMALE]))
                    $editableUser->gender_enum = $request->gender_enum;
            }

            if ($request->filled('height')) {
                $editableUser->height = $request->height;
            }

            if ($request->filled('weight')) {
                $editableUser->weight = $request->weight;
            }


            if ($request->filled('dob')) {
                $dob = \DateTime::createFromFormat('d-m-Y', $request->dob);
                if (!empty($dob))
                    $editableUser->dob = $dob;
            }

            if ($request->filled('password')) {
                $editableUser->password = password_hash($request->password, PASSWORD_DEFAULT);
            }


            if ($request->filled('arr_displayed')) {
                $editableUser->arr_displayed = $request->arr_displayed;
            }

            if ($request->filled('selected_unit')) {
                $editableUser->selected_unit = $request->selected_unit;
            }


        if($currentUser->userlevel_enum < ENUM_USERLEVEL_ADMIN)
        {
            
                return response()->json(
                    array(
                        'status' => false,
                        'code' => RESPONSE_CODE_ERROR_NOPERMISSION,
                        'data' => null,
                        'message'   => 'You are not authorized to edit this user'
                    ),
                    RESPONSE_CODE_ERROR_NOPERMISSION);
            
        }
        else
        {
            $editableUser->update();
            $editableUser->refresh();

            return response()->json(
                array(
                    'status' => true,
                    'code' => RESPONSE_CODE_SUCCESS_OK,
                    'data' => $editableUser->makeHidden(['api_token','userlevel_enum']),
                    'message' => 'User\'s information updated successfully.',
                    'errors' => null,
                ), RESPONSE_CODE_SUCCESS_OK
            );

        }



    }
}
