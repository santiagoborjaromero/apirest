<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Http;

class Microservicio  extends Controller
{
    public function sendHistorico($data, $token){
        $endpoint = env("ENDPOINT_DOCUMENTAL");

        $headers = [
            "Authorization" => "Bearer " . $token,
            "Content-Type" =>  "application/json"
        ];
        error_log(json_encode($headers));

        try{
            $response = Http::post($endpoint . "/api/v1/savecmd/", $data, $headers);
            if ($response->successful()) {
                $data = $response->json(); 
                error_log($data);
            } else {
                $statusCode = $response->status();
                $errorMessage = $response->body();
                error_log($statusCode);
                error_log($errorMessage);
            }
        }catch(Exception $err){
            error_log($err);
        }
    }
} 