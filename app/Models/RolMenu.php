<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RolMenu extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "rol_menu";
    protected $primaryKey = "idrol_menu";
    protected $fillable = [];
    protected $hidden = [];

    public function roles(): BelongsTo
    {
        return $this->belongsTo(Roles::class, "idrol", "idrol");
    }

    public function menu(): HasMany
    {
        return $this->hasMany(Menu::class, "idmenu", "idmenu");
    }
}
