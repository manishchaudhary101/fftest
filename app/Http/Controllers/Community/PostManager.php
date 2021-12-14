<?php


namespace App\Http\Controllers\Community;

use \App\Models\FB_Community_post;
use App\Models\FB_User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostManager
{

    public function search(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'start_date' => 'date_format:d-m-Y|required_with:end_date',
            'end_date' => 'date_format:d-m-Y|required_with:start_date',
            'month_year' => 'date_format:m-Y',
            'author_id' => 'integer|min:1',
            'items_per_page' => 'integer|min:1',
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

            $posts = FB_Community_post::with('hasManyTags')->whereNotNull('published_on');

            if($request->filled('start_date'))
            {
                $start_date = \DateTime::createFromFormat('d-m-Y',$request->input('start_date'));
                $end_date = \DateTime::createFromFormat('d-m-Y',$request->input('end_date'));

                if(!empty($start_date) && !empty($end_date))
                {
                    $posts->whereBetween('published_on',[$start_date,$end_date]);
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

            if($request->filled('month_year'))
            {
                $month_year = \DateTime::createFromFormat('m-Y',$request->input('month_year'));
                if(!empty($month_year))
                {
                    $posts->whereRaw('MONTH(published_on) = '.$month_year->format('m'));
                    $posts->whereRaw('YEAR(published_on) = '.$month_year->format('Y'));
                }
                else
                {
                    return response()->json(
                        array(
                            'status' => false,
                            'code' => RESPONSE_CODE_ERROR_BAD,
                            'data' => null,
                            'message' => 'Month-Year format is incorrect',
                            'errors' => null,
                        ), RESPONSE_CODE_ERROR_BAD
                    );
                }
            }

            if($request->filled('author_id'))
            {
                $posts->where('author_id',$request->input('author_id'));
            }

            if($request->filled('keyword'))
            {


                $posts->where(function($q) use ($request) {
                    $q->where('title','LIKE',"%".$request->input('keyword')."%")
                        ->orWhereHas('hasManyTags', function($q) use($request) {
                            $q->where('name', $request->input('keyword'));
                        });
                });
            }

            $itemsPerView = DEFAULT_COMMUNITY_POSTS_PER_PAGE;
            if ($request->filled('items_per_page') && (int)$request->input('items_per_page') > 0) {
                $itemsPerView = (int)$request->input('items_per_page');
            }

            $postsResults = $posts->orderBy('published_on', 'DESC')->paginate($itemsPerView);

            if (!empty($postsResults)) {
                return response()->json(
                    array(
                        'status' => true,
                        'code' => RESPONSE_CODE_SUCCESS_OK,
                        'data' => $postsResults,
                        'message' => 'Posts found successfully',
                        'errors' => null,
                    ), RESPONSE_CODE_SUCCESS_OK
                );
            } else {
                return response()->json(
                    array(
                        'status' => false,
                        'code' => RESPONSE_CODE_ERROR_BAD,
                        'data' => null,
                        'message' => 'Something went wrong!',
                        'errors' => null,
                    ), RESPONSE_CODE_ERROR_BAD
                );
            }
        }
    }

    public function createOrEdit(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'title' => 'string|required|min:3',
            'url' => 'url|required|min:3',
            'published_on' => 'date_format:d-m-Y H:i|required',
        ]);
        $currentUser = FB_User::where('id', $request->user_id)
            ->where('status_enum', ENUM_STATUS_ACTIVE)
            ->first();

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

            $communityPost = new \App\Models\FB_Community_post();
            if($request->filled('id'))
            {
                $communityPost = \App\Models\FB_Community_post::find($request->input('id'));
                if(empty($communityPost))
                {
                    return response()->json(
                        array(
                            'status' => false,
                            'code' => RESPONSE_CODE_ERROR_MISSINGDATA,
                            'data' => null,
                            'message' => 'Invalid ID',
                            'errors' => null,
                        ), RESPONSE_CODE_ERROR_MISSINGDATA
                    );
                }
            }

            if(empty($communityPost->id))
            {
                //its a new post
                $communityPost->author_id = $currentUser->id;
            }

            $communityPost->title = $request->input('title');
            $communityPost->url = $request->input('url');
            $communityPost->published_on = \DateTime::createFromFormat('d-m-Y H:i', $request->input('published_on'));
            $communityPost->save();

            if(empty($communityPost))
            {
                return response()->json(
                    array(
                        'status' => false,
                        'code' =>RESPONSE_CODE_ERROR_BAD,
                        'data' => null,
                        'message' => 'Oops! Couldnt save that!',
                        'errors' => null,
                    ), RESPONSE_CODE_ERROR_BAD
                );
            }
            else
            {
                return response()->json(
                    array(
                        'status' => true,
                        'code' => RESPONSE_CODE_SUCCESS_OK,
                        'data' => $communityPost,
                        'message' => 'Posts saved successfully',
                        'errors' => null,
                    ), RESPONSE_CODE_SUCCESS_OK
                );
            }
        }
    }


}
