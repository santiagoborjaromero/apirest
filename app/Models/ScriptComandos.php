<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScriptComandos extends Model
{
    
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "script_comandos";
    protected $primaryKey = "idscript_comando";
    protected $fillable = [];
    protected $hidden = [];

    // relacion con idscript
    // relacion con idtemplate_comando

}
