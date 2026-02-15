<?php

namespace App\Http\Controllers\Api;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Usuario\EditarPerfilRequest;
use App\Http\Requests\Usuario\BloquearUsuarioRequest;
use App\DTOs\Usuario\EditarPerfil\InputDto as EditarPerfilInputDto;
use App\Contracts\Usuario\Services\IUsuarioService;
use App\Contracts\Usuario\Services\IBloqueadoService;
use App\DTOs\Usuario\Bloqueados\InputDto as BloqueadoInputDto;
use App\Models\Usuario;

class UsuarioController extends Controller
{
    public function __construct(
        private IUsuarioService $usuarioService,
        private IBloqueadoService $bloqueadoService
    ) 
    {}

    public function update(int $id, EditarPerfilRequest $request)
    {
        $dto = EditarPerfilInputDto::fromRequest($request->validated());

        $perfil = $this->usuarioService->update($id, $dto);

        return response()->json([
            'status' => 'success',
            'message' => 'Perfil actualizado correctamente.',
            'data' => $perfil
        ], 200);
    }

    // == MODULO DE BLOQUEO DE USUARIOS == //

    public function bloquearUsuario(BloquearUsuarioRequest $request, Usuario $usuario)
    {
        $dto = BloqueadoInputDto::fromRequest($request->validated());
    
        $resultado = $this->bloqueadoService->ejecutarBloqueo($dto);
    
        return response()->json([
            'status' => 'success',
            'message' => 'Usuario bloqueado correctamente.',
            'data' => $resultado
        ], 201);

    }

    public function desbloquearUsuario(BloquearUsuarioRequest $request, Usuario $usuario)
    {
        $dto = BloqueadoInputDto::fromRequest($request->validated());

        $resultado = $this->bloqueadoService->ejecutarDesbloqueo($dto);
        
        return response()->json($resultado, 200);
    }

    public function obtenerBloqueadosPorUsuario()
    {
        $bloqueados = $this->bloqueadoService->solicitarBloqueadosPorUsuario(Auth::user()->usuario->id);

        return response()->json([
            'status' => 'success',
            'message' => empty($bloqueados) ? 'No hay usuarios bloqueados.' : 'Usuarios bloqueados',
            'data' => $bloqueados
        ], 200);
    }
}
