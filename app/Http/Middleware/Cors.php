<?php

namespace App\Http\Middleware;

use Closure;

class Cors
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
        // return $next($request)
        // ->header('Access-Control-Allow-Origin', '*')
        // ->header('Access-Control-Allow-Headers', '*')
        // ->header('Access-Control-Allow-Credentials', 'true')
        // ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

        // $headers = [
        //     'Access-Control-Allow-Origin'      => '*',
        //     'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE',
        //     'Access-Control-Allow-Credentials' => 'true',
        //     'Access-Control-Max-Age'           => '86400',
        //     'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With'
        // ];

        // if ($request->isMethod('OPTIONS'))
        // {
        //     return response()->json('{"method":"OPTIONS"}', 200, $headers);
        // }

        // $response = $next($request);
        // foreach($headers as $key => $value)
        // {
        //     $response->header($key, $value);
        // }

        // return $response;

        $allowedOrigins = ['192.168.1.131', 'example1.com', 'example2.com'];
        
        if($request->header('host')){
            if (in_array($request->header('host'), $allowedOrigins)) {
                return $next($request)
                    ->header('Access-Control-Allow-Origin', $request->header('host'))
                    ->header('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            }
          }

        return $next($request);

    }
}
