<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

abstract class Controller
{
    static $passphrase = "7PToGGTJ71knRd86WF39wfj619qewnbZ"; //Clave de 32 bits
    static $iv         = "cAbBrz3Lzy4Ucwhx"; //Vector de Inicializacion de 16 bits
    static $cifrado    = "AES-256-CBC";
    static $options    = OPENSSL_RAW_DATA;

    public static function reponseFormat($status, $data, $msg){
        $resp = [
            "status" => $status,
            "data" => $data,
            // "data" => Controller::encode(json_encode($data)),
            "message" => $msg,
        ];
        return json_encode($resp, JSON_PRETTY_PRINT);
    }

    static public function encode($data){
        // return base64_encode(openssl_encrypt($texto, Controller::$cifrado, Controller::$key, 0, Controller::$iv));

        $ciphertext = openssl_encrypt(
            $data, 
            Controller::$cifrado,
            Controller::$passphrase, 
            Controller::$options, 
            Controller::$iv
        );

        $encrypted = base64_encode(Controller::$iv . $ciphertext);
        return $encrypted;
    }

    static public function decode($encrypted){
        // $decrypted = openssl_decrypt(base64_decode($texto), Controller::$cifrado ,Controller::$passphrase, Controller::$options, Controller::$iv);
        $data = base64_decode($encrypted);
        $ivLength = openssl_cipher_iv_length(Controller::$cifrado);
        $iv = substr($data, 0, $ivLength);
        $ciphertext = substr($data, $ivLength);
        
        $decrypted = openssl_decrypt(
            $ciphertext,
            Controller::$cifrado,
            Controller::$passphrase,
            Controller::$options,
            $iv
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


    
}
