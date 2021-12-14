<?php

namespace App\Http\Controllers\Workout;

use App\Models\FB_User;
use App\Models\FB_Note_Doc;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

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

    function viewDocNote(Request $request)
    {
        $NotesToSearch = FB_Note_Doc::where('FB_User_id','=',$request->FB_Doctor_UserID);
        if($request->filled('FB_Workout_id'))
            {
                $NotesToSearch->where('FB_Workout_id','=',$request->FB_Workout_id);
                    
            }else{
                $NotesToSearch->where('FB_Workout_id','=',null);
            }
        $NotesToSearch = $NotesToSearch->orderBy('is_pinned', 'DESC');
        $NotesToSearch = $NotesToSearch->orderBy('id', 'DESC');
        $NotesToSearch = $NotesToSearch->get();
        
        $responseResult = [];
        foreach ($NotesToSearch as $note){
            $currentUser = FB_User::where('id', $note->FB_Doctor_UserID)->first();
            $note['FB_Doctor_User_Name'] = $currentUser->name;
            array_push($responseResult,$note);

        }

        return response()->json(
            array(
                'status' => true,
                'code' => RESPONSE_CODE_SUCCESS_OK,
                'data' => $responseResult,
                'message' => 'User found successfully',
                'errors' => null,
            ), RESPONSE_CODE_SUCCESS_OK
    );

       
    }


    function docAddNote(Request $request)
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
    
            if($currentUser->id)
            {
                $FB_Note = new \App\Models\FB_Note_Doc();
                $FB_Note->FB_User_id = $request->FB_Doctor_UserID;
                $FB_Note->FB_Workout_id = $request->FB_Workout_id;
                $FB_Note->updated_at = new \DateTime();
                $FB_Note->note = $request->input('note');
                $FB_Note->FB_Notes_Category = $request->FB_Notes_Category;
                $FB_Note->FB_Doctor_UserID =  $currentUser->id;
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
                    if(empty($FB_Note->note))
                        $FB_Note->note = '';
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

    function docEditNote(Request $request)
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
    
            if($currentUser->id)
            {    $FB_Note = \App\Models\FB_Note_Doc::find($request->input('note_id'));
                if(!empty($FB_Note))
                {
                    $FB_Note->note = $request->input('note','');
                    $FB_Note->updated_at = new \DateTime();
                    $FB_Note->FB_Notes_Category = $request->FB_Notes_Category;
                    $FB_Note->FB_Doctor_UserID = $currentUser->id;
                    if(empty($FB_Note->note))
                    $FB_Note->note = '';
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
    
    function docDeleteNote(Request $request)
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
        
            if($currentUser->id)
            {
                $FB_Note = \App\Models\FB_Note_Doc::find($request->input('note_id'));
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
    
    function pinnedDocNote(Request $request)
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
        
            if($currentUser->id)
            {
                $FB_Note = \App\Models\FB_Note_Doc::find($request->input('note_id'));
                if(!empty($FB_Note))
                {   
                    \App\Models\FB_Note_Doc::query()->update(['is_pinned' => 0]);
                    $FB_Note->is_pinned = $request->input('is_pinned','');
                    $FB_Note->update();
                    return response()->json(
                        array(
                            'status' => true,
                            'code' => RESPONSE_CODE_SUCCESS_OK,
                            'data' => $FB_Note,
                            'message' => 'Note pinned',
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
