<?php

namespace App\Http\Middleware;

use Closure;
use App\LoginToken;

class Member
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
        if(empty($_GET['token']) || empty($request->bearerToken())) {
            return response()->json([
                'message' => 'unauthorized user'
            ], 401);
        }

        $check1 = LoginToken::whereToken($_GET['token'])->doesntExist();
        $check2 = LoginToken::whereToken($request->bearerToken())->doesntExist();

        if($check1 || $check2) {
            return response()->json([
                'message' => 'unauthorized user'
            ], 401);
        }

        return $next($request);
    }
}
