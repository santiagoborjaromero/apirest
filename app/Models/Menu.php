<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "menu";
    protected $primaryKey = "idmenu";
    protected $fillable = [];
}
