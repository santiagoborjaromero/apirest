<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrupoUsuarios extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "grupo_usuarios";
    protected $primaryKey = "idgrupo_usuario";
    protected $fillable = [];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, "idcliente", "idcliente");
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, "idgrupo_usuario", "idgrupo_usuario");
    }

    public function rolmenugrupos(): HasMany
    {
        return $this->hasMany(RolMenuGrupos::class, "idgrupo_usuario", "idgrupo_usuario");
    }
}
