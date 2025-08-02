<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServidoresFamilia extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table = "servidores_familia";
    protected $primaryKey = "idservidores_familia";
    protected $guarded = ["idservidores_familia"];

    
}
