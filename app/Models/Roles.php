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
        return $this->belongsToMany(Menu::class, 'rol_menu', 'idrol', 'idmenu');
    }

    

    
}
