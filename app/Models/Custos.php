<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Custos extends Model
{
    protected $connection = "sqlsrv";

    protected $table = "rel_estoque_saldos";
    protected $fillable = [
        'produto_id',
        'empresa_id',
        'destino_estoque_id',
        'valor_custo_medio'
                
    ];
}
