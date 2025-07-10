<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scripts extends Model
{
    use HasFactory;
    // use SoftDeletes;
    public $timestamps = true;
    protected $table = "scripts";
    protected $primaryKey = "idscript";
    protected $guarded = ["idscript"];

    public function comandos(): HasMany
    {
        return $this->hasMany(ScriptComandos::class, "idscript", "idscript");
    }

    public function cmds(): BelongsToMany
    {
        // return $this->belongsToMany(TemplateComandos::class, 'script_comandos', 'idscript', 'idtemplate_comando');
        return $this->belongsToMany(TemplateComandos::class, 'script_comandos', 'idscript', 'idtemplate_comando')->orderBy('orden', 'asc');
    }


}
