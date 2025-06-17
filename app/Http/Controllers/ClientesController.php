<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientesRequest;
use App\Http\Requests\UpdateClientesRequest;
use App\Http\Resources\ClientesCollection;
use App\Http\Resources\CustomCollection;
use App\Models\Cliente;
use App\Filters\ClientesFilter;
use Illuminate\Http\Request;

class ClientesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $filter = new ClientesFilter();
        // $queryItems = $filter->transform($request);
        // $clientes = Clientes::where($queryItems);
        // return new ClientesCollection($clientes->paginate()->appends($request->query()));
        $clientes = Cliente::with("configuracion")->get();
        return ["status" => true, "data" => $clientes];
    }

    public function getAll(Request $request)
    {
        $clientes = Cliente::with("configuracion")->get();
        return ["status" => true, "data" => $clientes];
    }

    public function getOne($id)
    {
        $status = false;
        $data = "";
        if (isset($id)){
            $status = true;
            $data = Cliente::where("idcliente", "=", $id)->with("configuracion")->get();
        }else{
            $data = "ID del cliente esta vacío";
        }
        return ["status" => $status, "data" => $data];
    }

}
