<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaUso;
use App\Http\Requests\StoreAuditoriaUsoRequest;
use App\Http\Requests\UpdateAuditoriaUsoRequest;
use App\Models\HistoricoCmd;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditoriaUsoController extends Controller
{
    public function saveAuditoria($data)
    {   
        $data["metodo"] = $_SERVER["REQUEST_METHOD"];
        $data["ruta"] = $_SERVER["REQUEST_URI"];
        $data["ipaddr"] = $this->get_client_ip();
        if (isset($data["json"])){
            $data["json"] = json_encode($data["json"]);
        }
        AuditoriaUso::create($data);
    }

    public function get_client_ip() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    public function getAuditoria(Request $request, string $b64) {
        $payload = (Object)Controller::tokenSecurity($request);
        $status = false;
        $data = [];
        $mensaje="";

        if ($payload->validate){

            $input = explode("|",base64_decode($b64));
            $idusuario = $input[0];
            $metodo = $input[1];
            $fechaini = $input[2];
            $fechafin = $input[3];

            error_log(base64_decode($b64));

            $status = true;
            if ($idusuario=="T" && $metodo == "T"){
                $data = AuditoriaUso::with("usuario")
                    ->where("idcliente", $payload->payload["idcliente"])
                    ->whereBetween("created_at",[$fechaini." 00:00:00", $fechafin." 23:59:59"])
                    ->get();
            }else  if ($idusuario!="T" && $metodo == "T"){
                $data = AuditoriaUso::with("usuario")
                    ->where("idcliente", $payload->payload["idcliente"])
                    ->where("idusuario", $idusuario)
                    ->whereBetween("created_at",[$fechaini." 00:00:00", $fechafin." 23:59:59"])
                    ->get();
            }else  if ($idusuario=="T" && $metodo != "T"){
                $data = AuditoriaUso::with("usuario")
                    ->where("idcliente", $payload->payload["idcliente"])
                    ->where("metodo", "=", $metodo)
                    ->whereBetween("created_at",[$fechaini." 00:00:00", $fechafin." 23:59:59"])
                    ->get();
            }else  if ($idusuario!="T" && $metodo != "T"){
                $data = AuditoriaUso::with("usuario")
                    ->where("idcliente", $payload->payload["idcliente"])
                    ->where("idusuario", $idusuario)
                    ->where("metodo", "=", $metodo)
                    ->whereBetween("created_at",[$fechaini." 00:00:00", $fechafin." 23:59:59"])
                    ->get();
            }
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }

    // public function getHCmd(Request $request) {
    //     $payload = (Object)Controller::tokenSecurity($request);
    //     $status = false;
    //     $data = [];
    //     $mensaje="";

    //     if ($payload->validate){
    //         $status = true;
    //         // $data = HistoricoCmd::where("idcliente", $payload->payload["idcliente"])->get()->toJson();
    //     }else{
    //         $status = false;
    //         $mensaje = $payload->mensaje;
    //     }
    //     return Controller::reponseFormat($status, $data, $mensaje) ;
    // }

    public function accionesAudit(Request $request, $id){
        $status = false;
        $mensaje = "";
        $data = [];
        error_log("accionesAudit");

        $payload = (Object) Controller::tokenSecurity($request);
        if (!$payload->validate){
            $status = false;
            $mensaje = $payload->mensaje;
        }else{
            // error_log($id);
            $identificador = 0;
            if ($id == 0){
               $sql="SELECT
                  metodo, 
                  descripcion,
                  idusuario,
                  count(*) as total
                FROM 
                  auditoria_uso
                WHERE idcliente = ?
                GROUP BY metodo, descripcion, idusuario
                ORDER BY count(*) desc";
              $identificador = $payload->payload["idcliente"];
            }else{
              $sql = "SELECT
                  descripcion,
                  count(*) as total
                FROM auditoria_uso 
                GROUP BY idusuario, descripcion 
                HAVING idusuario = ?
                ORDER BY count(*) DESC LIMIT 10";
              $identificador = $id;
            }
    
            try{
              $data = DB::select($sql, [$identificador]);
              $mensaje = "";
              $status = true;
            }catch(Exception $err){
              $status = false;
              $mensaje = $err;
            }
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }

    public function ultimasAccionesAudit(Request $request, $id){
        $status = false;
        $mensaje = "";
        $data = [];
        error_log("accionesAudit");

        $payload = (Object) Controller::tokenSecurity($request);
        if (!$payload->validate){
            $status = false;
            $mensaje = $payload->mensaje;
        }else{
          try{
            if ($id == 0){
              $data = AuditoriaUso::where("idcliente", $payload->payload["idcliente"])->with("usuario")->orderBy('created_at', 'desc')->skip(0)->take(10)->get();
            }else{
              $data = AuditoriaUso::where("idusuario",$id)->with("usuario")->orderBy('created_at', 'desc')->skip(0)->take(10)->get();
            }
            $mensaje = "";
            $status = true;
          }catch(Exception $err){
            $status = false;
            $mensaje = $err;
          }
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }

}

                                   
