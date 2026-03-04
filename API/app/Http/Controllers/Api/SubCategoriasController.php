<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\SubCategoria;
use App\models\Categoria;

class SubCategoriasController extends Controller
{
    public function index(Categoria $categoria) {
        // $subcategorias = SubCategoria::where('categoria_id', $categoria->id)->get();
        $subcategorias = SubCategoria::get();
        return response()->json($subcategorias);
    }
}
