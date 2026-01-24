<?php

namespace App\DTOs\Chat;
use Illuminate\Contracts\Support\Arrayable;
use App\Models\Chat;
use Illuminate\Database\Eloquent\Collection as ModelCollection;

readonly class OutputDto implements Arrayable
{
    public function __construct(
        public int $id,
        public array $usuario,
        public array $mensaje
    )
    {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'usuario' => $this->usuario,
            'mensaje' => $this->mensaje
        ];
    }

    public function fromModel(Chat $chat): self
    {
        return new self(
            id: $chat->id,
            usuario: $chat->producto->relationLoaded("vendedor") && $chat->producto
            ? [
                'id' => $chat->producto?->id,
                'nombre' => $chat->producto?->nombre,
                'imagen' => $chat->producto?->imagen
            ] : null,
            mensaje: $chat->relationLoaded("mensaje") && $chat->mensaje
            ? [
                $chat->mensaje?->id,
                $chat->mensaje?->es_comprador,
                $chat->mensaje?->mensaje,
                $chat->mensaje?->imagen,
                $chat->mensaje?->fecha_registro
            ] : null
        );
    }

    public static function fromModelCollection(ModelCollection $chats): array
    {
        return $chats->map(fn ($chat) => self::fromModel($chat)->toArray())->all();
    }
}
