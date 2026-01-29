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
        public array $mensajes,
        public ?int $cantidad,
        public ?int $calificacion,
        public ?string $comentario,
        public ?Carbon $fecha_venta
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'comprador_id' => $this->comprador_id,
            'producto' => $this->producto,
            'estado_id' => $this->estado_id,
            'visto_comprador' => $this->visto_comprador,
            'visto_vendedor' => $this->visto_vendedor,
            'mensajes' => $this->mensajes,
            'cantidad' => $this->cantidad,
            'calificacion' => $this->calificacion,
            'comentario' => $this->comentario,
            'fecha_venta' => $this->fecha_venta?->toDateTimeString()
        ];
    }

    public static function fromModel(Chat $chat): self
    {
        return new self(
            id: $chat->id,
            comprador_id: $chat->comprador_id,
            producto: $chat->relationLoaded('producto') && $chat->producto
                ? [
                    'id' => $chat->producto->id,
                    'nombre' => $chat->producto->nombre,
                    // Lógica para obtener la primera foto disponible
                    'imagen' => $chat->producto->relationLoaded('fotos') 
                                ? $chat->producto->fotos->first()?->imagen 
                                : null,
                    'vendedor' => $chat->producto->relationLoaded('vendedor') && $chat->producto->vendedor
                        ? [
                            'id' => $chat->producto->vendedor->id,
                            'nickname' => $chat->producto->vendedor->nickname,
                            'imagen' => $chat->producto->vendedor->imagen
                        ] : null,
                ] : [],
            estado_id: $chat->estado_id,
            visto_comprador: (bool) $chat->visto_comprador,
            visto_vendedor: (bool) $chat->visto_vendedor,
            mensajes: $chat->relationLoaded('mensajes')
                ? $chat->mensajes->map(fn($m) => [
                    'id' => $m->id,
                    'es_comprador' => (bool) $m->es_comprador, // Casteo a bool por seguridad
                    'chat_id' => $m->chat_id,
                    'mensaje' => $m->mensaje,
                    'imagen' => $m->imagen,
                    'fecha' => $m->fecha_registro ? $m->fecha_registro->toDateTimeString() : null,
                ])->toArray()
                : [],
            cantidad: $chat->cantidad,
            calificacion: $chat->calificacion,
            comentario: $chat->comentario,
            fecha_venta: $chat->fecha_venta ? Carbon::parse($chat->fecha_venta) : null,
        );
    }
}