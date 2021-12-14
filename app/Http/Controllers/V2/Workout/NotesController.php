<?php

namespace App\Http\Controllers\v2\Workout;

use App\Models\FB_User;
use Illuminate\Http\Request;
use App\Notifications\V1\Workout\WorkoutEdited;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use phpDocumentor\Reflection\DocBlock\Tags\InvalidTag;

class NotesController extends Controller
{
    function addNote(\App\Models\FB_Workout $workout , Request $request)
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
            $currentUser = FB_User::where('id', $request->user_id)
                                  ->where('status_enum', ENUM_STATUS_ACTIVE)
                                  ->first();
    
            if($workout->created_by == $currentUser->id)
            {
                $FB_Note = new \App\Models\FB_Note();
                $FB_Note->FB_User_id = $currentUser->id;
                $FB_Note->FB_Workout_id = $workout->id;
                $workout->updated_at = new \DateTime();
                $FB_Note->note = $request->input('note');
                $FB_Note->source_platform = $request->source_platform;
                if($request->source_platform == 0){
                    $workout->is_synced_with_app = 0;   
                }
                if($request->source_platform == 0){
                    $workout->source_platform = 0;   
                }
                $workout->updated_source = $request->source_platform;
                $FB_Note->save();
                $workout->save();
                $currentUser->notify(new WorkoutEdited($workout));
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
            else
            {
                return response()->json(
                    array(
                        'status' => false,
                        'code' => RESPONSE_CODE_ERROR_BAD,
                        'data' => null,
                        'message' => 'Invalid Workout',
                        'errors' => null,
                    ),
                    RESPONSE_CODE_ERROR_BAD
                );
            }
            
        }
    }
    
    function editNote(\App\Models\FB_Workout $workout , Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'note_id'  => 'required',
                'note'  => 'sometimes'
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
            $currentUser = FB_User::where('id', $request->user_id)
                                  ->where('status_enum', ENUM_STATUS_ACTIVE)
                                  ->first();
    
            if($workout->created_by == $currentUser->id)
            {
                $FB_Note = \App\Models\FB_Note::find($request->input('note_id'));
                if(!empty($FB_Note))
                {
                    $FB_Note->note = $request->input('note','');
                    $FB_Note->source_platform = $request->source_platform;
                    $workout->updated_at = new \DateTime();
                    if($request->source_platform == 0){
                        $workout->is_synced_with_app = 0;   
                    }
                    if($request->source_platform == 0){
                        $workout->source_platform = 0;   
                    }
                    $workout->updated_source = $request->source_platform;
                    if(empty($FB_Note->note))
                    $FB_Note->note = '';
                    $FB_Note->update();
                    $workout->update();
                    $currentUser->notify(new WorkoutEdited($workout));
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
            else
            {
                return response()->json(
                    array(
                        'status' => false,
                        'code' => RESPONSE_CODE_ERROR_BAD,
                        'data' => null,
                        'message' => 'Invalid Workout',
                        'errors' => null,
                    ),
                    RESPONSE_CODE_ERROR_BAD
                );
            }
            
            
            
        }
    }
    
    function deleteNote(\App\Models\FB_Workout $workout , Request $request)
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
            $currentUser = FB_User::where('id', $request->user_id)
                                  ->where('status_enum', ENUM_STATUS_ACTIVE)
                                  ->first();
        
            if($workout->created_by == $currentUser->id)
            {
                $FB_Note = \App\Models\FB_Note::find($request->input('note_id'));
                $FB_Note->source_platform = $request->source_platform;
                if($request->source_platform == 0){
                    $workout->is_synced_with_app = 0;   
                }
                if($request->source_platform == 0){
                    $workout->source_platform = 0;   
                }
                $workout->updated_at = new \DateTime();
                $workout->updated_source = $request->source_platform;
                $workout->update();
                $currentUser->notify(new WorkoutEdited($workout));
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
            else
            {
                return response()->json(
                    array(
                        'status' => false,
                        'code' => RESPONSE_CODE_ERROR_BAD,
                        'data' => null,
                        'message' => 'Invalid Workout',
                        'errors' => null,
                    ),
                    RESPONSE_CODE_ERROR_BAD
                );
            }
        
        
        
        }
    }
}
