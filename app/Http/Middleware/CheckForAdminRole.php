<?php

namespace App\Http\Middleware;

use App\Models\FB_User;
use Closure;

class CheckForAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $adminUser = FB_User::where('id',$request->input('user_id'))->where('userlevel_enum','>=',ENUM_USERLEVEL_DOCTOR)
            ->first();
        if(empty($adminUser))
        {
            return response()->json(
                array(
                    'status' => false,
                    'code' => RESPONSE_CODE_ERROR_NOPERMISSION,
                    'data' => null,
                    'message' => 'Not Authorized!',
                ), RESPONSE_CODE_ERROR_NOPERMISSION
            );
        }
        else
        {
            return $next($request);
        }
        
    }
}
