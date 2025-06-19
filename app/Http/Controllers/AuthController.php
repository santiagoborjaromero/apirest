<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAuthRequest;
use App\Http\Requests\UpdateAuthRequest;
use App\Models\AuditoriaUso;
use App\Models\HistoricoClaves;
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

        $data = Usuario::where("usuario", $usuario)->where("estado", 1)->with("cliente", "roles", "roles.menu")->get();

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
                $newclave = $this->generacionClave();
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
                        }

                        if ($status){
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
            "paq" => $obj->idcliente,
            "ref" => $obj->idusuario,
            "task" => $obj->idrol,
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

    public function generacionClave(){
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $clave = '';
        $longitud = 16;
        $max = strlen($caracteres) - 1;
        for ($i = 0; $i < $longitud; $i++) {
            $clave .= $caracteres[random_int(0, $max)];
        }
        return $clave;
    }

}
