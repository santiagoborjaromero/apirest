<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $validate = Controller::tokenSecurity($request);
        if (!$validate["validate"]){
            return response()->json(json_decode(Controller::reponseFormat(false, null, $validate["mensaje"]),true));
        }   
        return $next($request);
    }
}
