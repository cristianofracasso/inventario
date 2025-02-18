<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'id',
        'nome',
        'status',
        'coleta'
      
    ];
}
