<?php

namespace App\Models;

use Database\Seeders\RolSeeder;
use Illuminate\Database\Eloquent\Factories\BelongsToRelationship;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Usuario extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "usuarios";
    protected $primaryKey = "idusuario";
    protected $fillable = [];
    protected $hidden = ["clave","verificacion_codigo","verificacion_expira"];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, "idcliente", "idcliente");
    }

    public function config(): BelongsTo
    {
        return $this->belongsTo(Configuracion::class, "idcliente", "idcliente");
    }

    public function roles(): BelongsTo
    {
        return $this->belongsTo(Roles::class, "idrol", "idrol");
    }
   
}
