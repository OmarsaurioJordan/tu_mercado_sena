<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\Integridad;

class IntegridadController extends Controller
{
    public function index()
    {
        return response()->json(Integridad::all(), 200);
    }
}
