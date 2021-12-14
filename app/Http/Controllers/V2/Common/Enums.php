<?php

/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 16-04-2018
 * Time: 18:03
 */
namespace App\Http\Controllers\V2\Common;

use \Illuminate\Support\Facades\Validator;
use \Illuminate\Http\Request;

class Enums
{

    public function getEnumValuesByGroup(Request $request)
    {

        //Validate for user id and token
        $validator = Validator::make($request->all(),
            [
                'group_id' => 'required',
            ]
        );

        if ($validator->fails()) {
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
        } else {

            $returnData = array();
            $enumslist = \App\Models\FC_Enum::whereIn('group_id', explode(',', $request->input('group_id')))
                ->orderBy('group_id')
                ->get();


            foreach ($enumslist as $enumObj) {
                //for autocomplete only
                if (empty($enumObj))
                    $enumObj = new \App\Models\FC_Enum();

                //if it hasnt been previously set, set it now
                if (!isset($returnData['group_' . $enumObj->group_id]))
                    $returnData['group_' . $enumObj->group_id] = array();

                $returnData['group_' . $enumObj->group_id][] = $enumObj->getAPIObj(true);
            }


            if (empty($returnData)) {
                return response()
                    ->json(
                        array(
                            'status' => false,
                            'code' => RESPONSE_CODE_ERROR_NOTFOUND,
                            'data' => null,
                            'message' => 'No values were found! Maybe change the group id?',
                            'errors' => null
                        ),
                        RESPONSE_CODE_ERROR_NOTFOUND
                    );
            } else {
                return response()
                    ->json(
                        array(
                            'status' => true,
                            'code' => RESPONSE_CODE_SUCCESS_OK,
                            'data' => $returnData,
                            'message' => 'Successfully retrieved.',
                            'errors' => null,
                        ),
                        RESPONSE_CODE_SUCCESS_OK
                    );
            }

        }
    }

    public function getEnumValuesByName(Request $request)
    {

        //Validate for user id and token
        $validator = Validator::make($request->all(),
            [
                'name' => 'required',
            ]
        );

        if ($validator->fails()) {
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
        } else {

            $returnData = array();
            $enumslist = \App\Models\FC_Enum::whereIn('name', explode(',', $request->input('name')))
                ->orderBy('name')
                ->get();

            foreach ($enumslist as $enumObj) {
                //for autocomplete only
                if (empty($enumObj))
                    $enumObj = new \App\Models\FC_Enum();


                $returnData[] = $enumObj->getAPIObj(true);
            }


            if (empty($returnData)) {
                return response()
                    ->json(
                        array(
                            'status' => false,
                            'code' => RESPONSE_CODE_ERROR_NOTFOUND,
                            'data' => null,
                            'message' => 'No values were found! Maybe search for other names?',
                            'errors' => null
                        ),
                        RESPONSE_CODE_ERROR_NOTFOUND
                    );
            } else {
                return response()
                    ->json(
                        array(
                            'status' => true,
                            'code' => RESPONSE_CODE_SUCCESS_OK,
                            'data' => $returnData,
                            'message' => 'Successfully retrieved.',
                            'errors' => null,
                        ),
                        RESPONSE_CODE_SUCCESS_OK
                    );
            }

        }
    }

    public function getEnumValuesById(Request $request)
    {

        //Validate for user id and token
        $validator = Validator::make($request->all(),
            [
                'enum_id' => 'required',
            ]
        );

        if ($validator->fails()) {
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
        } else {

            $returnData = array();
            $enumslist = \App\Models\FC_Enum::whereIn('id', explode(',', $request->input('enum_id')))
                ->orderBy('id')
                ->get();

            foreach ($enumslist as $enumObj) {
                //for autocomplete only
                if (empty($enumObj))
                    $enumObj = new \App\Models\FC_Enum();


                $returnData[] = $enumObj->getAPIObj(true);
            }


            if (empty($returnData)) {
                return response()
                    ->json(
                        array(
                            'status' => false,
                            'code' => RESPONSE_CODE_ERROR_NOTFOUND,
                            'data' => null,
                            'message' => 'No values were found! Maybe change the id?',
                            'errors' => null
                        ),
                        RESPONSE_CODE_ERROR_NOTFOUND
                    );
            } else {
                return response()
                    ->json(
                        array(
                            'status' => true,
                            'code' => RESPONSE_CODE_SUCCESS_OK,
                            'data' => $returnData,
                            'message' => 'Successfully retrieved.',
                            'errors' => null,
                        ),
                        RESPONSE_CODE_SUCCESS_OK
                    );
            }

        }
    }

    public function getPhpInfo()
    {
        phpinfo();
    }
}
