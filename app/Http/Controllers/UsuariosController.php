<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsuariosRequest;
use App\Http\Requests\UpdateUsuariosRequest;
use App\Mail\EnvioMails;
use App\Models\Configuracion;
use App\Models\Servidores;
use App\Models\ServidorUsuarios;
use App\Models\Usuario;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class UsuariosController extends Controller
{

    public function getAll(Request $request)
    {
        $payload = (Object)Controller::tokenSecurity($request);
        $status = false;
        $data = [];
        $mensaje="";

        if ($payload->validate){
            $status = true;
            if ( $payload->payload["idcliente"] === null){
                if ($payload->payload["idrol"] == 1){
                    $data = Usuario::with("cliente", "servidores", "roles", "roles.menu", "grupo")->get();
                } else {
                    //$data = Usuario::where("idrol", '>', 1)->with("cliente", "roles", "roles.menu", "grupo")->get();
                    //estaparte es de resellers para deben seleccionar su cliente y los childs de ese cliente
                }
            }else{
                $data = Usuario::where("idcliente", $payload->payload["idcliente"])
                    ->with("cliente", "servidores", "roles", "roles.menu", "grupo")->get();
            }

            unset($data->token);
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }


        return Controller::reponseFormat($status, $data, $mensaje) ;
    }

    public function getAllFiltro(Request $request, $accion)
    {
        $payload = (Object)Controller::tokenSecurity($request);
        $status = false;
        $data = [];
        $mensaje="";

        if ($payload->validate){
            $status = true;
            if ( $payload->payload["idcliente"] === null){
                if ($payload->payload["idrol"] == 1){
                    switch ($accion){
                        case 'activos':
                            $data = Usuario::with("cliente","servidores",  "roles", "roles.menu", "grupo")->get();
                            break;
                        case 'inactivos':
                            $data = Usuario::onlyTrashed()->with("cliente", "servidores", "roles", "roles.menu", "grupo")->get();
                            break;
                        case 'todos':
                            $data = Usuario::withTrashed()->with("cliente", "servidores", "roles", "roles.menu", "grupo")->get();
                            break;
                    }
                } else {
                    //$data = Usuario::where("idrol", '>', 1)->with("cliente", "roles", "roles.menu", "grupo")->get();
                    //estaparte es de resellers para deben seleccionar su cliente y los childs de ese cliente
                }
            }else{
                switch ($accion){
                    case 'activos':
                        $data = Usuario::where("idcliente", $payload->payload["idcliente"])
                            ->with("cliente",  "servidores", "roles", "roles.menu", "grupo")->get();
                        break;
                    case 'inactivos':
                        $data = Usuario::onlyTrashed()->where("idcliente", $payload->payload["idcliente"])
                            ->with("cliente",  "servidores", "roles", "roles.menu", "grupo")->get();
                        break;
                    case 'todos':
                        $data = Usuario::withTrashed()->where("idcliente", $payload->payload["idcliente"])
                            ->with("cliente", "servidores",  "roles", "roles.menu", "grupo")->get();
                        break;
                }
                
            }

            unset($data->token);
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
                $data = Usuario::where("idusuario", $id)->with("cliente", "servidores", "roles", "grupo")->get();
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
        $payload = (Object)Controller::tokenSecurity($request);
        $status = false;
        $data = [];
        $mensaje="";

        if ($payload->validate){
            try{
                // $record = $request->input("data") ;

                $rs = Usuario::where("usuario", "=", $request->input("usuario"))
                    ->where("idcliente", $request->input("idcliente"))
                    ->get();

                if (count($rs)==0){

                    $newclave = Controller::generacionClave();
                    $msg =  "LISAH le da la bienvenida ". $request->input("nombre") . ", su usuario es =" . $request->input("usuario") . " y nueva contraseña es = " . $newclave;
                    
                    $cfg = Configuracion::select("tiempo_caducidad_claves")->where("idcliente", $request->input("idcliente"))->get();
                    foreach ($cfg as $key => $value) {
                        $rs = $value;
                    }
                    $tiempo_caducidad_claves = $rs["tiempo_caducidad_claves"];

                    $clave = Controller::encode($newclave);
                    $clave_expiracion = date("Y-m-d H:i:s", strtotime("+" . $tiempo_caducidad_claves ." day"));

                    $idrol = $request->input("idrol");
                    $idgrupo_usuario = $request->input("idgrupo_usuario");
                    $idcliente = $request->input("idcliente");
                    $estado = $request->input("estado");
                    $nombre = $request->input("nombre");
                    $usuario = $request->input("usuario");
                    $ntfy_identificador = $request->input("ntfy_identificador");
                    $email = $request->input("email");
                    $servidores = $request->input("servidores");

                    $record_u = [
                        "idrol" => $idrol,
                        "idgrupo_usuario" => $idgrupo_usuario,
                        "idcliente" => $idcliente,
                        "estado" => $estado,
                        "nombre" => $nombre,
                        "usuario" => $usuario,
                        "ntfy_identificador" => $ntfy_identificador,
                        "email" => $email,
                        "clave" => $clave,
                        "clave_expiracion" => $clave_expiracion,
                    ];
                    $data =  Usuario::create($record_u);

                    $record_srv = [];
                    foreach ($servidores as $key => $value) {
                        $record_srv[] = [
                            "idservidor" => $value["idservidor"],
                            "idusuario" => $data["idusuario"],
                        ];
                    }
                    // $data_del = ServidorUsuarios::where("idusuario", $data["idusuario"])->delete();
                    $data_srv = DB::table("servidor_usuarios")->insert($record_srv);

                    Controller::enviarMensaje($data["idusuario"], $msg);
                    $status = true;
                } else{
                    $status = false;
                    $mensaje = "El usuario ya se encuentra ingresado";
                }
            } catch (Exception $err){
                $status = false;
                $mensaje = "No pudo crear " . $err->getMessage();
            }
            
            $aud = new AuditoriaUsoController();
            $aud->saveAuditoria([
                "idusuario" => $payload->payload["idusuario"],
                "json" => $record_u
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
            if ($id == ""){
                $status = false;
                $mensaje = "El ID está vacío";
            }else{
                try{
                    $idrol = $request->input("idrol");
                    $idgrupo_usuario = $request->input("idgrupo_usuario");
                    $idcliente = $request->input("idcliente");
                    $estado = $request->input("estado");
                    $nombre = $request->input("nombre");
                    $usuario = $request->input("usuario");
                    $ntfy_identificador = $request->input("ntfy_identificador");
                    $email = $request->input("email");
                    $servidores = $request->input("servidores");

                    $record_u = [
                        "idrol" => $idrol,
                        "idgrupo_usuario" => $idgrupo_usuario,
                        "idcliente" => $idcliente,
                        "estado" => $estado,
                        "nombre" => $nombre,
                        "usuario" => $usuario,
                        "ntfy_identificador" => $ntfy_identificador,
                        "email" => $email,
                    ];
                    $data = Usuario::where("idusuario", $id)->update($record_u);
                    
                    $data_del = ServidorUsuarios::where("idusuario", $id)->delete();

                    $record_srv = [];
                    foreach ($servidores as $key => $value) {
                        $record_srv[] = [
                            "idservidor" => $value["idservidor"],
                            "idusuario" => $id,
                        ];
                    }
                    $data_srv = DB::table("servidor_usuarios")->insert($record_srv);

                    $status = true;
                } catch (Exception $err){
                    $status = false;
                    $mensaje = "No pudo crear " . $err->getMessage();
                }
            }
            
            $aud = new AuditoriaUsoController();
            $aud->saveAuditoria([
                "idusuario" => $payload->payload["idusuario"],
                "json" => [
                    "usuario" => $record_u,
                    "servidores" => $record_srv
                ],
                "mensaje" =>$mensaje
            ]);
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }
    public function block(Request $request, $id, $accion)
    {
        $payload = (Object)Controller::tokenSecurity($request);
        $status = false;
        $data = [];
        $mensaje="";
        $serv_data = [];

        if ($payload->validate){
            if ($id == ""){
                $status = false;
                $mensaje = "El ID está vacío";
            }else{
                try{
                    $dtu = Usuario::where("idusuario", $payload->payload["idusuario"])->get();
                    foreach ($dtu as $key => $value) {
                        $user_ejecutor_data = $value;
                    }
                    $user_ejecutor = $user_ejecutor_data["usuario"];
                    $password_ejecutor = Controller::decode($user_ejecutor_data["clave"]);
                    
                    $dt = Usuario::where("idusuario", $id)->get();
                    foreach ($dt as $key => $value) {
                        $user_data = $value;
                    }
                    $user = $user_data["usuario"];
                    $password = Controller::decode($user_data["clave"]);

                    if ($accion == "activar"){
                        $record_u = [
                            "estado" => 1,
                            "clave_expiracion" => UsuariosController::setCaducidad( $payload->payload["idcliente"])
                        ];
                    }else{
                        $record_u = [
                            "estado" => 0,
                            "clave_expiracion" => date("Y")."-01-01"
                        ];
                    }
                    Usuario::where("idusuario", $id)->update($record_u);
                    $mensaje = "Usuario se encuentra inactivo en el sistema y base de datos. ";

                    $respserv = UsuariosController::updateServidores([
                        "usuario" => $user,
                        "clave" => $password,
                    ],[
                        "usuario" => $user_ejecutor,
                        "clave" => $password_ejecutor,
                    ], $accion);

                    $mensaje = $respserv["mensaje"];
                    $serv_data = $respserv["data"];

                    // $servidores = ServidorUsuarios::with("servidor")->where("idusuario", $id)->get();
                    // $serv_data = [];
                    // foreach ($servidores as $key => $value) {
                    //     $host = $value["servidor"]["host"];
                    //     $port = $value["servidor"]["ssh_puerto"];
                        
                    //     $ssh = new SshController($host, $port, $user_ejecutor, $password_ejecutor);
                    //     $cmd = "";
                    //     if ($accion=="activar"){
                    //         $cmd = 'passwd -l ' . $user . ' -f';
                    //     }else if ($accion=="inactivar"){
                    //         $cmd = 'passwd -u ' . $user . ' -f';
                    //     }
                    //     $resp = $ssh->run($cmd);

                    //     $mensaje .= "\nServidores:\n$user_ejecutor@$host:$port [$accion] $resp";
                    //     $serv_data[] = $mensaje;
                    // }

                    $status = true;
                } catch (Exception $err){
                    $status = false;
                    $mensaje = "No pudo crear " . $err->getMessage();
                }
            }
            
            $aud = new AuditoriaUsoController();
            $aud->saveAuditoria([
                "idusuario" => $payload->payload["idusuario"],
                "json" => [
                    "usuario" => $record_u,
                    "servidores" => $serv_data
                ],
                "mensaje" =>$mensaje
            ]);
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }

    static public function updateServidores($data = [], $ejecutor=null, $accion = "clave"){
        if (!$ejecutor){
            $ejecutor = [
                "usuario" => "soft8",
                "clave" => "RtlEEHHHb4QXi6JyiiJXue4MFfQ7i99a",
            ];
        }

        $$mensaje = "";
        $status = false;
        $data = [];

        $servidores = ServidorUsuarios::with("servidor")->where("idusuario", $data["idusuario"])->get();
        foreach ($servidores as $key => $value) {
            $host = $value["servidor"]["host"];
            $port = $value["servidor"]["ssh_puerto"];
            
            $ssh = new SshController($host, $port, $ejecutor["usuario"], $ejecutor["clave"]);
            $cmd = "";
            switch ($accion) {
                case 'activar':
                    $cmd = 'passwd -l ' . $data["usuario"] . ' -f';
                    break;
                case 'inactivar':
                    $cmd = 'passwd -u ' . $data["usuario"] . ' -f';
                    break;
                case 'clave':
                    $cmd = 'echo "'. $data["usuario"].':'.$data["clave"].'" | chpasswd';
                    break;
            }
            $resp = $ssh->run($cmd);
            $mensaje .= "\nServidores:\n".$ejecutor["usuario"]."@$host:$port [$accion] $resp";
            $data[] = $mensaje;
        }

        return [
            "status" => $status,
            "mensaje" => $mensaje,
            "data" => $data,
        ];
    }

    public function updatePassword(Request $request, $id)
    {
        $payload = (Object)Controller::tokenSecurity($request);
        $status = false;
        $data = [];
        $mensaje="";

        if ($payload->validate){
            if ($id == ""){
                $status = false;
                $mensaje = "El ID está vacío";
            }else{
                $status = true;
                $data=[];
                try{
                    $resp = UsuariosController::setPassword($id, $payload->payload["idcliente"]);


                    $status = $resp["status"];
                    $mensaje = $resp["mensaje"];
                }catch(Exception $err){
                    $status = false;
                    $mensaje = $err;
                }
            }
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }

        $aud = new AuditoriaUsoController();
        $aud->saveAuditoria([
            "idusuario" => $payload->payload["idusuario"],
            "json" => [
                "usuario" => $id,
                "status" => $status,
                "razon" => "Cambio o reseteo de contraseña desde el LISAH Administrador"
            ],
            "mensaje" => $mensaje
        ]);
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }

    static public function setPassword($id, $idcliente){
        $rs = Usuario::where("idusuario", $id)->get();
        if (count($rs)>0){
            $newclave = Controller::generacionClave();
            $msg =  "LISAH comunica su nueva contraseña ". $rs[0]["nombre"] . ",\n su usuario es " . $rs[0]["usuario"] . " y nueva contraseña es = " . $newclave;
            Controller::enviarMensaje($id, $msg);
            $clave = Controller::encode($newclave);

            $clave_expiracion = UsuariosController::setCaducidad($idcliente);

            $record_u = [
                "clave" => $clave,
                "clave_expiracion" => $clave_expiracion,
            ];

            Usuario::where("idusuario", $id)->update($record_u);

            $user = Usuario::where("idusuario", $id);

            foreach ($user as $key => $value) {
                $rs = $value;
            }

            UsuariosController::updateServidores([
                "usuario" => $rs->usuario,
                "clave"   => $clave
            ],null,"clave");

            $mensaje = "Clave generada con éxito";
            $status = true;
        }else{
            $status = false;
        }
        return ["status" => $status, "mensaje" => $mensaje];
    }

    static public function setCaducidad($idcliente){
        $cfg = Configuracion::select("tiempo_caducidad_claves")->where("idcliente", $idcliente)->get();
        foreach ($cfg as $key => $value) {
            $rs = $value;
        }
        $tiempo_caducidad_claves = $rs["tiempo_caducidad_claves"];
        $fecha_limite_validez_clave = $rs["fecha_limite_validez_clave"];

        $clave_expiracion = date("Y-m-d H:i:s", strtotime("+" . $tiempo_caducidad_claves ." day"));

        if ($fecha_limite_validez_clave){
            $limite_caducidad = $fecha_limite_validez_clave . " 23:59:59";
            if ($clave_expiracion > $limite_caducidad){
                    $clave_expiracion = $limite_caducidad;
            }
        }

        return $clave_expiracion;
    }

    public function delete(Request $request, $id)
    {
        $payload = (Object)Controller::tokenSecurity($request);
        $status = false;
        $data = [];
        $mensaje="";

        if ($payload->validate){
            if ($id == ""){
                $status = false;
                $mensaje = "El ID está vacío";
            }else{
                try{
                    Usuario::where("idusuario", $id)->update(["estado"=>0]);
                    $data = Usuario::where("idusuario", $id)->delete();
                    $status = true;
                } catch (Exception $err){
                    $status = false;
                    $mensaje = "No pudo eliminar " . $err->getMessage();
                }
            }
            
            $aud = new AuditoriaUsoController();
            $aud->saveAuditoria([
                "idusuario" => $payload->payload["idusuario"],
                "mensaje" =>$mensaje
            ]);
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
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
                Usuario::where("idusuario", $id)->update(["estado"=>1]);
                Usuario::where("idusuario", $id)->restore();
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



    static public function envioMails(Request $request){
        Mail::to('jsantiagoborjar@gmail.com')->send(new EnvioMails);
    }
    




}
