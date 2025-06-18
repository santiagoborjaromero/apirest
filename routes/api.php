<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\UsuariosController;
use App\Http\Middleware\EnsureTokenIsValid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

//Public
Route::group(["prefix"=>"v1", "namespace" => "App\Http\Controller"], function (){
    Route::get("healthy", function(){
        return ["status" => true, "data" => "Estado server activo"];
    });
    Route::post("login", [AuthController::class, 'authUser']);
    
});

//Private
Route::group([
    "prefix"=>"v1", 
    "namespace" => "App\Http\Controller",
    "middleware" => "private"
], function (){
    Route::get("clientes", [ClientesController::class, 'getAll']);
    Route::get("clientes/{id}", [ClientesController::class, 'getOne']);
    
    Route::get("usuarios", [UsuariosController::class, 'getAll']);
    Route::get("usuarios/{id}", [UsuariosController::class, 'getOne']);
// });
});