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
use App\Http\Requests\Usuario\StoreFavoritoRequest;
use App\Models\Usuario;
use App\Services\Usuario\FavoritoService;

class UsuarioController extends Controller
{
    public function __construct(
        private IUsuarioService $usuarioService,
        private IBloqueadoService $bloqueadoService,
        protected FavoritoService $favoritoService
    ) 
    {}

    public function update(Usuario $usuario, EditarPerfilRequest $request)
    {
        // dd para debbugin
        // dd([
        //     'method' => $request->method(),
        //     'content_type' => $request->header('Content-Type'),
        //     'hasFile' => $request->hasFile('imagen'),
        //     'file' => $request->file('imagen'),
        //     'allFiles' => $request->allFiles(),
        // ]);   
        
        $dto = EditarPerfilInputDto::fromRequest($request);

    
        $perfil = $this->usuarioService->update($usuario->id, $dto);

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

    public function mostrarFavoritos()
    {
        $usuarioId = Auth::user()->usuario->id;

        $resultado = $this->favoritoService->solicitarFavoritosPorUsuario($usuarioId);

        return response()->json($resultado, 200);
    }

    public function añadirAFavoritos(StoreFavoritoRequest $request, Usuario $usuario)
    {
        $datos = $request->validated();

        $resultado = $this->favoritoService->añadirUsuarioAFavoritos($datos['votante_id'], $datos['votado_id']);

        return response()->json($resultado, 201);
    }

    public function eliminarDeFavoritos(Usuario $usuario) {
        $votanteId = Auth::user()->usuario->id;
        $votadoId = $usuario->id;

        $resultado = $this->favoritoService->eliminarUsuarioDeFavoritos($votanteId, $votadoId);

        return response()->json($resultado, 200);
    }
}
