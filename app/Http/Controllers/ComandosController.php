<?php

namespace App\Http\Controllers;

use App\Models\Comandos;
use App\Models\Servidores;
use App\Models\Usuario;
use Carbon\Exceptions\Exception;
use Illuminate\Http\Request;

class ComandosController extends Controller
{
    public function getAll(Request $request)
    {
        $payload = (Object)Controller::tokenSecurity($request);
        $status = false;
        $data = [];
        $mensaje="";

        if ($payload->validate){
            $status = true;
            $data = Comandos::where("idcliente", $payload->payload["idcliente"])->get();
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }
}