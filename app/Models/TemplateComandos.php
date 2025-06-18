<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TemplateComandos extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "template_comandos";
    protected $primaryKey = "idtemplate_comando";
    protected $fillable = [];
    protected $hidden = [];

    //Relacion con 1 idCliente varios templates

}
