<?php

namespace App\Http\Controllers;

use App\Models\DocumentalHistorico;
use App\Models\HistoricoCmd;
use App\Models\Servidores;
use App\Models\ServidoresFamilia;
use App\Models\Usuario;
use Carbon\Exceptions\Exception;
use Illuminate\Http\Request;

use function Laravel\Prompts\error;

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
                    $data = Servidores::with("cliente","usuarios", "familia")->where("idcliente", $payload->payload["idcliente"])->get();
                    break;
                case 'inactivos':
                    $data = Servidores::onlyTrashed()->with("cliente","usuarios", "familia")->where("idcliente", $payload->payload["idcliente"])->get();
                    break;
                case 'todos':
                    $data = Servidores::withTrashed()->with("cliente","usuarios", "familia")->where("idcliente", $payload->payload["idcliente"])->get();
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

    public function getFamilia(Request $request)
    {
        $status = false;
        $data = [];
        $mensaje = "";
        $payload = (Object) Controller::tokenSecurity($request);
        if ($payload->validate){
            $status = true;
            $data = ServidoresFamilia::get();
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }

    public function getOneWithUsers(Request $request, $id)
    {
        $status = false;
        $data = [];
        $mensaje = "";
        $payload = (Object) Controller::tokenSecurity($request);
        if ($payload->validate){
            if (isset($id)){
                $status = true;
                $data = Servidores::with([
                        'usuarios' => function ($query) {
                            $query->addSelect('*'); 
                        },
                        'usuarios.grupo',
                        'cliente.configuracion.script.cmds',
                        'comandos'
                    ])
                    ->where('idservidor', $id)
                    ->get();

                $data->each(function ($servidor) {
                    $servidor->usuarios->each->makeVisible('clave');
                });
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
            $record_g["terminal_puerto"] =  $record["terminal_puerto"];
            $record_g["comentarios"] =  $record["comentarios"];
            $record_g["idservidores_familia"] =  $record["idservidores_familia"];
            // $record_g["idscript_monitoreo"] =  $record["idscript_monitoreo"];

            try{
                $data = Servidores::create($record_g);
                $status = true;
            } catch( Exception $err){
                $status = false;
                $mensaje = $err;
            }

            // if (count($record["servidor_monitoreo"])){
            //     foreach ($record["servidor_monitoreo"] as $key => $value) {
            //         $record_serv_mon = [
            //             "idservidor" => $data["idservidor"],
            //             "idscript" => $value["idscript"],
            //         ];
            //     }
            //     try{
            //         $data_serv = ServidorMonitoreo::create($record_serv_mon);
            //         $status = true;
            //     } catch( Exception $err){
            //         $status = false;
            //         $mensaje = $err;
            //     }

            // }


            $aud->saveAuditoria([
                "idusuario" => $payload->payload["idusuario"],
                "json" => $record,
                "descripcion" => "Creación de Servidores"
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
                $record_g["terminal_puerto"] =  $record["terminal_puerto"];
                $record_g["comentarios"] =  $record["comentarios"];
                $record_g["idservidores_familia"] =  $record["idservidores_familia"];
                // $record_g["idscript_monitoreo"] =  $record["idscript_monitoreo"];

                try{
                    $data = Servidores::where("idservidor", $id)->update($record_g);
                    $status = true;

                } catch( Exception $err){
                    $status = false;
                    $mensaje = $err;
                }
                $aud->saveAuditoria([
                    "idusuario" => $payload->payload["idusuario"],
                    "json" => $record,
                    "descripcion" => "Actualización de Servidores"
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
                    "descripcion" => "Eliminado de Servidores"
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
                    "descripcion" => "Recuperación de Servidores"
                ]);
            } else {
                $status = false;
                $mensaje = "ID se encuentra vacío";
            }
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }


    public function healthyServers(Request $request){
        $status = false;
        $data = [];
        $mensaje = "";

        $payload = (Object) Controller::tokenSecurity($request);
        if (!$payload->validate){
            $status = false;
            $mensaje = $payload->mensaje;
        }else{
            if ($request->input("puerto") !="" && $request->input("host") != ""){

                $host = $request->input("host");
                $port = $request->input("puerto");

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
                    $aud = new AuditoriaUsoController();
                    $aud->saveAuditoria([
                        "idusuario" => $payload->payload["idusuario"],
                        "json" => [
                            "host"=>$host,
                            "port"=>$port,
                            "user"=>$user,
                            "result"=>$data
                        ],
                        "mensaje" => $mensaje,
                        "descripcion" => "Comprobación estado api"
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

    public function cmds(Request $request){
        $status = false;
        $data = [];
        $mensaje = "";

        $payload = (Object) Controller::tokenSecurity($request);
        if (!$payload->validate){
            $status = false;
            $mensaje = $payload->mensaje;
        }else{
            if ($request->input("puerto") !="" && $request->input("host") != ""){

                $host = $request->input("host");
                $port = $request->input("puerto");
                $data_original = $request->input("data");

                $rsusr = Usuario::where("idusuario", $payload->payload["idusuario"])->get();
                foreach ($rsusr as $key => $value) {
                    $rs = $value;
                }

                $user = $rs["usuario"];
                $password = Controller::decode($rs["clave"]);

                $data_respuesta = [];

                foreach ($data_original as $key => $value) {
                    $comando = $value["cmd"];
                    $tiempo_inicio = microtime(true);
                    try{
                        $ssh = new SshController($host, $port, $user, $password);
                        $resp = $ssh->run($comando);

                        error_log($resp);

                        $status = true;
                        // $tiempo_fin = microtime(true);
                        // $tiempo_transcurrido = round( $tiempo_fin - $tiempo_inicio ,2);
                        // $identificador = $request->input("identificador");
                        
                        $data_respuesta[] = [
                            "id" => $value["id"],
                            "cmd" => base64_encode($value["cmd"]),
                            "respuesta" => base64_encode($resp),
                        ];
                        $data = [
                            "action" => $request->input("action"),
                            "identificador" => $request->input("identificador"),
                            "data" =>  $data_respuesta
                        ];

                    }catch(Exception $err){
                        $status = false;
                        $mensaje = $err;
                    }
                }
            } else {
                $status = false;
                $mensaje = "El host o el puerto estan vacíos";
            }
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }
}
