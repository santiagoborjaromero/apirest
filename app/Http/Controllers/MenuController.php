<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use Exception;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\Cast\Object_;

class MenuController extends Controller
{
    


    public function getAll(Request $request)
    {
        $status = false;
        $data = [];
        $mensaje = "";
        $payload = (Object) Controller::tokenSecurity($request);
        if ($payload->validate){
            $status = true;
            $data = Menu::All();
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
        $mensaje = "" ;

        $payload = (Object)Controller::tokenSecurity($request);
        if ($payload->validate){
            $status = false;
            $data = "";
            if (isset($id)){
                $status = true;
                $data = Menu::where("idmenu", "=", $id)->get();
            }else{
                $mensaje = "ID esta vacío";
            }
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }

   

    public function saveNew(Request $request)
    {
        $aud = new AuditoriaUsoController();
        
        $status = false;
        $data = [];
        $mensaje = "";

        $payload = (Object) Controller::tokenSecurity($request);
        if (!$payload->validate){
            $status = false;
            $mensaje = $payload->mensaje;
        }else{
            $record = $request->input("data");
            // $record = [];
            // $record["orden"] =  $request->input("orden");
            // $record["nombre"] =  $request->input("nombre");
            // $record["icono"] =  $request->input("icono");
            // $record["ruta"] =  $request->input("ruta");
            // $record["es_submenu"] =  $request->input("es_submenu");
            // $record["estado"] =  $request->input("estado");
            try{
                $data = Menu::create($record);
                $status = true;
                $aud->saveAuditoria([
                    "idusuario" => $payload->payload["idusuario"],
                    "json" => $record,
                    "descripcion" => "Creación de Menu de Opciones"
                ]);
            } catch( Exception $err){
                $status = false;
                $mensaje = $err;
            }
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }




    public function delete($id)
    {
        $status = false;
        $data = [];
        $mensaje = "";
        if (isset($id)){
            $status = true;
            $data = Menu::where("idmenu", "=", $id)->get();
            if (count($data)>0){
                Menu::where("idmenu", "=", $id)->delete();
            }
        }else{
            $mensaje = "ID esta vacío";
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }



}
