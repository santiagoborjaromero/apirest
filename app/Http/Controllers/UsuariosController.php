<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsuariosRequest;
use App\Http\Requests\UpdateUsuariosRequest;
use App\Models\Usuario;
use Illuminate\Http\Response;
use Illuminate\Http\Request;


class UsuariosController extends Controller
{

    public function getAll(Request $request)
    {
        $payload = Controller::tokenSecurity($request)["payload"];
         // {"ref":idusuario,"paq":idcliente,"task":idrol,"expire_date":"2025-06-19 02:29:07"}
        if ( $payload["paq"] ===null){
            $data = Usuario::with("cliente", "roles", "roles.menu")->get();
        }else{
            $data = Usuario::where("idcliente", $payload["paq"])->with("cliente", "roles", "roles.menu")->get();
        }
        return Controller::reponseFormat(true, $data, "") ;
    }

    public function getOne($id)
    {
        $status = false;
        $data = [];
        $mensaje = "";
        if (isset($id)){
            $status = true;
            // $data = Usuario::where("idusuario", "=", $id)->with("cliente", "rol")->get();
            $data = Usuario::where("idusuario", $id)->with("cliente", "roles")->get();
            // $data = Usuario::where("usuario", $usuario)->where("estado", 1)->with("cliente", "roles", "roles.menu")->get();

        }else{
            $data = [];
            $mensaje = "ID del usuario esta vacío";
        }
        return Controller::reponseFormat($status, $data, $mensaje) ;

    }


    // /**
    //  * Display a listing of the resource.
    //  */
    // public function index()
    // {
    //     //
    // }

    // /**
    //  * Show the form for creating a new resource.
    //  */
    // public function create()
    // {
    //     //
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(StoreUsuariosRequest $request)
    // {
    //     //
    // }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(Usuarios $usuarios)
    // {
    //     //
    // }

    // /**
    //  * Show the form for editing the specified resource.
    //  */
    // public function edit(Usuarios $usuarios)
    // {
    //     //
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(UpdateUsuariosRequest $request, Usuarios $usuarios)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(Usuarios $usuarios)
    // {
    //     //
    // }
}
