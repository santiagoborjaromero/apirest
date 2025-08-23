<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditoriaUso extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table = "auditoria_uso";
    protected $primaryKey = "idauditoria_uso";
    protected $guarded = ["created_at", "updated_at"];


    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, "idusuario", "idusuario");
    }

}
