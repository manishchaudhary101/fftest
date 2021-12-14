<?php

namespace App\Http\Controllers\Admin\Workout;

use App\Models\FB_User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class NotesController extends Controller
{
    function addNote(\App\Models\FB_Workout $workout , Request $request)
    {
        /** @var FB_User $currentUser */
        $currentUser = FB_User::where('id', $request->user_id)
                              ->where('status_enum', ENUM_STATUS_ACTIVE)
                              ->first();
    
        if($currentUser->userlevel_enum == ENUM_USERLEVEL_DOCTOR && $currentUser->authorizedUsers->where('id',$workout->created_by)->count() > 0)
        {
            $validation = Validator::make(
                $request->all(),
                [
                    'note'  => 'required|string|min:1'
                ]
            );
    
            if ($validation->fails())
            {
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
            }
            else
            {
                
                
                    $FB_Note = new \App\Models\FB_Note();
                    $FB_Note->FB_User_id = $currentUser->id;
                    $FB_Note->FB_Workout_id = $workout->id;
                    $FB_Note->note = $request->input('note');
                    $FB_Note->save();
            
                    return response()->json(
                        array(
                            'status' => true,
                            'code' => RESPONSE_CODE_SUCCESS_OK,
                            'data' => $FB_Note,
                            'message' => 'New note added',
                            'errors' => null,
                        ), RESPONSE_CODE_SUCCESS_OK
                    );
                
                
        
            }
        }
        else
        {
            return response()->json(
                array(
                    'status' => false,
                    'code' => RESPONSE_CODE_ERROR_NOPERMISSION,
                    'data' => null,
                    'message'   => 'You are not authorized for this action'
                ),
                RESPONSE_CODE_ERROR_NOPERMISSION);
        }
        
    }
    
    function editNote(\App\Models\FB_Workout $workout , Request $request)
    {
        /** @var FB_User $currentUser */
        $currentUser = FB_User::where('id', $request->user_id)
                              ->where('status_enum', ENUM_STATUS_ACTIVE)
                              ->first();
    
        if($currentUser->userlevel_enum == ENUM_USERLEVEL_DOCTOR && $currentUser->authorizedUsers->where('id',$workout->created_by)->count() > 0)
        {
            $validation = Validator::make(
                $request->all(),
                [
                    'note_id'  => 'required',
                    'note'  => 'required|string|min:1'
                ]
            );
    
            if ($validation->fails())
            {
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
            }
            else
            {
                
                    $FB_Note = \App\Models\FB_Note::find($request->input('note_id'));
                    if(!empty($FB_Note))
                    {
                        $FB_Note->note = $request->input('note');
                        $FB_Note->update();
                        return response()->json(
                            array(
                                'status' => true,
                                'code' => RESPONSE_CODE_SUCCESS_OK,
                                'data' => $FB_Note,
                                'message' => 'Note updated',
                                'errors' => null,
                            ), RESPONSE_CODE_SUCCESS_OK
                        );
                    }else
                    {
                        return response()->json(
                            array(
                                'status' => false,
                                'code' => RESPONSE_CODE_ERROR_BAD,
                                'data' => null,
                                'message' => 'Invalid Note',
                                'errors' => null,
                            ),
                            RESPONSE_CODE_ERROR_BAD
                        );
                    }
                
                
        
        
        
            }
        }
        else
        {
            return response()->json(
                array(
                    'status' => false,
                    'code' => RESPONSE_CODE_ERROR_NOPERMISSION,
                    'data' => null,
                    'message'   => 'You are not authorized for this action'
                ),
                RESPONSE_CODE_ERROR_NOPERMISSION);
        }
        
    }
    
    function deleteNote(\App\Models\FB_Workout $workout , Request $request)
    {
        /** @var FB_User $currentUser */
        $currentUser = FB_User::where('id', $request->user_id)
                              ->where('status_enum', ENUM_STATUS_ACTIVE)
                              ->first();
    
        if($currentUser->userlevel_enum == ENUM_USERLEVEL_DOCTOR && $currentUser->authorizedUsers->where('id',$editableWorkout->created_by)->count() > 0)
        {
    
            $validation = Validator::make(
                $request->all(),
                [
                    'note_id'  => 'required',
                ]
            );
    
            if ($validation->fails())
            {
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
            }
            else
            {
                
                
                    $FB_Note = \App\Models\FB_Note::find($request->input('note_id'));
                    if(!empty($FB_Note))
                    {
                        $FB_Note->delete();
                        return response()->json(
                            array(
                                'status' => true,
                                'code' => RESPONSE_CODE_SUCCESS_OK,
                                'data' => $FB_Note,
                                'message' => 'Note deleted',
                                'errors' => null,
                            ), RESPONSE_CODE_SUCCESS_OK
                        );
                    }else
                    {
                        return response()->json(
                            array(
                                'status' => false,
                                'code' => RESPONSE_CODE_ERROR_BAD,
                                'data' => null,
                                'message' => 'Invalid Note',
                                'errors' => null,
                            ),
                            RESPONSE_CODE_ERROR_BAD
                        );
                    }
                
                
        
        
        
            }
        }
        else
        {
            return response()->json(
                array(
                    'status' => false,
                    'code' => RESPONSE_CODE_ERROR_NOPERMISSION,
                    'data' => null,
                    'message'   => 'You are not authorized for this action'
                ),
                RESPONSE_CODE_ERROR_NOPERMISSION);
        }
        
    }
}
