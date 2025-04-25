<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estilo extends Model
{
    protected $fillable = ['nombre', 'descripcion', 'tipo_fermentacion_id'];

    public function tipoFermentacion(){
        return $this->belongsTo(TipoFermentacion::class);
    }
}
