<?php

namespace App\Http\Middleware;

use Closure;

class Checkpermission
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
        // $user = auth('api')->user();

        // $methodName = $request->getMethod();
        // $pathInfo = $request->getPathInfo();
        // $routeName = app()->router->getRoutes()[$methodName . $pathInfo]['action']['as'];
        
        // if($user->hasPermissionTo($routeName))
        // {
        //     return $next($request);
        // }else{
        //     return response()->json(['error' => true, 'message' => 'You are not authorized for this action.', 'data' => null], 401);
        // }
        return $next($request);
    }
}
