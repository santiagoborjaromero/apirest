<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ColaProcesos extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "cola_procesos";
    protected $primaryKey = "idcola_proceso";
    protected $fillable = [];
    protected $hidden = [];

    //idservidor, idusuario
}
