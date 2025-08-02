<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
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
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])){
            error_log('POST PUT PATCH');
            if ($request->has('data')){
                $encryptedData = $request->input('data');
                error_log($encryptedData);
                $data = Controller::decode($encryptedData);
                error_log($data);
                $request->replace(json_decode($data,true));
            }else{
                error_log("No tiene Data");
            }
        }
        return $next($request);
    }
}
