<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditoriaUso extends Model
{
    use HasFactory;
    // use SoftDeletes;
    public $timestamps = true;
    protected $table = "auditoria_uso";
    protected $primaryKey = "idauditoria_uso";
    protected $guarded = ["created_at", "updated_at"];
    // protected $fillable = [
    //     "idusuario",
    //     "metodo",
    //     "ruta",
    //     "ipaddr",
    //     "json",
    //     "mensaje",
    // ];
}
