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

    public function fromModel(Chat $chat, bool $bloqueo_mutuo): self
    {
        return new self(
            id: $chat->id,
            usuario: $bloqueo_mutuo === false ? 
                ($chat->producto->relationLoaded("vendedor") && $chat->producto->vendedor
                ? [
                    'id' => $chat->producto->vendedor?->id,
                    'nickname' => $chat->producto->vendedor?->nickname,
                    'imagen' => $chat->producto->vendedor?->imagen
                ] : null)
                : ($chat->producto->relationLoaded("vendedor") && $chat->producto->vendedor
                    ? [
                        'id' => $chat->producto->vendedor?->id,
                        'nickname' => $chat->producto->vendedor?->nickname
                    ] : null) ,
            ultimoMensajeTexto: $chat->ultimoMensaje?->mensaje ?? 'Sin mensajes aún',
            fechaUltimoMensaje: $chat->ultimoMensaje?->fecha_registro
        );
    }

    public static function fromModelCollection(ModelCollection $chats, int $usuario_id, array $mapaBloqueos): array
    {
        return $chats->map(function ($chat) use ($usuario_id, $mapaBloqueos) {
                // Determinamos quién es el otro
                $otroId = ($chat->comprador_id === $usuario_id) 
                    ? $chat->producto->vendedor_id 
                    : $chat->comprador_id;

                // Si el ID del otro está en el mapa de bloqueos, bloqueo_mutuo es true
                $estaBloqueado = in_array($otroId, $mapaBloqueos);

                return self::fromModel($chat, $estaBloqueado)->toArray();
            })->all();    
    }
}
