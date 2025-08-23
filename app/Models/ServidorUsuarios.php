<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServidorUsuarios extends Model
{
    use HasFactory;
    // use SoftDeletes;
    public $timestamps = true;
    protected $table = "servidor_usuarios";
    protected $primaryKey = "idservidor_usuario";
    protected $fillable = ["idservidor_usuario"];

    public function servidor(): BelongsTo
    {
        return $this->belongsTo(Servidores::class, "idservidor", "idservidor");
    }
}
