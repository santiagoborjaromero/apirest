<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    /** @use HasFactory<\Database\Factories\ClientesFactory> */
    use HasFactory;
    public $timestamps = true;
    protected $table = "roles";
    protected $primaryKey = "idrol";
    protected $fillable = [];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, "idcliente", "idcliente");
        //Un usuario tiene un solo cliente
    }

}
