<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsuariosRequest;
use App\Http\Requests\UpdateUsuariosRequest;
use App\Mail\EnvioMails;
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
                $record = $request->input("data") ;

                $rs = Usuario::where("usuario", "=", $record["usuario"])
                    ->where("idcliente", $record["idcliente"])
                    ->get();

                if (count($rs)==0){
                    $newclave = Controller::generacionClave();
                    $msg =  "LISAH le da la bienvenida ". $record["nombre"] . ", su usuario es =" . $record["usuario"] . " y nueva contraseña es = " . $newclave;
                    $clave = Controller::encode($newclave);
                    $clave_expiracion = date("Y-m-d H:i:s", strtotime('+1 year'));

                    $idrol = $record["idrol"];
                    $idgrupo_usuario = $record["idgrupo_usuario"];
                    $idcliente = $record["idcliente"];
                    $estado = $record["estado"];
                    $nombre = $record["nombre"];
                    $usuario = $record["usuario"];
                    $ntfy_identificador = $record["ntfy_identificador"];
                    $email = $record["email"];
                    $servidores = $record["servidores"];

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
                "json" => $record
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
                    // $record = json_decode(json_encode($request->input("data")), true) ;
                    $record = $request->input("data");
                    $idrol = $record["idrol"];
                    $idgrupo_usuario = $record["idgrupo_usuario"];
                    $idcliente = $record["idcliente"];
                    $estado = $record["estado"];
                    $nombre = $record["nombre"];
                    $usuario = $record["usuario"];
                    $ntfy_identificador = $record["ntfy_identificador"];
                    $email = $record["email"];
                    $servidores = $record["servidores"];

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

                    $record_srv = [];
                    foreach ($servidores as $key => $value) {
                        $record_srv[] = [
                            "idservidor" => $value["idservidor"],
                            "idusuario" => $id,
                        ];
                    }
                    $data_del = ServidorUsuarios::where("idusuario", $id)->delete();
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
                "json" => $record,
                "mensaje" =>$mensaje
            ]);
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
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
