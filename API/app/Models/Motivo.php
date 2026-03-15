<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Motivo extends Model
{
    protected $table = 'motivos';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion'
    ];

    public function Pqrs()
    {
        $this->hasMany(Pqrs::class, 'motivo_id');
    }

    public function denuncias()
    {
        return $this->hasMany(Denuncia::class, 'motivo_id');
    }
}
