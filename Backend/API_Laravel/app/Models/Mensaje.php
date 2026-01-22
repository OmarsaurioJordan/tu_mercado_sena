<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Chat;

class Mensaje extends Model
{
    
    // Relación 1-1
    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }
}
