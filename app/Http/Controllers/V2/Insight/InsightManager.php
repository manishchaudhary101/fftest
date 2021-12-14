<?php


namespace App\Http\Controllers\V2\Insight;

use App\Models\FB_Community_post;
use App\Models\FB_Insight;
use App\Models\FB_User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InsightManager
{
    public function search(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'start_date' => 'date_format:'.DEFAULT_DATERANGE_INPUT_FORMAT.'|required_with:end_date',
            'end_date' => 'date_format:'.DEFAULT_DATERANGE_INPUT_FORMAT.'|required_with:start_date',
            'items_per_page' => 'integer|min:5',
            'category' => 'integer',
            'keyword' => 'string|min:3',
        ]);

        if($validation->fails()){
            return response()->json(
                array(
                    'status' => false,
                    'code' => RESPONSE_CODE_ERROR_BAD,
                    'data' => null,
                    'message' => 'Required fields missing',
                    'errors' => $validation->errors(),
                ), RESPONSE_CODE_ERROR_BAD
            );
        }else{

            $insights = FB_Insight::where('created_by',$request->user_id);

            if($request->filled('start_date'))
            {
                $start_date = \DateTime::createFromFormat(DEFAULT_DATERANGE_INPUT_FORMAT,$request->input('start_date'));
                $end_date = \DateTime::createFromFormat(DEFAULT_DATERANGE_INPUT_FORMAT,$request->input('end_date'));

                if(!empty($start_date) && !empty($end_date))
                {
                    $insights->whereBetween('created_at',[$start_date,$end_date]);
                }
                else
                {
                    return response()->json(
                        array(
                            'status' => false,
                            'code' => RESPONSE_CODE_ERROR_BAD,
                            'data' => null,
                            'message' => 'Start / End date format is incorrect',
                            'errors' => null,
                        ), RESPONSE_CODE_ERROR_BAD
                    );
                }
            }

            if($request->filled('keyword'))
            {
                $insights->where('title','LIKE',"%".$request->input('keyword')."%");
            }

            if($request->filled('category'))
            {
                $insights->where('category',$request->input('category'));
            }
            if($request->filled('with_workouts'))
            {
                $insights->with('hasManyWorkouts');
            }else
            {
                $insights->with(['hasManyWorkouts' => function($q){
                    $q->select('FB_Workout_id');
                }]);
            }

            $itemsPerView = DEFAULT_INSIGHTS_PER_PAGE;
            if ($request->filled('items_per_page') && (int)$request->input('items_per_page') > 0) {
                $itemsPerView = (int)$request->input('items_per_page');
            }

            $postsResults = $insights->orderBy('created_at', 'DESC')->paginate($itemsPerView);

            if (!empty($postsResults)) {
                return response()->json(
                    array(
                        'status' => true,
                        'code' => RESPONSE_CODE_SUCCESS_OK,
                        'data' => $postsResults,
                        'message' => 'Insights found successfully',
                        'errors' => null,
                    ), RESPONSE_CODE_SUCCESS_OK
                );
            } else {
                return response()->json(
                    array(
                        'status' => false,
                        'code' => RESPONSE_CODE_SUCCESS_OK,
                        'data' => null,
                        'message' => 'No records found!',
                        'errors' => null,
                    ), RESPONSE_CODE_SUCCESS_OK
                );
            }
        }
    }

}
