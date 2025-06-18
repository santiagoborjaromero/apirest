<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaUso;
use App\Http\Requests\StoreAuditoriaUsoRequest;
use App\Http\Requests\UpdateAuditoriaUsoRequest;

class AuditoriaUsoController extends Controller
{
    public function saveAuditoria($data)
    {   
        $data["metodo"] = $_SERVER["REQUEST_METHOD"];
        $data["ruta"] = $_SERVER["REQUEST_URI"];
        $data["ipaddr"] = $this->get_client_ip();
        if (isset($data["json"])){
            $data["json"] = json_encode($data["json"]);
        }
        AuditoriaUso::create($data);
    }

    public function get_client_ip() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

}

                                   
