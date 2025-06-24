<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
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
}
