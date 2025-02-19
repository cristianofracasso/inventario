<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $primaryKey = 'id'; // Define o campo id como chave primária
    public $incrementing = false; // Indica que o id não é auto-incrementável
    protected $keyType = 'string'; // Define o tipo da chave primária como string

    protected $fillable = [
        'id',
       
       
        
    ];
    public function coletas()
    {
        return $this->hasMany(Coleta::class, 'codigo_palet', 'id');
    }
  
}
