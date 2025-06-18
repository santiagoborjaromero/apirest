<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ColaComandos extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "cola_comandos";
    protected $primaryKey = "idcola_comando";
    protected $fillable = [];
    protected $hidden = [];

    //idcola_procesos
}
