<?php

namespace App\Http\Controllers;

use App\Models\Variables;
use App\Http\Requests\StoreVariablesRequest;
use App\Http\Requests\UpdateVariablesRequest;
use Illuminate\Http\Request;

class VariablesController extends Controller
{
   public function getAll(Request $request)
    {
        $payload = (Object)Controller::tokenSecurity($request);
        $status = false;
        $data = [];
        $mensaje="";

        if ($payload->validate){
            $status = true;
            $data = Variables::all();
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }
}
