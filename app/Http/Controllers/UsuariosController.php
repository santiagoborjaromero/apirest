<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsuariosRequest;
use App\Http\Requests\UpdateUsuariosRequest;
use App\Models\Usuario;
use Illuminate\Http\Response;
use Illuminate\Http\Request;


class UsuariosController extends Controller
{

    public function getAll(Request $request)
    {
        $payload = (Object)Controller::tokenSecurity($request);
        $payload_data = $payload->payload;
        $status = false;
        $data = [];
        $mensaje="";

        if ($payload->validate){
            $status = true;
            if ( $payload_data->idcliente === null){
                $data = Usuario::with("cliente", "roles", "roles.menu")->get();
            }else{
                $data = Usuario::where("idcliente", $payload->idcliente)->with("cliente", "roles", "roles.menu")->get();
            }
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }


        return Controller::reponseFormat($status, $data, $mensaje) ;
    }

    public function getOne(Request $request, $id)
    {
        $payload = (Object)Controller::tokenSecurity($request);
        $status = false;
        $data = [];
        $mensaje="";

        if ($payload->validate){
            $status = true;
            if (isset($id)){
                $status = true;
                $data = Usuario::where("idusuario", $id)->with("cliente", "roles")->get();
            }else{
                $data = [];
                $mensaje = "ID del usuario esta vacío";
            }
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }


}
