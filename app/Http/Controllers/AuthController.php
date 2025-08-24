<?php

namespace App\Http\Controllers;

use App\Http\Resources\UsuariosCollection;
use App\Models\AuditoriaUso;
use App\Models\Configuracion;
use App\Models\HistoricoClaves;
use App\Models\HistoricoCodigoVerificacion;
use App\Models\Usuario;
use Exception;
use Illuminate\Http\Request;

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
                $record["clave_expiracion"] = UsuariosController::setCaducidad($rs->idcliente);
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
                        ],
                        "mensaje" => $mensaje
                    ]);
                    Controller::enviarMensaje($rs->idusuario, $mensaje);
                } else {

                    if (!$rs->idcliente){
                        $rs->token = $this->genToken($rs, true);
                        $record["token"] = $rs->token;
                        $rs->ultimo_logueo = date("Y-m-d H:i:s");
                        $rs->numero_logueos = intval($rs->numero_logueos) + 1;
                        $record["ultimo_logueo"] = $rs->ultimo_logueo;
                        $record["numero_logueos"] =$rs->numero_logueos;
                        Usuario::where("idusuario", $rs->idusuario)->update(json_decode(json_encode($record),true));
                    }else{

                        $cfg = Configuracion::where("idcliente", $rs->idcliente)->get();
                        foreach ($cfg as $key => $value) {
                            $cfgrs = $value;
                        }
    
                        $fecha_limite_validez_clave = $cfgrs["fecha_limite_validez_clave"];
                        if ($fecha_limite_validez_clave && $fecha_limite_validez_clave < date("Y-m-d H:i:s")){
                            $status = false;
                            $mensaje = "CADUCIDAD GENERAL: No se encuentra autorizado para ingresar dado a la fecha límite de caducidad de contraseña que esta determinado en {$fecha_limite_validez_clave}. Consulte con Administración";
                            $data = [];
                            $aud->saveAuditoria([
                                "idusuario" => $rs->idusuario,
                                "mensaje" => $mensaje
                            ]);
                        }else{
                            if (!$rs->clave_expiracion){
                                $rs->clave_expiracion = UsuariosController::setCaducidad($rs->idcliente);
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
                                    } else {
                                        $val_token = $this->validateToken($rs->token);
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
                                        $codigo = $this->generacionCodigoVerificacion($rs->idusuario);
                                        Controller::enviarMensaje($rs->idusuario, "Codigo de verificacion para LISAH es: {$codigo}");
                                        $record["verificacion_codigo"] = $codigo;
                                        $record["verificacion_expira"] = date('Y-m-d H:i:s', (strtotime ("+5 Minute")));
                                        $status = true;
                                    }
                                }
        
                                if ($status){
                                    Usuario::where("idusuario", $rs->idusuario)->update(json_decode(json_encode($record),true));
                                    $aud->saveAuditoria([
                                        "idusuario" => $rs->idusuario,
                                        "mensaje" => "Logueado"
                                    ]);
                                }
                            }
                        }
                    }

                }
            }
            
        }else{
            $status = false;
            $mensaje = "Usuario no existe o se encuentra inactivo. Contacte al administrador.";
            $data = [];
            $aud->saveAuditoria([
                "json" => ["usuario" => $usuario],
                "mensaje" => $mensaje
            ]);
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }

    public function logout(Request $request){
        $status = "";
        $data = [];
        $mensaje = "";

        $payload = (Object) Controller::tokenSecurity($request);
        if ($payload->validate){
            $status = true;
            $data = [];
            $user = Usuario::where("idusuario", $payload->payload["idusuario"])->get();
            foreach ($user as $key => $value) {
                $rs = $value;
            }

            $rs->numero_deslogueos = intval($rs->numero_deslogueos) + 1;
            $record["numero_deslogueos"] =$rs->numero_deslogueos;
            
            Usuario::where("idusuario", $payload->payload["idusuario"])->update(json_decode(json_encode($record),true));

            $mensaje = "Cerrada la sesion con exito";
            $status = true;
            $data = [];


            $aud = new AuditoriaUsoController();
            $aud->saveAuditoria([
                "idusuario" => $rs->idusuario,
                "json" => null,
                "mensaje" =>  $mensaje
            ]);
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }

        return Controller::reponseFormat($status, $data, $mensaje);
    }

    public function resetPassword(Request $request)
    {
        $usuario = $request->input("usuario");  
        $clave = $request->input("clave");

        $aud = new AuditoriaUsoController();

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
                UsuariosController::setPassword($rs->idusuario, $rs->idcliente);
            } else {
                if ( Controller::encode($clave) != $rs->clave ){
                    $status = false;
                    $mensaje = "Contraseña erronea";
                    $data = [];
                    $aud->saveAuditoria([
                        "idusuario" => $rs->idusuario,
                        "json" => [
                            "usuario" => $usuario,
                        ],
                        "mensaje" => $mensaje
                    ]);
                    Controller::enviarMensaje($rs->idusuario, $mensaje);
                } else {
                    UsuariosController::setPassword($rs->idusuario, $rs->idcliente);
                    $mensaje = "Se ha establecido nueva contraseña";
                    $aud->saveAuditoria([
                        "idusuario" => $rs->idusuario,
                        "mensaje" => $mensaje
                    ]);
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


    public function genToken($obj, $especial=false){
        
        $idcliente = 0;
        $idgrupo_usuario = 0;
        $idrol = $obj->idrol;
        $idusuario = $obj->idusuario;

        if (!$especial){
            $cfg = Configuracion::where("idcliente", $obj->idcliente)->get();
            foreach ($cfg as $key => $value) {
                $rs = $value;
            }
            $tiempo_caducidad_token_usuarios = $rs["tiempo_caducidad_token_usuarios"];
            $fecha_limite_validez_clave = $rs["fecha_limite_validez_clave"];
    
            $caducidad_token = date("Y-m-d H:i:s", strtotime('+' . $tiempo_caducidad_token_usuarios . ' hours'));
            
            if ($fecha_limite_validez_clave){
                $limite_caducidad = $fecha_limite_validez_clave . " 23:59:59";
                if ($caducidad_token > $limite_caducidad){
                        $caducidad_token = $limite_caducidad;
                }
            }
            $idcliente = $obj->idcliente;
            $idgrupo_usuario = $obj->idgrupo_usuario;
        } else {
            $idrol = 1;
            $caducidad_token  = date("Y-m-d H:i:s", strtotime('+10 year'));
        }
        
        $rec = [
            "idcliente" => $idcliente,
            "idusuario" => $idusuario,
            "idrol" => $idrol,
            "idgrupo_usuario" => $idgrupo_usuario,
            "expire_date" => $caducidad_token
        ];

        return base64_encode(Controller::encode(json_encode($rec)));
    }

    public function validateToken($token){
        $conclusion = false;
        $msg = "";
        
        $payload = json_decode(Controller::decode(base64_decode($token)), true);
        if ($payload){
            if ($payload["expire_date"] >= date("Y-m-d H:i:s")){
                $conclusion = true;
                $msg = "";
            } else {
                $conclusion = false;
                $msg = "expire";
            }

            $cfg = Configuracion::where("idcliente", $payload["idcliente"])->get();
            foreach ($cfg as $key => $value) {
                $rs = $value;
            }
            $fecha_limite_validez_clave = $rs["fecha_limite_validez_clave"];

            if ($fecha_limite_validez_clave && $payload["idgrupo_usuario"]){
                
                $limite_caducidad =  $fecha_limite_validez_clave . " 23:59:59";

                if (Controller::validateDate($limite_caducidad)){
                    if ($payload["expire_date"] > $limite_caducidad){
                        $conclusion = false;
                        $msg = "expire";
                    }
    
                    $record_u = [ 
                        "estado" => 0 ,
                        "clave_expiracion" => $limite_caducidad
                    ];
                    Usuario::where("idcliente", $payload["idcliente"])->update($record_u);
    
                    $aud = new AuditoriaUsoController();
                    $aud->saveAuditoria([
                        "idusuario" => $payload->payload["idusuario"],
                        "mensaje" => "Caducidad de contraseñas general se desactiva usuarios"
                    ]);
                }
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
