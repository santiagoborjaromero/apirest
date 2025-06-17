<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Usuario extends Model
{
    /** @use HasFactory<\Database\Factories\UsuariosFactory> */
    use HasFactory;
    public $timestamps = true;
    protected $table = "usuarios";
    protected $primaryKey = "idusuario";
    protected $fillable = [];
    protected $hidden = ["clave"];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, "idcliente", "idcliente");
        //Un usuario tiene un solo cliente
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, "idrol", "idrol");
        //Un usuario tiene un solo cliente
    }

}
