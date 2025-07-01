<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RolMenuGrupos extends Model
{
    use HasFactory;
    // use SoftDeletes;
    public $timestamps = true;
    protected $table = "rolmenu_grupos";
    protected $primaryKey = "idrolmenu_grupos";
    protected $guarded = ["idrolmenu_grupos"];

    public function rolmenu(): HasMany
    {
        return $this->hasMany(RolMenu::class, "idrol_menu", "idrol_menu");
    }
}
