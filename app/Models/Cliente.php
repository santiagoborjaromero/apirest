<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    /** @use HasFactory<\Database\Factories\ClientesFactory> */
    use HasFactory;
    public $timestamps = true;
    protected $table = "clientes";
    protected $primaryKey = "idcliente";
    protected $fillable = [];

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, "idcliente", "idcliente");
        //Un cliente tiene varios usuarios
    }

    public function configuracion(): BelongsTo
    {
        return $this->belongsTo(Configuracion::class, "idcliente", "idcliente");
        //Un cliente tiene una sola configuracion
    }

}
