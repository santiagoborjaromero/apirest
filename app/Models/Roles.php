<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Roles extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "roles";
    protected $primaryKey = "idrol";
    protected $fillable = [];

    public function rolmenu(): hasMany
    {
        return $this->hasMany(RolMenu::class, "idrol", "idrol");
    }

    public function menu(): BelongsToMany
    {
        // return $this->belongsToMany(Menu::class, RolMenu::class, "idmenu", "idrol");
        // return $this->belongsToMany(Menu::class, "rol_menu", "idmenu", "idrol");
        // return $this->hasManyThrough(
        //     RolMenu::class, 
        //     Menu::class,
        //     'idmenu', // Foreign key MENU 
        //     'idmenu', // Foreign key ROLMENU
        //     'idrol', // Local key on the USUARIO
        //     'idmenu' // Local key on the MENU
        // );
        return $this->belongsToMany(Menu::class, 'rol_menu', 'idrol', 'idmenu');
    }
}
