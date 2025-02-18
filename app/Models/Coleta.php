<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coleta extends Model
{
    protected $fillable = [
        'id',
        'sku',
        'custo',
        'codigo_palet',
        'serial',
        'grupo',
        'contagem'
    ];
    public function area()
    {
        return $this->belongsTo(Area::class, 'codigo_palet', 'id');
    }
}
