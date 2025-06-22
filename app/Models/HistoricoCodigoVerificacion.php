<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HistoricoCodigoVerificacion extends Model
{
    // use HasFactory;
    // use SoftDeletes;
    public $timestamps = true;
    protected $table = "historico_codigo_verificacion";
    protected $primaryKey = "idcodigoverificacion";
    protected $fillable = ["idusuario", "codigo"];
}
