<?php

namespace App\Http\Controllers;

use App\Mail\EnvioMails;
use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

abstract class Controller
{
    static $passphrase = "7PToGGTJ71knRd86WF39wfj619qewnbZ"; //Clave de 32 bits
    static $iv         = "cAbBrz3Lzy4Ucwhx"; //Vector de Inicializacion de 16 bits
    static $cifrado    = "AES-256-CBC";
    static $options    = OPENSSL_RAW_DATA;

    public static function reponseFormat($status, $data, $msg){
        $resp = [
            "status" => $status,
            // "data" => $data,
            // "message" => $msg,
            "data" => Controller::encode(json_encode($data)),
            "message" => Controller::encode($msg),
        ];
        $response =  json_encode($resp, JSON_PRETTY_PRINT);
        // error_log($response);
        return $response;
    }

    static public function encode($data){

        $ciphertext = openssl_encrypt(
            $data, 
            Controller::$cifrado,
            Controller::$passphrase, 
            Controller::$options, 
            Controller::$iv
        );

        $encrypted = base64_encode($ciphertext);
        return $encrypted;
    }

    static public function decode($encrypted){
        $ciphertext = base64_decode($encrypted);
        $decrypted = openssl_decrypt(
            $ciphertext,
            Controller::$cifrado,
            Controller::$passphrase,
            Controller::$options,
            Controller::$iv
        );
        return $decrypted;
    }

    static public function tokenSecurity(Request $request){
        $token = "";
        $conclusion = false;
        $msg = "";
        $data = [];
        
        //TODO: Extrae la cabecera
        $auth = $request->header('Authorization');
        $auth_arr = explode(" ",$auth);
        if ($auth_arr[0] == "Bearer"){
            if ($auth_arr[1] != ""){
                $token = $auth_arr[1];
                $payload = json_decode(Controller::decode(base64_decode($token)), true);
                if ($payload){
                    if ($payload["expire_date"] >= date("Y-m-d H:i:s")){
                        $conclusion = true;
                        $msg = "";
                        $data = $payload;
                    } else {
                        $conclusion = false;
                        $msg = "Token expirado, realice el proceso de loguearse nuevamente";
                    }
                }else{
                    $conclusion = false;
                    $msg = "Token invalido";
                }
            }else{
                $conclusion = false; 
                $token = "";
                $msg = "El token no puede estar vacio";
            }
        }else{
            $conclusion = false; 
            $token = "";
            $msg = "El token esta mal formado es de tipo Bearer";
        }
        
        return ["validate" => $conclusion, "mensaje" => $msg, "payload" => $data];
    }

    static public function enviarMensaje($idusuario, $texto){

        $data_u = Usuario::where("idusuario", $idusuario)->get();
        foreach ($data_u as $key => $value) {
            $rs_usuario = $value;
        }
        $idcliente = $rs_usuario->idcliente;
        
        
        if ($idcliente === null){
            $segundo_factor_activo = true;
            $segundo_factor_metodo = "ntfy";
        } else {
            $data = Configuracion::select("segundo_factor_activo", "segundo_factor_metodo")->where("idcliente", $idcliente)->get();
            foreach ($data as $key => $value) {
                $rs = $value;
            }
            $segundo_factor_activo = $rs["segundo_factor_activo"];
            $segundo_factor_metodo = $rs["segundo_factor_metodo"];
        }
        
        if ($segundo_factor_activo == 1){
            if ($segundo_factor_metodo == "ntfy"){
                $cmd = 'curl -d "'.$texto.'" ntfy.sh/lisah_'.$rs_usuario->ntfy_identificador;
                error_log($cmd);
                shell_exec($cmd);
            }elseif ($segundo_factor_metodo == "email"){

            }
        }
        
    }


    static public function generacionClave(){
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
