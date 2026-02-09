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
        public bool $visto_comprador,
        public bool $visto_vendedor,
        public ?string $fechaUltimoMensaje
    )
    {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'usuario' => $this->usuario,
            'visto_comprador' => $this->visto_comprador,
            'visto_vendedor' => $this->visto_vendedor,
            'ultimoMensajeTexto' => $this->ultimoMensajeTexto,
            'fechaUltimoMensaje' => $this->fechaUltimoMensaje
        ];
    }

    public static function fromModel(Chat $chat, int $usuario_id, bool $bloqueo_mutuo): self
    {
        $esComprador = $chat->comprador_id === $usuario_id;

        $otroUsuario = $esComprador
            ? $chat->producto->vendedor
            : $chat->comprador;

        return new self(
            id: $chat->id,
            usuario: $otroUsuario
                ? [
                    'id' => $otroUsuario->id,
                    'nickname' => $otroUsuario->nickname,
                    'imagen' => $bloqueo_mutuo ? null : $otroUsuario->imagen
                ]
                : [],
            visto_comprador: $chat->visto_comprador,
            visto_vendedor: $chat->visto_vendedor,
            ultimoMensajeTexto: $chat->ultimoMensaje?->mensaje ?? 'Sin mensajes aún',
            fechaUltimoMensaje: $chat->ultimoMensaje?->fecha_registro
        );
    }

    public static function fromModelCollection(
        ModelCollection $chats,
        int $usuario_id,
        array $mapaBloqueos
    ): array {
        return $chats->map(function ($chat) use ($usuario_id, $mapaBloqueos) {

            $otroId = ($chat->comprador_id === $usuario_id)
                ? $chat->producto->vendedor_id
                : $chat->comprador_id;

            $bloqueoMutuo = in_array($otroId, $mapaBloqueos);

            return self::fromModel($chat, $usuario_id, $bloqueoMutuo)->toArray();
        })->all();
    }
}
