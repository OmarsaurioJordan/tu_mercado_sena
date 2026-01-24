<?php

namespace App\DTOs\Chat;

use Illuminate\Contracts\Support\Arrayable;
use App\Models\Chat;
use Carbon\Carbon;

readonly class OutputDetailsDto implements Arrayable
{
    public function __construct(
        public int $id,
        public int $comprador_id,
        public array $producto,
        public int $estado_id,
        public bool $visto_comprador,
        public bool $visto_vendedor,
        public ?int $cantidad,
        public ?int $calificacion,
        public ?string $comentario,
        public ?Carbon $fecha_venta
    )
    {}

    // Método toArray
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'comprador_id' => $this->comprador_id,
            'producto' => $this->producto,
            'estado_id' => $this->estado_id,
            'visto_comprador' => $this->visto_comprador,
            'visto_vendedor' => $this->visto_vendedor,
            'cantidad' => $this->cantidad,
            'calificacion' => $this->calificacion,
            'comentario' => $this->comentario,
            'fecha_venta' => $this->fecha_venta
        ];
    }

    public static function fromModel(Chat $chat): self
    {
        return new self(
            id: $chat->id,
            comprador_id: $chat->comprador->id,
            producto: $chat->relationLoaded('producto') && $chat->producto
            ? [
                'id' => $chat->producto?->id,
                'nombre' => $chat->producto->nombre,
                'vendedor' => $chat->producto->relationLoaded('vendedor') && $chat->producto->vendedor
                ? [
                    'id' => $chat->producto->vendedor?->id,
                    'nickname' => $chat->producto->vendedor?->nickname,
                    'imagen' => $chat->producto->vendedor?->imagen
                ] : null,
            ] : null,
            estado_id: $chat->estado->id,
            visto_comprador: $chat->visto_comprador,
            visto_vendedor: $chat->visto_vendedor,
            cantidad: $chat?->cantidad,
            calificacion: $chat?->calificacion,
            comentario: $chat?->comentario,
            fecha_venta: $chat?->fecha_venta
        );
    }
}