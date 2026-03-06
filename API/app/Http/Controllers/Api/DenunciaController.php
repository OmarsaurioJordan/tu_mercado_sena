<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Denuncia\DenunciaRequest;
use App\Services\Denuncia\DenunciaService;

class DenunciaController extends Controller
{
    public function __construct(private DenunciaService $service) {}

    public function store(DenunciaRequest $request)
    {
        $denuncia = $this->service->crear($request->validated());
        return response()->json(['success' => true, 'data' => $denuncia], 201);
    }
}
