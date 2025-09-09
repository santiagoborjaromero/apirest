<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConfiguracionRequest;
use App\Http\Requests\UpdateConfiguracionRequest;
use App\Models\Cliente;
use App\Models\Configuracion;
use Exception;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function getAll(Request $request)
    {
        $payload = (Object)Controller::tokenSecurity($request);
        $data = [];
        $mensaje = "";

        if (!$payload->validate){
            $status = false;
            $mensaje = $payload->mensaje;
        } else {
            $status = true;
            $data = Configuracion::with("cliente")
                ->where("idcliente", $payload->payload["idcliente"])
                ->get();
        }

        return Controller::reponseFormat($status, $data, $mensaje);
    }


    public function update(Request $request)
    {
        $payload = (Object)Controller::tokenSecurity($request);
        $data = [];
        $mensaje = "";
        $status = false;

        
        if (!$payload->validate){
            $status = false;
            $mensaje = $payload->mensaje;
        } else {
            $aud = new AuditoriaUsoController();
            $status = true;

            $record_clie = [
                "nombre" => $request->input("cliente_nombre"),
                "identificacion" => $request->input("cliente_identificacion"),
                "direccion" => $request->input("cliente_direccion"),
                "telefono" => $request->input("cliente_telefono"),
                "idusuario" => $request->input("idusuario"),
            ];

            $record_cfg = [
                "idcliente" => $request->input("idcliente"),
                "idusuario" => $request->input("idusuario"),
                "ldap_servidor" => $request->input("ldap_servidor"),
                "ldap_puerto" => $request->input("ldap_puerto"),
                "ldap_usuario" => $request->input("ldap_usuario"),
                "ldap_clave" => $request->input("ldap_clave"),
                "ldap_base_dn" => $request->input("ldap_base_dn"),
                "segundo_factor_activo" => $request->input("segundo_factor_activo"),
                "segundo_factor_metodo" => $request->input("segundo_factor_metodo"),
                // "idscript_creacion_grupo_usuarios" => $request->input("idscript_creacion_grupo_usuarios"),
                "idscript_creacion_usuario" => $request->input("idscript_creacion_usuario"),
                "tiempo_refresco" => $request->input("tiempo_refresco"),
                "tiempo_caducidad_claves" => $request->input("tiempo_caducidad_claves"),
                "tiempo_caducidad_token_usuarios" => $request->input("tiempo_caducidad_token_usuarios"),
                // "tiempo_caducidad_token_agente" => $request->input("tiempo_caducidad_token_agente"),
                "fecha_limite_validez_clave" => $request->input("fecha_limite_validez_clave"),
            ];

            $idconfiguracion = $request->input("idconfiguracion");
            $idcliente = $request->input("idcliente");

            try{
                $status = true;
                $data = Configuracion::where("idconfiguracion", $idconfiguracion)->update($record_cfg);
            } catch(Exception $err){
                $status = false;
                $mensaje = $err;
            }
            try{
                $status = true;
                $data = Cliente::where("idcliente", $idcliente)->update($record_clie);
            } catch(Exception $err){
                $status = false;
                $mensaje = $err;
            }

            $aud->saveAuditoria([
                "idcliente" => $payload->payload["idcliente"],
                "idusuario" => $payload->payload["idusuario"],
                "json" => [
                    "cliente" => $record_clie,
                    "configuracion" => $record_cfg,
                    "descripcion" => "Configuración"
                ]
            ]);
        }

        return Controller::reponseFormat($status, $data, $mensaje);
    }

}
