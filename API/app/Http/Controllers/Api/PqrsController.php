<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Pqrs\StorePqrsRequest;
use App\Services\Pqrs\PqrsServices;
use App\DTOs\Pqrs\InputDto;
use App\Models\Pqrs;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\isEmpty;

class PqrsController extends Controller
{
    public function __construct(
        protected PqrsServices $pqrsServices
    ){}

    public function index()
    {
        // Policy que valida que un usuario vea los PQRS de otro usuario
        $this->authorize('viewAny', Pqrs::class);

        $pqrs = Pqrs::with(['usuario:id', 'estado:id,nombre', 'motivo:id,nombre'])
            ->Where('usuario_id', Auth::user()->usuario->id)
            ->get();
        
        return response()->json([
            'success' => true,
            'message' => isEmpty($pqrs) ? 'No hay PQRS realizadas' : 'PQRS obtenidas exitosamente',
            'data' => $pqrs,
        ]);
    }

    public function store(StorePqrsRequest $request)
    {
        // Policy que valida que un usuario cree un PQRS en nombre de otro usuario
        $this->authorize('create', Pqrs::class);

        // Crear la PQRS utilizando el servicio y devolver Json
        $data = InputDto::fromRequest($request->validated());
        $result = $this->pqrsServices->createPqrs($data);
        return response()->json($result, 201);
    }
}
