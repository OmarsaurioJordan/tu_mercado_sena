<?php

namespace App\DTOs\Chat;

use Illuminate\Contracts\Support\Arrayable;
use Carbon\Carbon;
use App\Models\Chat;
use Illuminate\Database\Eloquent\Collection;

class EstadosOutputDto implements Arrayable
{
    public function __construct(
        public int $id,
        public array $producto,
        public array $usuario,
        public array $estado,
        public ?int $cantidad,
        public ?int $precio,
        public ?int $calificacion,
        public ?string $comentario,
        public ?Carbon $fecha_venta
    )
    {}


    public function toArray(): array
    {
        return [
            "id" => $this->id,
            "producto" => $this->producto,
            "usuario" => $this->usuario,
            "estado" => $this->estado,
            "cantidad" => $this->cantidad,
            "precio" => $this->precio,
            "calificacion" => $this->calificacion,
            "comentario" => $this->comentario,
            "fecha_venta" => $this->fecha_venta
        ];
    }

    public static function fromModel(Chat $chat, int $usuarioId): self
    {

        $esComprador = $chat->comprador_id === $usuarioId;

        $otroUsuario = $esComprador
            ? $chat->producto->vendedor
            : $chat->comprador;

        return new self(
            id: $chat->id,
            producto: $chat->relationLoaded("producto") && $chat->producto
                ? [
                    "id" => $chat->producto->id,
                    "nombre" => $chat->producto->nombre,
                    "imagen" => $chat->producto->relationLoaded('fotos') 
                                    ? $chat->producto->fotos->first()?->imagen 
                                    : null,
                ] : [], // Si no hay una relación de producto retonar array vacio
            usuario: $otroUsuario
                ? [
                    "id" => $chat->producto->vendedor->id,
                    "nickname" => $chat->producto->vendedor->nickname,
                    "avatar" => $chat->producto->vendedor->imagen
                ]: [], // Si no hay una relación de vendedor retonar array vacio
            estado: $chat->relationLoaded('estado') && $chat->estado
                ? [
                    "id" => $chat->estado->id,
                    "nombre" => $chat->estado->nombre
                ] : [],
            cantidad: $chat->cantidad ?? null,
            precio: $chat->precio ?? null,
            calificacion: $chat->calificacion ?? null,
            comentario: $chat->calificacion ?? null,
            fecha_venta: $chat->fecha_venta ?? null
        );
    }

    public static function fromModelCollection(Collection $chats, int $usuarioId): array 
    {
        return $chats->map(fn (Chat $chat) => self::fromModel($chat, $usuarioId)->toArray())->all();
    }
}
