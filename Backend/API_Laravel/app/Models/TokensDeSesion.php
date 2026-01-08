<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokensDeSesion extends Model
{
    protected $table = 'tokens_de_sesion';  

    public $timestamps = true;

    const CREATED_AT = 'fecha_registro';
    const UPDATED_AT = 'fecha_actualiza';

    protected $fillable = [
        'cuenta_id',
        'dispositivo',
        'jti',
        'ultimo_uso'
    ];

    protected $casts = [
            'cuenta_id' => 'integer',
            'ultimo_uso' => 'datetime',
            'fecha_registro' => 'datetime',
            'fecha_actualiza' => 'datetime-'
        ];

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class);
    }
}
