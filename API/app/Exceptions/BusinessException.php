<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class BusinessException extends Exception
{
    protected $code;

    public function __construct(string $message = "Error", int $code = 422)
    {
        parent::__construct($message, $code);
        $this->code = $code;
    }

    /**
     * Renderizar la excepción en una respuesta JSON.
     */

    public function render($request): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'type' => class_basename($this),
            'message' => $this->getMessage(),
        ], $this->code);
    }
}
