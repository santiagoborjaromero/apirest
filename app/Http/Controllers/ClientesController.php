<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientesRequest;
use App\Http\Requests\UpdateClientesRequest;
use App\Http\Resources\ClientesCollection;
use App\Http\Resources\CustomCollection;
use App\Models\Cliente;
use App\Filters\ClientesFilter;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Request;

class ClientesController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function getAll(Request $request)
    {
        $status = false;
        $data = [];
        $mensaje = "";
        $payload = (Object) Controller::tokenSecurity($request);
        if ($payload->validate){
            $status = true;
            $data = Cliente::with("configuracion")->get();
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }

        return Controller::reponseFormat($status, $data, $mensaje) ;
    }

    public function getOne(Request $request,  $id)
    {
        $status = false;
        $data = [];
        $mensaje = "";
        $payload = (Object) Controller::tokenSecurity($request);
        if ($payload->validate){
            $status = false;
            if (isset($id)){
                $status = true;
                $data = Cliente::where("idcliente", "=", $id)->with("configuracion")->get();
            }else{
                $mensaje = "ID del cliente esta vacío";
            }
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }
        
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }

}
