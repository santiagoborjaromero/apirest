<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Configuracion extends Model
{
    /** @use HasFactory<\Database\Factories\ConfiguracionFactory> */
    use HasFactory;

    public $timestamps = true;
    protected $table = "configuracion";
    protected $primaryKey = "idconfiguracion";
    protected $fillable = [];
    protected $hidden = [];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, "idcliente", "idcliente");
        //Una configuracion tiene un solo cliente
    }
}
