<?php

namespace App\Http\Controllers;

use App\Models\TemplateComandos;
use App\Http\Requests\StoreTemplateComandosRequest;
use App\Http\Requests\UpdateTemplateComandosRequest;
use Exception;
use Illuminate\Http\Request;

class TemplateComandosController extends Controller
{
    public function getAll(Request $request)
    {
        $payload = (Object)Controller::tokenSecurity($request);
        $status = false;
        $data = [];
        $mensaje="";

        if ($payload->validate){
            $status = true;
            $data = TemplateComandos::where("idcliente", $payload->payload["idcliente"])->get();
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
                    $data = TemplateComandos::where("idcliente", $payload->payload["idcliente"])->get();
                    break;
                case 'inactivos':
                    $data = TemplateComandos::onlyTrashed()->where("idcliente", $payload->payload["idcliente"])->get();
                    break;
                case 'todos':
                    $data = TemplateComandos::withTrashed()->where("idcliente", $payload->payload["idcliente"])->get();
                    break;
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
                $data = TemplateComandos::where("idtemplate_comando", $id)->get();
            }else{
                $data = [];
                $mensaje = "ID esta vacío";
            }
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }

    public function save(Request $request)
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
            $record =  $request->input("data");

            $rs = TemplateComandos::where("idcliente", $payload->payload["idcliente"])
                ->where("linea_comando", $record["linea_comando"])
                ->get();

            if (count($rs)>0){
                $status = false;
                $mensaje = "El comando ingresado ya está registrado";
            } else {
                $record_g = [
                    "idcliente" => $payload->payload["idcliente"],
                    "linea_comando" => $record["linea_comando"],
                ];
                try{
                    $data = TemplateComandos::create($record_g);
                    $status = true;
                    
                } catch( Exception $err){
                    $status = false;
                    $mensaje = $err;
                }
                
                $aud->saveAuditoria([
                    "idusuario" => $payload->payload["idusuario"],
                    "json" => $record
                ]);
            }
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }


    public function update(Request $request, $id)
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
            if ($id !=""){
                $record =  $request->input("data");
                try{
                    $data = TemplateComandos::where("idtemplate_comando", $id)->update($record);
                    $status = true;
                    
                } catch( Exception $err){
                    $status = false;
                    $mensaje = $err;
                }

                $aud->saveAuditoria([
                    "idusuario" => $payload->payload["idusuario"],
                    "json" => $record
                ]);
            } else {
                $status = false;
                $mensaje = "ID se encuentra vacío";
            }
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }


    public function delete(Request $request, $id)
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
            if ($id !=""){
                $status = true;
                $data = [];
                TemplateComandos::where("idtemplate_comando", $id)->delete();
                $aud->saveAuditoria([
                    "idusuario" => $payload->payload["idusuario"],
                ]);
            } else {
                $status = false;
                $mensaje = "ID se encuentra vacío";
            }
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }

    public function recovery(Request $request, $id)
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
            if ($id !=""){
                $status = true;
                $data = [];
                TemplateComandos::where("idtemplate_comando", $id)->restore();
                $aud->saveAuditoria([
                    "idusuario" => $payload->payload["idusuario"],
                ]);
            } else {
                $status = false;
                $mensaje = "ID se encuentra vacío";
            }
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }
}
