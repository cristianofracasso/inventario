<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $fillable = [
        'id',
       
       
        
    ];
    public function coletas()
    {
        return $this->hasMany(Coleta::class, 'codigo_palet', 'id');
    }
  
}
