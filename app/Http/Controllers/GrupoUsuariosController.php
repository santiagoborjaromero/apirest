<?php

namespace App\Http\Controllers;

use App\Models\GrupoUsuarios;
use App\Http\Requests\StoreGrupoUsuariosRequest;
use App\Http\Requests\UpdateGrupoUsuariosRequest;
use Illuminate\Http\Request;

class GrupoUsuariosController extends Controller
{
    public function getAll(Request $request)
    {
        $status = false;
        $data = [];
        $mensaje = "";
        $payload = (Object) Controller::tokenSecurity($request);
        if ($payload->validate){
            $status = true;
            if ($payload->payload["idcliente"] === null){
                $data = null;
            }else {
                $data = GrupoUsuarios::where("idcliente", $payload->payload["idcliente"])->get();
            }
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }

    public function getAllFiltro(Request $request, $accion)
    {
        $status = false;
        $data = [];
        $mensaje = "";
        $payload = (Object) Controller::tokenSecurity($request);
        if ($payload->validate){
            $status = true;
            switch($accion){
                case 'activos':
                    $data = GrupoUsuarios::with("cliente", "usuarios", "rolmenugrupos.rolmenu.menu")->where("idcliente", $payload->payload["idcliente"])->get();
                    break;
                case 'inactivos':
                    $data = GrupoUsuarios::onlyTrashed()->with("cliente", "usuarios", "rolmenugrupos.rolmenu.menu")->where("idcliente", $payload->payload["idcliente"])->get();
                    break;
                case 'todos':
                    $data = GrupoUsuarios::withTrashed()->with("cliente", "usuarios", "rolmenugrupos.rolmenu.menu")->where("idcliente", $payload->payload["idcliente"])->get();
                    break;
            }
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }

    public function getAllFromClient(Request $request, $id)
    {
        $status = false;
        $data = [];
        $mensaje = "";
        $payload = (Object) Controller::tokenSecurity($request);
        if ($payload->validate){
            $status = true;
            $data = GrupoUsuarios::where("idcliente", $id)->get();
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }

    public function getOne(Request $request, $id)
    {
        $status = false;
        $data = [];
        $mensaje = "";
        $payload = (Object) Controller::tokenSecurity($request);
        if ($payload->validate){
            $status = true;
            GrupoUsuarios::with("cliente", "usuarios", "rolmenugrupos.rolmenu.menu")->where("idgrupo_usuario", $id)->get();
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }
}
