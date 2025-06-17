<?php
namespace App\Filters;
use Illuminate\Http\Request;
use App\Filters\ApiFilter;

class ClientesFilter extends ApiFilter{

    protected $safeParams  = [
        "nombre" => ["eq"],
        "identificacion" => ["eq"],
        "direccion" => ["eq"],
        "telefono" => ["eq"],
    ];
    protected $columnMap   = [];
    protected $operatorMap = [
        "eq" => "=",
        "lt" => "<",
        "lte" => "<=",
        "gt" => ">",
        "gte" => ">="
    ];
}