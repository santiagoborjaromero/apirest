<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scripts extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "scripts";
    protected $primaryKey = "idscript";
    protected $fillable = [];
    protected $hidden = [];

    //Relacion con 1 idCliente varios scripts
}
