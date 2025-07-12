<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServidorUsuarios extends Model
{
    use HasFactory;
    // use SoftDeletes;
    public $timestamps = true;
    protected $table = "servidor_usuarios";
    protected $primaryKey = "idservidor_usuario";
    protected $fillable = ["idservidor_usuario"];
}
