<?php

namespace App\Http\Controllers;

use App\Models\Servidores;
use App\Models\Usuario;
use Carbon\Exceptions\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use phpseclib3\Crypt\RSA\Formats\Keys\OpenSSH;
use phpseclib3\Net\SSH2;

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
            $record_g["ubicacion"] =  $record["ubicacion"];
            $record_g["nombre"] =  $record["nombre"];
            $record_g["host"] =  $record["host"];
            $record_g["ssh_puerto"] =  $record["ssh_puerto"];
            $record_g["agente_puerto"] =  $record["agente_puerto"];
            $record_g["comentarios"] =  $record["comentarios"];
            // $record_g["idscript_nuevo"] =  $record["idscript_nuevo"];

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
                $record_g["ubicacion"] =  $record["ubicacion"];
                $record_g["nombre"] =  $record["nombre"];
                $record_g["host"] =  $record["host"];
                $record_g["ssh_puerto"] =  $record["ssh_puerto"];
                $record_g["agente_puerto"] =  $record["agente_puerto"];
                $record_g["comentarios"] =  $record["comentarios"];
                // $record_g["idscript_nuevo"] =  $record["idscript_nuevo"];

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
                $port = $record["puerto"];
                // $port = "0000";

                $data = Usuario::where("idusuario", $payload->payload["idusuario"])->get();
                foreach ($data as $key => $value) {
                    $rs = $value;
                }

                $user = $rs["usuario"];
                $password = Controller::decode($rs["clave"]);

                $tiempo_inicio = microtime(true);
                try{
                    $ssh = new SshController($host, $port, $user, $password);
                    // $data = $ssh->run('uptime -p');
                    $cmd = 'sec=$(( $(date +%s) - $(date -d "$(ps -p 1 -o lstart=)" +%s) )); d=$((sec/86400)); h=$(( (sec%86400)/3600 )); m=$(( (sec%3600)/60 )); s=$((sec%60)); printf "%02d:%02d:%02d:%02d\n" $d $h $m $s';
                    $data = $ssh->run($cmd);
                    if (!$data) {
                        $status = false;
                        $tiempo_fin = microtime(true);
                        $tiempo_transcurrido = round( $tiempo_fin - $tiempo_inicio ,2);
                        $mensaje = "Servidor {$host}:{$port} con usuario: {$user}, no respondio al login SSH. {$tiempo_transcurrido} seg";
                    }else{
                        $tiempo_fin = microtime(true);
                        $tiempo_transcurrido = round( $tiempo_fin - $tiempo_inicio ,2);
                        // $data = "Servidor {$host}:{$port} con usuario: {$user}, respondio correctamente al login SSH. {$tiempo_transcurrido} seg";
                        $data = str_replace("\n","",$data);
                        $status = true;

                        // try{
                        //     Servidores::where("host", $host)
                        //         ->where("port", $port)
                        //         ->update(["salud"=>1, "salud_fecha" => date("Y-m-d H:i:s")]);
                        // }catch(Exception $err){}
                    }
                    $aud->saveAuditoria([
                        "idusuario" => $payload->payload["idusuario"],
                        "json" => [
                            "host"=>$host,
                            "port"=>$port,
                            "user"=>$user,
                            "result"=>$data
                        ],
                        "mensaje" => $mensaje
                    ]);
                }catch(Exception $err){
                    $status = false;
                    $mensaje = $err;
                }

            } else {
                $status = false;
                $mensaje = "El host o el puerto estan vacíos";
            }
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }
}
