<?php
/**
 * Created by PhpStorm.
 * User: Prakhar sharma
 * Date: 30-05-2019
 * Time: 11:50
 */
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Response;

class Cors
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $allowed_http_origins = array(
            "https://api.travelfolk.i22.in",
            "https://chatserver.travelfolk.i22.in:9000",
            "http://localhost:8080",
        );

        $request_headers = apache_request_headers();
        if (isset($request_headers['Origin'])) {
            $http_origin = $request_headers['Origin'];

            if (in_array($http_origin, $allowed_http_origins)) {
                header("Access-Control-Allow-Origin: *");

                return $next($request);
            } else {
                return $next($request);
            }


        } else {
            return $next($request);
        }
    }

}