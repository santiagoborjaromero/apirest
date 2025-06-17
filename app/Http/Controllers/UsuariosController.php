<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsuariosRequest;
use App\Http\Requests\UpdateUsuariosRequest;
use App\Models\Usuario;
use Illuminate\Http\Response;
use Illuminate\Http\Request;


class UsuariosController extends Controller
{

    public function getAll()
    {
        $clientes = Usuario::with("cliente", "rol")->get();
        return ["status" => true, "data" => $clientes];
    }

    public function getOne($id)
    {
        // var_dump($request->getContent());
        $status = false;
        $data = "";
        if (isset($id)){
            $status = true;
            $data = Usuario::where("idusuario", "=", $id)->with("cliente", "rol")->get();
        }else{
            $data = "ID del usuario esta vacío";
        }
        // return ["status" => $status, "data" => $data];
        $response = ["status" => $status, "data" => $data];
        return response()->json($response);

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
