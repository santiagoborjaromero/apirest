<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Configuracion extends Model
{
    use HasFactory;
    use SoftDeletes;
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

    public function script(): BelongsTo
    {
        return $this->belongsTo(Scripts::class,"idscript_creacion_usuario", "idscript");
    }
}
