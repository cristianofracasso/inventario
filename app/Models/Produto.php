<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $connection = "sqlsrv";

    protected $table = "produtos";
    protected $fillable = [
        'id',
        'codigo_barras',
        'controla_numero_serie',
        'ativo'
        
        
    ];
}
