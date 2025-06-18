<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HistoricoClaves extends Model
{
    
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = "historico_claves";
    protected $primaryKey = "idhistorico_clave";
    protected $guarded = ["created_at"];

    // relacion idcliente
}
