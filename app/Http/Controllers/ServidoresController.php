<?php

namespace App\Http\Controllers;

use App\Models\Servidores;
use App\Http\Requests\StoreServidoresRequest;
use App\Http\Requests\UpdateServidoresRequest;
use App\Models\Usuario;
use Carbon\Exceptions\Exception;
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

    public function getOne(Request $request, $id)
    {
        $status = false;
        $data = [];
        $mensaje = "";
        $payload = (Object) Controller::tokenSecurity($request);
        if ($payload->validate){
            if (isset($id)){
                $status = true;
                $data = Servidores::where("idservidor", $id)->with("cliente")->get();
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

            $record_g = [];
            $record_g["idcliente"] =  $payload->payload["idcliente"];
            $record_g["localizacion"] =  $record["localizacion"];
            $record_g["nombre"] =  $record["nombre"];
            $record_g["host"] =  $record["host"];
            $record_g["puerto"] =  $record["puerto"];
            $record_g["idscript_nuevo"] =  $record["idscript_nuevo"];
            
            try{
                $data = Servidores::create($record_g);
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

                $record_g = [];
                $record_g["idcliente"] =  $payload->payload["idcliente"];
                $record_g["localizacion"] =  $record["localizacion"];
                $record_g["nombre"] =  $record["nombre"];
                $record_g["host"] =  $record["host"];
                $record_g["puerto"] =  $record["puerto"];
                $record_g["idscript_nuevo"] =  $record["idscript_nuevo"];
    
                try{
                    $data = Servidores::where("idservidor", $id)->update($record_g);
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
                Servidores::where("idservidor", $id)->update(["estado" => 0]);
                Servidores::where("idservidor", $id)->delete();
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
                Servidores::where("idservidor", $id)->restore();
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


    public function healthyServers(Request $request){
        
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
            if ($record["puerto"] !="" && $record["host"] != ""){

                $host = $record["host"];
                $puerto = $record["puerto"];

                $data = Usuario::where("idusuario", $payload->payload["idusuario"])->get();
                foreach ($data as $key => $value) {
                    $rs = $value;
                }

                $user = $rs["usuario"];
                $password = Controller::decode($rs["clave"]);
                $cmd = "ls /";

                echo "{$host} {$puerto} {$user} {$password}";
                die();

                $status = true;
                $data = [];
                $data = Servidores::where("host", $host)
                    ->where("puerto", $puerto)
                    ->get();

                if(count($data)>0){
                    $ssh          = SSH::run();
                    print_r($ssh);
                    // $login        = ssh2_auth_password($ssh, SFTP_USER, SFTP_PASS);
                    // $sftp         = ssh2_sftp($ssh);
                    // $sftp_fd      = intval($sftp);
                    // $filesystem   = opendir("ssh2.sftp://$sftp_fd/.");


                }else{

                }

                // shell_exec("ssh user@host.com mkdir /testing");

                // $aud->saveAuditoria([
                //     "idusuario" => $payload->payload["idusuario"],
                // ]);
            } else {
                $status = false;
                $mensaje = "El hosto o el puerto estan vacíos";
            }
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }
}
