<?php

namespace App\Http\Controllers\Api;

use App\Models\Estado;

class EstadosController extends Controller
{
    public function index()
    {
        return Estado::whereIn('id', [1, 5, 6, 7, 8])->get();
    }
}
