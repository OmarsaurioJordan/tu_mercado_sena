<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario;

class Estado extends Model
{
    protected $table = 'estados';
    
    public function usuario()
    {
        return $this->hasMany(Usuario::class);
    }
}
