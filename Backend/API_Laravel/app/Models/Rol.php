<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario;

class Rol extends Model
{
    protected $table = 'roles';
    
    public function usuario()
    {
        return $this->hasMany(Usuario::class);
    }
}
