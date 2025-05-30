<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cerveza extends Model
{
    protected $fillable = ['nombre', 'precio', 'marca_id', 'graduacion', 'tipo_envase', 'estilo_id', 'ibu', 'capacidad', 'imagen', 'descripcion'];

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    public function estilo()
    {
        return $this->belongsTo(Estilo::class);
    }

}
