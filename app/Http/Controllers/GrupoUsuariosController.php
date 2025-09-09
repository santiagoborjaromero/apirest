<?php

namespace App\Http\Controllers;

use App\Models\GrupoUsuarios;
use App\Http\Requests\StoreGrupoUsuariosRequest;
use App\Http\Requests\UpdateGrupoUsuariosRequest;
use App\Models\RolMenuGrupos;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\VarDumper\VarDumper;

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
                $data = GrupoUsuarios::with("scripts","scripts.cmds")->where("idcliente", $payload->payload["idcliente"])->get();
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
                    $data = GrupoUsuarios::with("cliente", "usuarios", "scripts", "rolmenugrupos.rolmenu.menu")->where("idcliente", $payload->payload["idcliente"])->get();
                    break;
                case 'inactivos':
                    $data = GrupoUsuarios::onlyTrashed()->with("cliente", "scripts", "usuarios", "rolmenugrupos.rolmenu.menu")->where("idcliente", $payload->payload["idcliente"])->get();
                    break;
                case 'todos':
                    $data = GrupoUsuarios::withTrashed()->with("cliente", "scripts", "usuarios", "rolmenugrupos.rolmenu.menu")->where("idcliente", $payload->payload["idcliente"])->get();
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
            $data = GrupoUsuarios::with("cliente", "usuarios", "scripts", "rolmenugrupos.rolmenu.menu")->where("idgrupo_usuario", $id)->get();
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
            $record_g = [];
            $record_g["idcliente"] =  $payload->payload["idcliente"];
            $record_g["nombre"] =  $request->input("nombre");
            // $record_g["idgrupo"] =  $request->input("idgrupo");
            $record_g["idscript_creacion"] =  $request->input("idscript_creacion");

            $rolmenugrupos =  $request->input("rolmenugrupos");
            try{
                $data = GrupoUsuarios::create($record_g);
                $status = true;
                
            } catch( Exception $err){
                $status = false;
                $mensaje = $err;
            }

            $record = [];
            foreach ($rolmenugrupos as $key => $value) {
                $record[] = [
                    "idgrupo_usuario" => $data["idgrupo_usuario"],
                    "idrol_menu" => $value["idrol_menu"],
                    "scope" => $value["scope"],
                ];
            }

            try{
                $data = DB::table("rolmenu_grupos")->insert($record);
                $status = true;
            } catch( Exception $err){
                $status = false;
                $mensaje = $err;
            }
            
            $aud->saveAuditoria([
                "idcliente" => $payload->payload["idcliente"],
                "idusuario" => $payload->payload["idusuario"],
                "json" => [
                    "grupo_usuario" => $record_g,
                    "rolmenu_grupos" => $record,
                    "descripcion" => "Actualización de Grupo de Usuario"
                ]
            ]);


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
                $record_g = [];
                $record_g["idcliente"] =  $payload->payload["idcliente"];
                $record_g["nombre"] =  $request->input("nombre");
                // $record_g["idgrupo"] =  $request->input("idgrupo");
                $record_g["idscript_creacion"] =  $request->input("idscript_creacion");
    
                $rolmenugrupos =  $request->input("rolmenugrupos");
                try{
                    $data = GrupoUsuarios::where("idgrupo_usuario", $id)->update($record_g);
                    $status = true;
                    
                } catch( Exception $err){
                    $status = false;
                    $mensaje = $err;
                }

                RolMenuGrupos::where("idgrupo_usuario", $id)->delete();
    
                $record = [];
                foreach ($rolmenugrupos as $key => $value) {
                    $record[] = [
                        "idgrupo_usuario" => $id,
                        "idrol_menu" => $value["idrol_menu"],
                        "scope" => $value["scope"],
                    ];
                }
    
                try{
                    $data = DB::table("rolmenu_grupos")->insert($record);
                    $status = true;
                } catch( Exception $err){
                    $status = false;
                    $mensaje = $err;
                }
                
                $aud->saveAuditoria([
                    "idcliente" => $payload->payload["idcliente"],
                    "idusuario" => $payload->payload["idusuario"],
                    "json" => [
                        "grupo_usuario" => $record_g,
                        "rolmenu_grupos" => $record,
                        "descripcion" => "Actualización de Grupo de Usuario"
                    ]
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
                GrupoUsuarios::where("idgrupo_usuario", $id)->delete();
                RolMenuGrupos::where("idgrupo_usuario", $id)->delete();
                $aud->saveAuditoria([
                    "idcliente" => $payload->payload["idcliente"],
                    "idusuario" => $payload->payload["idusuario"],
                    "descripcion" => "Eliminado de Grupo de Usuario"
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
                GrupoUsuarios::where("idgrupo_usuario", $id)->restore();
                $aud->saveAuditoria([
                    "idcliente" => $payload->payload["idcliente"],
                    "idusuario" => $payload->payload["idusuario"],
                    "descripcion" => "Recuperación de Grupo de Usuario"
                ]);
            } else {
                $status = false;
                $mensaje = "ID se encuentra vacío";
            }
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }

}
