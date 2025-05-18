<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoFermentacion extends Model
{
    protected $fillable = ['nombre', 
                           'descripcion',
                           'levadura',
                           'temperatura',
                           'tiempo',
                        ];

    public function estilos(){
        return $this->hasMany(Estilo::class);
    }
}
