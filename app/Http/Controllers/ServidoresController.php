<?php

namespace App\Http\Controllers;

use App\Models\Servidores;
use App\Http\Requests\StoreServidoresRequest;
use App\Http\Requests\UpdateServidoresRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServidoresController extends Controller
{
    public function getAll(Request $request)
    {
        $payload = (Object)Controller::tokenSecurity($request);
        $status = false;
        $data = [];
        $mensaje="";

        if ($payload->validate){
            $status = true;
            $data = Servidores::where("idcliente", $payload->payload["idcliente"])
                ->with("cliente")->get();
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
                    $data = Servidores::with("cliente")->where("idcliente", $payload->payload["idcliente"])->get();
                    break;
                case 'inactivos':
                    $data = Servidores::onlyTrashed()->with("cliente")->where("idcliente", $payload->payload["idcliente"])->get();
                    break;
                case 'todos':
                    $data = Servidores::withTrashed()->with("cliente")->where("idcliente", $payload->payload["idcliente"])->get();
                    break;
            }
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }

    // public function getAllFromClient(Request $request, $id)
    // {
    //     $status = false;
    //     $data = [];
    //     $mensaje = "";
    //     $payload = (Object) Controller::tokenSecurity($request);
    //     if ($payload->validate){
    //         $status = true;
    //         $data = GrupoUsuarios::where("idcliente", $id)->get();
    //     }else{
    //         $status = false;
    //         $mensaje = $payload->mensaje;
    //     }
    //     return Controller::reponseFormat($status, $data, $mensaje) ;
    // }

    // public function getOne(Request $request, $id)
    // {
    //     $status = false;
    //     $data = [];
    //     $mensaje = "";
    //     $payload = (Object) Controller::tokenSecurity($request);
    //     if ($payload->validate){
    //         $status = true;
    //         $data = GrupoUsuarios::with("cliente", "usuarios", "rolmenugrupos.rolmenu.menu")->where("idgrupo_usuario", $id)->get();
    //     }else{
    //         $status = false;
    //         $mensaje = $payload->mensaje;
    //     }
    //     return Controller::reponseFormat($status, $data, $mensaje) ;
    // }

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
            $record_g = [];
            $record_g["idcliente"] =  $payload->payload["idcliente"];
            $record_g["nombre"] =  $request->input("nombre");
            $record_g["host"] =  $request->input("host");
            $record_g["puerto"] =  $request->input("puerto");
            $record_g["localizacion"] =  $request->input("localizacion");
            $record_g["idscript_nuevo"] =  $request->input("idscript_nuevo");

            $servidores =  $request->input("servidores");
            
            try{
                $data = Servidores::create($record_g);
                $status = true;
            } catch( Exception $err){
                $status = false;
                $mensaje = $err;
            }

            $record = [];
            foreach ($servidores as $key => $value) {
                $record[] = [
                    "idgrupo_usuario" => $data["idservidor"],
                    "idusuario" => $value["idusuario"],
                ];
            }

            try{
                $data = DB::table("servidor_usuarios")->insert($record);
                $status = true;
            } catch( Exception $err){
                $status = false;
                $mensaje = $err;
            }
            
            $aud->saveAuditoria([
                "idusuario" => $payload->payload["idusuario"],
                "json" => [
                    "servidor" => $record_g,
                    "servidor_usuarios" => $record
                ]
            ]);


        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }


    // public function update(Request $request, $id)
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
    //             $record_g = [];
    //             $record_g["idcliente"] =  $payload->payload["idcliente"];
    //             $record_g["nombre"] =  $request->input("nombre");
    //             $record_g["idgrupo"] =  $request->input("idgrupo");
    
    //             $rolmenugrupos =  $request->input("rolmenugrupos");
    //             try{
    //                 $data = GrupoUsuarios::where("idgrupo_usuario", $id)->update($record_g);
    //                 $status = true;
                    
    //             } catch( Exception $err){
    //                 $status = false;
    //                 $mensaje = $err;
    //             }

    //             RolMenuGrupos::where("idgrupo_usuario", $id)->delete();
    
    //             $record = [];
    //             foreach ($rolmenugrupos as $key => $value) {
    //                 $record[] = [
    //                     "idgrupo_usuario" => $id,
    //                     "idrol_menu" => $value["idrol_menu"],
    //                     "scope" => $value["scope"],
    //                 ];
    //             }
    
    //             try{
    //                 $data = DB::table("rolmenu_grupos")->insert($record);
    //                 $status = true;
    //             } catch( Exception $err){
    //                 $status = false;
    //                 $mensaje = $err;
    //             }
                
    //             $aud->saveAuditoria([
    //                 "idusuario" => $payload->payload["idusuario"],
    //                 "json" => [
    //                     "grupo_usuario" => $record_g,
    //                     "rolmenu_grupos" => $record
    //                 ]
    //             ]);
    //         } else {
    //             $status = false;
    //             $mensaje = "ID se encuentra vacío";
    //         }
    //     }
    //     return Controller::reponseFormat($status, $data, $mensaje) ;
    // }


    // public function delete(Request $request, $id)
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
    //             GrupoUsuarios::where("idgrupo_usuario", $id)->delete();
    //             RolMenuGrupos::where("idgrupo_usuario", $id)->delete();
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
    //             GrupoUsuarios::where("idgrupo_usuario", $id)->restore();
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
