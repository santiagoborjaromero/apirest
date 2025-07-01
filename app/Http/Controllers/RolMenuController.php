<?php

namespace App\Http\Controllers;

use App\Models\RolMenu;
use App\Http\Requests\StoreRolMenuRequest;
use App\Http\Requests\UpdateRolMenuRequest;
use Illuminate\Http\Request;

class RolMenuController extends Controller
{
    public function getMenuByClient(Request $request)
    {
        $status = false;
        $data = [];
        $mensaje = "" ;

        $payload = (Object)Controller::tokenSecurity($request);
        if ($payload->validate){
            $status = true;
            $data = RolMenu::where("idrol", "=", 3)->with("menu")->get();
        }else{
            $status = false;
            $mensaje = $payload->mensaje;
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;
    }
}
