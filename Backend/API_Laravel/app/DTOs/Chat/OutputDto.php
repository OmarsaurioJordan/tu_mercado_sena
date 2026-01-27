<?php

namespace App\DTOs\Chat;
use Illuminate\Contracts\Support\Arrayable;
use App\Models\Chat;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as ModelCollection;

readonly class OutputDto implements Arrayable
{
    public function __construct(
        public int $id,
        public array $usuario,
        public string $ultimoMensajeTexto,
        public Carbon $fechaUltimoMensaje
    )
    {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'usuario' => $this->usuario,
            'ultimoMensajeTexto' => $this->ultimoMensajeTexto,
            'fechaUltimoMensaje' => $this->fechaUltimoMensaje
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
            ultimoMensajeTexto: $chat->ultimoMensaje?->mensaje ?? 'Sin mensajes aún',
            fechaUltimoMensaje: $chat->ultimoMensaje?->fecha_registro
        );
    }

    public static function fromModelCollection(ModelCollection $chats): array
    {
        return $chats->map(fn ($chat) => self::fromModel($chat)->toArray())->all();
    }
}
