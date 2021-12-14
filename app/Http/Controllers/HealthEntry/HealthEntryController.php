<?php

namespace App\Http\Controllers\HealthEntry;

use App\Models\FB_Health_Category;
use App\Models\FB_Health_Tag_Type;
use App\Models\FB_Health_Units;
use App\Models\FB_User;
use App\Models\HealthEntry;
use App\Models\Health_Tags_Enum;
use App\Notifications\V1\HealthEntry\HealthEntryDeleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
                $health_entry->save();
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
            $categories = FB_Health_Category::select('id', 'name')->with(array('tags' => function ($query) {
                $query->select('id', 'name', 'tag_type_id', 'unit_id', 'category_id', 'priority', 'status');
            }))->get();
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

    public function getHealthEntry(Request $request)
    {
        $currentUser = FB_User::where('id', $request->user_id)
            ->where('status_enum', ENUM_STATUS_ACTIVE)
            ->first();

        if (!empty($currentUser)) {

            $healthEntry = HealthEntry::select('Health_Tags_Enum.name as tag_name', 'Health_Tags_Enum.tag_type_id', 'Health_Units_Enum.name as unit_name', 'Health_Category_Enum.name as category_name', 'Health_Units_Enum.unit_description as unit_description', 'HealthEntry.id', 'HealthEntry.category_id', 'HealthEntry.record_type', 'HealthEntry.tag_id'
                , 'HealthEntry.note', 'HealthEntry.quantity', 'HealthEntry.created_by', 'HealthEntry.time_zone_utc_offset', 'HealthEntry.tag_entry_time_epoch', 'HealthEntry.workout_id', 'HealthEntry.unit_id'
            )
                ->leftjoin('Health_Tags_Enum', 'HealthEntry.tag_id', '=', 'Health_Tags_Enum.id')
                ->leftjoin('Health_Units_Enum', 'HealthEntry.unit_id', '=', 'Health_Units_Enum.id')
                ->leftjoin('Health_Category_Enum', 'HealthEntry.category_id', '=', 'Health_Category_Enum.id')
                ->where('created_by', $currentUser->id)
                ->get();
            return response()->json(
                array(
                    'status' => true,
                    "version" => 1.0,
                    'code' => RESPONSE_CODE_SUCCESS_OK,
                    'data' => $healthEntry,
                    'message' => 'Health entry loaded',
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

    public function getHealthEntryRange(Request $request)
    {

        $currentUser = FB_User::where('id', $request->user_id)
            ->where('status_enum', ENUM_STATUS_ACTIVE)
            ->first();

        if (!empty($currentUser)) {

            $from = $request->secondUTC;
            $to = $request->firstUTC;
            $healthEntry = HealthEntry::whereBetween('start_time_utc', [$from, $to])
                ->leftjoin('Health_Tags_Enum', 'HealthEntry.tag_id', '=', 'Health_Tags_Enum.id')
                ->leftjoin('Health_Units_Enum', 'HealthEntry.unit_id', '=', 'Health_Units_Enum.id')
                ->select('Health_Tags_Enum.name as tagName', 'Health_Units_Enum.name as unitName', 'HealthEntry.*')
                ->where('created_by', $currentUser->id)
                ->get();
            return response()->json(
                array(
                    'status' => true,
                    "version" => 1.0,
                    'code' => RESPONSE_CODE_SUCCESS_OK,
                    'data' => $healthEntry,
                    'message' => 'Health entry loaded',
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

    public function deleteTag(Request $request)
    {

        $validation = Validator::make($request->all(), [
            'health_entry_id' => 'required',
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

            $currentUser = FB_User::where('id', $request->user_id)
                ->where('status_enum', ENUM_STATUS_ACTIVE)
                ->first();

            $totalTagsDeleted = 0;
            $deletedTagsIds = array();
            if (!empty($currentUser)) {
                $tagsIdsToDelete = json_decode($request->input('health_entry_id'));
                if (!empty($tagsIdsToDelete)) {
                    foreach ($tagsIdsToDelete as $tagid) {
                        $healthEntry = HealthEntry::where('id', '=', $tagid)
                            ->where('created_by', '=', $currentUser->id)
                            ->first();

                       if (!empty($healthEntry)) {

                            array_push($deletedTagsIds, $healthEntry->id);
                            $healthEntry->updated_source = $request->input('source_platform', 0);
                            $healthEntry->update();
                            $healthEntry->delete();
                            if($request->input('source_platform') == 0){
                            $currentUser->notify(new HealthEntryDeleted($healthEntry));
                            }
                            Log::debug('---notified---');
                            $totalTagsDeleted++;

                        }
                    }

                } else {
                    return response()->json(
                        array(
                            'status' => false,
                            'code' => RESPONSE_CODE_ERROR_BAD,
                            'data' => null,
                            'message' => 'Bad HealthEntryIDs structure',
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
                        'message' => 'User not found.',
                        'errors' => null,
                    ), RESPONSE_CODE_ERROR_BAD
                );
            }

            if (!empty($tagsIdsToDelete)) {
                return response()->json(
                    array(
                        'status' => true,
                        'code' => RESPONSE_CODE_SUCCESS_OK,
                        'data' => $deletedTagsIds,
                        'message' => 'Health Entry Deleted: ' . $totalTagsDeleted,
                        'errors' => null,
                    ), RESPONSE_CODE_SUCCESS_OK
                );
            } else {
                return response()->json(
                    array(
                        'status' => false,
                        'code' => RESPONSE_CODE_ERROR_BAD,
                        'data' => null,
                        'message' => 'something went wrong, Please try again',
                        'errors' => null,
                    ), RESPONSE_CODE_ERROR_BAD
                );
            }

        }
    }
}
