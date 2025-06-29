<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GrupoUsuariosController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\UsuariosController;
use App\Http\Middleware\EnsureTokenIsValid;
use App\Models\GrupoUsuarios;
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
    Route::get("email", [UsuariosController::class, 'envioMails']);


    
});

//Private
Route::group([
    "prefix"=>"v1", 
    "namespace" => "App\Http\Controller",
    "middleware" => "private"
], function (){
    Route::post("codigoverificador/{codigo}", [AuthController::class, 'verificarCodigo']);
    Route::post("regenerarcodigo", [AuthController::class, 'regenerarCodigo']);

    Route::get("menus", [MenuController::class, 'getAll']);
    Route::get("menus/{id}", [MenuController::class, 'getOne']);
    Route::post("menu", [MenuController::class, 'saveNew']);

    Route::get("config", [ConfiguracionController::class, 'getAll']);
    Route::put("config", [ConfiguracionController::class, 'update']);


    Route::get("clientes", [ClientesController::class, 'getAll']);
    Route::get("clientes/{id}", [ClientesController::class, 'getOne']);
    
    Route::get("usuarios", [UsuariosController::class, 'getAll']);
    Route::get("usuarios_filter/{accion}", [UsuariosController::class, 'getAllFiltro']);
    Route::get("usuarios/{id}", [UsuariosController::class, 'getOne']);
    Route::post("usuario", [UsuariosController::class, 'save']);
    Route::put("usuario/{id}", [UsuariosController::class, 'update']);
    Route::delete("usuario/{id}", [UsuariosController::class, 'delete']);
    
    Route::get("grupousuarios", [GrupoUsuariosController::class, 'getAll']);
    Route::get("grupousuarios/cliente/{id}", [GrupoUsuariosController::class, 'getAllFromClient']);


// });
});