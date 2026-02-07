<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suceso extends Model
{
    protected $table = 'sucesos';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion'
    ];

    public function auditorias()
    {
        return $this->hasMany(Auditoria::class, 'suceso_id');
    }
}
