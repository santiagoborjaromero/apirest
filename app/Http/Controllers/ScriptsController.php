<?php

namespace App\Http\Controllers;

use App\Models\Scripts;
use App\Http\Requests\StoreScriptsRequest;
use App\Http\Requests\UpdateScriptsRequest;
use App\Models\ScriptComandos;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScriptsController extends Controller
{
   public function getAll(Request $request)
    {
        $payload = (Object)Controller::tokenSecurity($request);
        $status = false;
        $data = [];
        $mensaje="";

        if ($payload->validate){
            $status = true;
            $data = Scripts::where("idcliente", $payload->payload["idcliente"])
                ->with("cmds")->get();
            unset($data->token);
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
                    $data = Scripts::with("cmds")->where("idcliente", $payload->payload["idcliente"])->get();
                    break;
                case 'inactivos':
                    $data = Scripts::onlyTrashed()->with("cmds")->where("idcliente", $payload->payload["idcliente"])->get();
                    break;
                case 'todos':
                    $data = Scripts::withTrashed()->with("cmds")->where("idcliente", $payload->payload["idcliente"])->get();
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
        $status = false;
        $data = [];
        $mensaje = "";
        $payload = (Object) Controller::tokenSecurity($request);
        if ($payload->validate){
            $status = true;
            $data = Scripts::with("cmds")->where("idscript",$id)->get();
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }

    public function save(Request $request)
    {
        $payload = (Object)Controller::tokenSecurity($request);
        $status = false;
        $data = [];
        $mensaje="";

        if ($payload->validate){
            
            $record = $request->input("data") ;

            try{
                $record_scripts = [
                    "idcliente" => $payload->payload["idcliente"],
                    "nombre" => $request->input("nombre"),
                    "estado" => $request->input("estado"),
                ];
                $data = Scripts::create($record_scripts);
                
                $cmds = $request->input("cmds") ;
                $record_cmds = [];
                foreach ($cmds as $key => $value) {
                    $record_cmds[] = [
                        "idscript" => $data["idscript"],
                        "idtemplate_comando" => $value["idtemplate_comando"],
                    ];
                }

                $data_cmds = DB::table("script_comandos")->insert($record_cmds);
                $status = true;
            } catch (Exception $err){
                $status = false;
                $mensaje = "No pudo crear " . $err->getMessage();
            }
            
            $aud = new AuditoriaUsoController();
            $aud->saveAuditoria([
                "idcliente" => $payload->payload["idcliente"],
                "idusuario" => $payload->payload["idusuario"],
                "json" => $record,
                "descripcion" => "Creación de Scripts"
            ]);
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }

    public function update(Request $request, $id)
    {
        $payload = (Object)Controller::tokenSecurity($request);
        $status = false;
        $data = [];
        $mensaje="";

        if ($payload->validate){
            if ($id !=""){
                $record = $request->input();
                try{
                    $record_scripts = [
                        "idcliente" => $payload->payload["idcliente"],
                        "nombre" => $record["nombre"],
                        "estado" => $record["estado"]
                    ];
                    $data = Scripts::where("idscript", $id)->update($record_scripts);
                    
                    $cmds = $record["cmds"] ;
                    $record_cmds = [];
                    $orden = 0;
                    foreach ($cmds as $key => $value) {
                        $orden++;
                        $record_cmds[] = [
                            "idscript" => $id,
                            "orden" => $orden,
                            "idtemplate_comando" => $value["idtemplate_comando"],
                        ];
                    }
                    $data_del = ScriptComandos::where("idscript", $id)->delete();
                    $data_cmds = DB::table("script_comandos")->insert($record_cmds);
                    $status = true;
                } catch (Exception $err){
                    $status = false;
                    $mensaje = "No pudo crear " . $err->getMessage();
                }
                
                $aud = new AuditoriaUsoController();
                $aud->saveAuditoria([
                    "idcliente" => $payload->payload["idcliente"],
                    "idusuario" => $payload->payload["idusuario"],
                    "json" => $record,
                    "descripcion" => "Actualización de Scripts"
                ]);
            }else{
                $status = false;
                $mensaje = "ID se encuentra vacío";
            }
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }

    public function delete(Request $request, $id)
    {
        $status = false;
        $data = [];
        $mensaje = "";
        $payload = (Object) Controller::tokenSecurity($request);
        if ($payload->validate){
            if ($id !=""){
                $status = true;
                $data = ScriptComandos::where("idscript", $id)->delete();
                $data = Scripts::where("idscript",$id)->delete();
            } else { 
                $status = false;
                $mensaje = "ID se encuentra vacío";
            }
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }

    // public function recovery(Request $request, $id)
    // {
    //     $aud = new AuditoriaUsoController();
        
    //     $status = false;
    //     $data = [];
    //     $mensaje = "";

    //     $payload = (Object) Controller::tokenSecurity($request);
    //     if (!$payload->validate){
    //         $status = false;
    //         $mensaje = $payload->mensaje;
    //     }else{
    //         if ($id !=""){
    //             $status = true;
    //             $data = [];
    //             Scripts::where("idscript", $id)->restore();
    //             ScriptComandos::where("idscript", $id)->restore();
    //             $aud->saveAuditoria([
    //                 "idusuario" => $payload->payload["idusuario"],
    //             ]);
    //         } else {
    //             $status = false;
    //             $mensaje = "ID se encuentra vacío";
    //         }
    //     }
    //     return Controller::reponseFormat($status, $data, $mensaje) ;
    // }
}
