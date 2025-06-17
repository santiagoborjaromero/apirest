<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id("idusuario");
            $table->integer("idroles");
            $table->integer("idcliente");
            $table->integer("idgrupo_usuario");
            $table->string("nombre");
            $table->string("usuario");
            $table->string("clave");
            $table->datetime("clave_expiracion");
            $table->datetime("ultimo_logueo");
            $table->string("token");
            $table->string("keygen");
            $table->string("email");
            $table->integer("email_confirmado");
            $table->integer("estado");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
