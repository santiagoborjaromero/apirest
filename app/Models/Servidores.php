<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Servidores extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "servidores";
    protected $primaryKey = "idservidor";
    protected $guarded = ["idservidor"];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, "idcliente", "idcliente");
    }

    public function familia(): BelongsTo
    {
        return $this->belongsTo(ServidoresFamilia::class, "idservidores_familia", "idservidores_familia");
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'servidor_usuarios', 'idservidor', 'idusuario');
    }

    public function comandos(): HasMany
    {
        return $this->hasMany(Comandos::class, "idservidores_familia", "idservidores_familia");
    }

    
}
