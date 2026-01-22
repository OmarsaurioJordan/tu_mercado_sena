<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario;
use App\Models\Chat;

class Estado extends Model
{
    protected $table = 'estados';
    
    public function usuario()
    {
        return $this->hasMany(Usuario::class);
    }

    public function chat()
    {
        return $this->hasMany(Chat::class, 'estado_id');
    }
}
