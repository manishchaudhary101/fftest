<?php

namespace App\Http\Controllers\V2\HealthEntry;

use App\Models\FB_Health_Category;
use App\Models\FB_Health_Tag_Type;
use App\Models\FB_Health_Units;
use App\Models\FB_User;
use App\Models\HealthEntry;
use Illuminate\Http\Request;
use App\Notifications\V1\HealthEntry\HealthEntryAdded;
use Illuminate\Support\Facades\Validator;

class HealthEntryController
{
    public function createHealthEntry(Request $request)
    {

        $validation = Validator::make(
            $request->all(),
            [
                'user_id' => 'required',
                'access_key' => 'required',
                'category_id' => 'required',
                'tag_id' => 'required',
                'start_time_utc' => 'required',
                'tag_entry_time_epoch' => 'required',
                'time_zone_utc_offset' => 'required',
                'source_platform'  => 'required',
            ]
        );

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

            $existRecord = HealthEntry::where('created_by', $request->user_id)
                ->where('tag_entry_time_epoch', $request->input('tag_entry_time_epoch'))
                ->first();
            if (empty($existRecord)) {
                $health_entry = new \App\Models\HealthEntry();
                $health_entry->created_by = $currentUser->id;
                $health_entry->category_id = $request->input('category_id');
                $health_entry->tag_id = $request->input('tag_id');
                $health_entry->created_at = $request->input('created_at');
                $health_entry->note = $request->input('note');
                $health_entry->quantity = $request->input('quantity');
                $health_entry->workout_id = $request->input('workout_id');
                $health_entry->unit_id = $request->input('unit_id');
                $health_entry->start_time_utc = $request->input('start_time_utc');
                $health_entry->tag_entry_time_epoch = $request->input('tag_entry_time_epoch');
                $health_entry->time_zone_utc_offset = $request->input('time_zone_utc_offset');
                $health_entry->created_at = \Carbon\Carbon::now()->toDateTimeString();
                $health_entry->updated_source = $request->input('source_platform', 0);
                $health_entry->save();
                if($request->input('source_platform') == 0){
                    $currentUser->notify(new HealthEntryAdded($health_entry));
                }
                $healthEntryData = HealthEntry::where('id', $health_entry->id)->first();
                $healthEntryData->created_at = date(DATE_ISO8601, strtotime($healthEntryData->created_at));
                return response()->json(
                    array(
                        'status' => true,
                        'code' => RESPONSE_CODE_SUCCESS_OK,
                        'healthEntry_id' => $health_entry->id,
                        'data' => $healthEntryData,
                        'message' => 'Health entry added',
                        'errors' => null,
                    ), RESPONSE_CODE_SUCCESS_OK
                );
            } else {
                $existRecord->created_at = date(DATE_ISO8601, strtotime($existRecord->created_at));
                return response()->json(
                    array(
                        'status' => true,
                        'code' => RESPONSE_CODE_SUCCESS_OK,
                        'healthEntry_id' => $existRecord->id,
                        'data' => $existRecord,
                        'message' => 'Record already exist',
                        'errors' => null,
                    ),
                    RESPONSE_CODE_SUCCESS_OK
                );
            }
        }
    }

    public function getHealthTags(Request $request)
    {
        $currentUser = FB_User::where('id', $request->user_id)
            ->where('status_enum', ENUM_STATUS_ACTIVE)
            ->first();

        if (!empty($currentUser)) {

            //all time stats
            $categories = FB_Health_Category::select('id', 'name')->orderBy('priority', 'ASC')->with(array('tags' => function ($query) {
                $query->select('id', 'name', 'tag_type_id', 'unit_id', 'category_id', 'priority', 'status')->where('status', 1)->orderBy('priority', 'ASC');
            }))->where('status', 1)->get();
            $tag_types = FB_Health_Tag_Type::select('id', 'name', 'status')->get();
            $unit = FB_Health_Units::select('id', 'name', 'unit_description', 'status')->get();
            return response()->json(
                array(
                    'status' => true,
                    "version" => 1.0,
                    'code' => RESPONSE_CODE_SUCCESS_OK,
                    'data' => array(
                        'categories' => $categories,
                        'tag_types' => $tag_types,
                        'unit' => $unit,
                    ),
                    'message' => 'Tags loaded',
                    'errors' => null,
                ), RESPONSE_CODE_SUCCESS_OK
            );
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
