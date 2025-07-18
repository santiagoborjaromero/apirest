<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAuthRequest;
use App\Http\Requests\UpdateAuthRequest;
use App\Models\AuditoriaUso;
use App\Models\HistoricoClaves;
use App\Models\HistoricoCodigoVerificacion;
use App\Models\Usuario;
use Exception;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\Cast\Object_;

class AuthController extends Controller
{
    public function authUser(Request $request)
    {
        $usuario = $request->input("usuario");   //$_POST["usuario"]
        $clave = $request->input("clave");

        $aud = new AuditoriaUsoController();

        $record = [];
        $rs = null;
        $status = true;
        $mensaje = "";
        $data = null;

        $data = Usuario::where("usuario", $usuario)->where("estado", 1)
            ->with("cliente", "roles", "roles.menu", "config", "grupo")
            ->get();

        foreach ($data as $key => $value) {
            $rs = $value;
        }

        if (count($data)>0){
            $status = true;

            if ($rs->clave === null || $rs->clave == "cambiar" || $rs->clave == ""){
                $mensaje = "Se establece nueva contraseña";
                $aud->saveAuditoria([
                    "idusuario" => $rs->idusuario,
                    "mensaje" => $mensaje
                ]);
                $status = false;
                $data = [];
                $newclave = Controller::generacionClave();
                $msg = $mensaje . " " . $newclave;
                Controller::enviarMensaje($rs->idusuario, $msg);
                $record["clave"] = Controller::encode($newclave);
                $record["clave_expiracion"] = date("Y-m-d H:i:s", strtotime('+1 year'));
                Usuario::where("idusuario", $rs->idusuario)->update(json_decode(json_encode($record),true));
            } else {
                if ( Controller::encode($clave) != $rs->clave ){
                    $status = false;
                    $mensaje = "Contraseña erronea";
                    $data = [];
                    $aud->saveAuditoria([
                        "idusuario" => $rs->idusuario,
                        "json" => [
                            "usuario" => $usuario,
                            // "clave" => $clave,
                        ],
                        "mensaje" => $mensaje
                    ]);
                    Controller::enviarMensaje($rs->idusuario, $mensaje);
                } else {
                    if ($rs->clave_expiracion===null){
                        $rs->clave_expiracion = date("Y-m-d H:i:s", strtotime('+1 year'));
                        $record["clave_expiracion"] = $rs->clave_expiracion;
                        $mensaje = "Se ha asignado nueva fecha de caducidad a la contraseña, se encontraba sin asignar";
    
                        $aud->saveAuditoria([
                            "idusuario" => $rs->idusuario,
                            "json" => [],
                            "mensaje" => $mensaje
                        ]);
                    }
    
                    if ($rs->clave_expiracion < date("Y-m-d H:i:s")){
                        $status = false;
                        $mensaje = "Contraseña caducada";
                        $data = [];
                        $aud->saveAuditoria([
                            "idusuario" => $rs->idusuario,
                            "json" => [],
                            "mensaje" => $mensaje
                        ]);
    
                        HistoricoClaves::create([
                            "idusuario" => $rs->idusuario,
                            "clave" => $rs->clave
                        ]);
    
                    } else {
                        $rs->ultimo_logueo = date("Y-m-d H:i:s");
                        $rs->numero_logueos = intval($rs->numero_logueos) + 1;
                        $record["ultimo_logueo"] = $rs->ultimo_logueo;
                        $record["numero_logueos"] =$rs->numero_logueos;

                        if (intval($rs->roles->estado) == 0){
                            $status = false;
                            $mensaje = "Rol asignado al usuario se encuentra suspendido";
                            $data = [];
                            $aud->saveAuditoria([
                                "idusuario" => $rs->idusuario,
                                "json" => ["rol" => $rs->roles->idrol, "nombre" => $rs->roles->nombre],
                                "mensaje" => $mensaje
                            ]);
                        } else {
                            if ($rs->token === null) {
                                $rs->token = $this->genToken($rs);
                                $record["token"] = $rs->token;
                                // error_log($rs->token);
                            } else {
                                $val_token = $this->validateToken($rs->token);
                                error_log("Validacion de token");
                                error_log(json_encode($val_token));
                                if (!$val_token["validate"]){
                                    switch ($val_token["mensaje"]){
                                        case "bad":
                                            $status = false;
                                            $data = [];
                                            $mensaje = "Token inválido";
                                            $aud->saveAuditoria([
                                                "idusuario" => $rs->idusuario,
                                                "mensaje" => $mensaje
                                            ]);
                                            break;
                                        case "expire":
                                            $rs->token = $this->genToken($rs);
                                            $record["token"] = $rs->token;
                                            
                                            break;
                                    }
                                }

                                /**
                                 * TODO: Cuando todo esta bien se genera codigo de verificación
                                 */
                                error_log("Generando Codigo");
                                $codigo = $this->generacionCodigoVerificacion($rs->idusuario);
                                error_log("Enviando Codigo");
                                Controller::enviarMensaje($rs->idusuario, "Codigo de verificacion para LISAH es: {$codigo}");
                                $record["verificacion_codigo"] = $codigo;
                                $record["verificacion_expira"] = date('Y-m-d H:i:s', (strtotime ("+5 Minute")));
                                $status = true;
                            }
                        }

                        if ($status){
                            error_log("Guardando");
                            error_log(json_encode($record));
                            Usuario::where("idusuario", $rs->idusuario)->update(json_decode(json_encode($record),true));
                            $aud->saveAuditoria([
                                "idusuario" => $rs->idusuario,
                                "mensaje" => "Logueado"
                            ]);
                        }
                    }
                }
            }
            
        }else{
            $status = false;
            $mensaje = "Usuario no existe o se encuentra inactivo";
            $data = [];
            $aud->saveAuditoria([
                "json" => ["usuario" => $usuario],
                "mensaje" => $mensaje
            ]);
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }


    public function genToken($obj){
        $rec = [
            "idcliente" => $obj->idcliente,
            "idusuario" => $obj->idusuario,
            "idrol" => $obj->idrol,
            "expire_date" => date("Y-m-d H:i:s", strtotime('+1 day'))
        ];
        error_log(json_encode($rec));
        return base64_encode(Controller::encode(json_encode($rec)));
    }

    public function validateToken($token){
        $conclusion = false;
        $msg = "";
        
        error_log("TOKEN");
        error_log($token);
        $payload = json_decode(Controller::decode(base64_decode($token)), true);
        error_log(json_encode($payload));
        if ($payload){
            if ($payload["expire_date"] >= date("Y-m-d H:i:s")){
                $conclusion = true;
                $msg = "";
            } else {
                $conclusion = false;
                $msg = "expire";
            }
        }else{
            $conclusion = false;
            $msg = "bad";
        }

        return ["validate" => $conclusion, "mensaje" => $msg];
    }


    public function regenerarCodigo(Request $request){
        $status = false;
        $data = [];
        $mensaje = "";
        $record = [];
        $payload = (Object) Controller::tokenSecurity($request);
        if ($payload->validate){
            $status = true;
            $data = [];
            $mensaje = "Código generado";
            $codigo = $this->generacionCodigoVerificacion($payload->payload["idusuario"]);
            Controller::enviarMensaje($payload->payload["idusuario"], "Codigo de verificacion para LISAH es: {$codigo}");
            $record["verificacion_codigo"] = $codigo;
            $record["verificacion_expira"] = date('Y-m-d H:i:s', (strtotime ("+5 Minute")));
            Usuario::where("idusuario", $payload->payload["idusuario"])->update(json_decode(json_encode($record),true));
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }
    
    public function generacionCodigoVerificacion($idusuario){
        do {
            $codigo = str_pad(random_int(000000,999999),6,"0",STR_PAD_LEFT);
            $existe = "";
            $rs = HistoricoCodigoVerificacion::select("codigo")
                ->where("idusuario", "=",  $idusuario)
                ->where("codigo", "=", $codigo)
                ->get();
            if ($rs){
                foreach ($rs as $key => $value) {
                    $existe = $value["codigo"];
                }
            }
        } while($existe != "");

        $data = [
            "idusuario" => $idusuario,
            "codigo" => $codigo
        ];

        HistoricoCodigoVerificacion::create($data);

        return $codigo;
    }

    public function verificarCodigo(Request $request, $codigo){
        $payload = (Object) Controller::tokenSecurity($request);
        if ( !$payload->validate ){
            $status = $payload->validate;
            $mensaje = $payload->mensaje;
        }else{
            

            $rs = Usuario::where("idusuario", $payload->payload["idusuario"])->get();
            foreach ($rs as $key => $value) {
                $row = $value;
            }
            if ($row){
                $verificacion_codigo = $row["verificacion_codigo"];
                $fecha_expiracion = date($row["verificacion_expira"]);
                if ($fecha_expiracion >= date("Y-m-d H:i:s")){
                    if ($verificacion_codigo == $codigo){
                        $status = true;
                        $mensaje = "Código de verificación es correcto";
                    }else {
                        $status = false;
                        $mensaje = "El código de verificación es incorrecto";
                    }
                } else {
                    $status = false;
                    $mensaje = "El código de verificación ha expirado";
                }
            } else {
                $status = false;
                $mensaje = "El código de verificación no existe";
            }

            if (!$status){
                $aud = new AuditoriaUsoController();
                $aud->saveAuditoria([
                    "idusuario" => $payload->payload["idusuario"],
                    "mensaje" => $mensaje
                ]);
            }
        }

        return Controller::reponseFormat($status, [], $mensaje) ;
    }


}
