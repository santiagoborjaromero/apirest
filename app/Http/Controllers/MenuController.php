<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use Exception;
use Illuminate\Http\Request;


class MenuController extends Controller
{
    


    public function getAll(Request $request)
    {
        $payload = Controller::tokenSecurity($request)["payload"];
        // {"ref":idusuario,"paq":idcliente,"task":idrol,"expire_date":"2025-06-19 02:29:07"}
        $data = Menu::All();
        return Controller::reponseFormat(true, $data, "") ;
    }


    public function getOne($id)
    {
        $status = false;
        $data = "";
        if (isset($id)){
            $status = true;
            $data = Menu::where("idmenu", "=", $id)->get();
        }else{
            $data = "ID esta vacío";
        }
        return ["status" => $status, "data" => $data];
    }

    public function saveNew(Request $request)
    {
        $aud = new AuditoriaUsoController();

        $payload = Controller::tokenSecurity($request);
        $payload_data = Controller::tokenSecurity($request)["payload"];
        if (!$payload["validate"]){
            $status = false;
            $data = $payload["mensaje"];
        }else{
            $record = $request->input("data");
            // $record = [];
            // $record["orden"] =  $request->input("orden");
            // $record["nombre"] =  $request->input("nombre");
            // $record["icono"] =  $request->input("icono");
            // $record["ruta"] =  $request->input("ruta");
            // $record["es_submenu"] =  $request->input("es_submenu");
            // $record["estado"] =  $request->input("estado");
            try{
                $data = Menu::created($record);
                $status = true;
                $aud->saveAuditoria([
                    "idusuario" => $payload_data->idusuario,
                    "json" => $record
                ]);
            } catch( Exception $err){
                $status = false;
                $data = $err;
            }
        }
        return ["status" => $status, "data" => $data];
    }







    public function delete($id)
    {
        $status = false;
        $data = "";
        if (isset($id)){
            $status = true;
            $data = Menu::where("idmenu", "=", $id)->get();
            if (count($data)>0){
                Menu::where("idmenu", "=", $id)->delete();
            }
        }else{
            $data = "ID esta vacío";
        }
        return ["status" => $status, "data" => []];
    }



}
