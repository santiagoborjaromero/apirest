<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Servidores extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "servidores";
    protected $primaryKey = "idservidor";
    protected $fillable = [];
    protected $hidden = [];

    //Relacion con 1 idCliente varios servidores
}
