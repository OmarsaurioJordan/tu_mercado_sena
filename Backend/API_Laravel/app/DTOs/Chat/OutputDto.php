<?php

namespace App\DTOs\Chat;

use Illuminate\Contracts\Support\Arrayable;
use App\Models\Chat;
use Illuminate\Database\Eloquent\Collection as ModelCollection;

class OutputDto implements Arrayable
{
    public function __construct(
        public int $id
    )
    {}

    // Método toArray
    public function toArray(): array
    {
        return [
            'id' => $this->id
        ];
    }

    public static function formModel(Chat $chat): self
    {
        return new self(
            id: $chat->id
        );
    }

    public static function fromModelCollection(ModelCollection $chats): array
    {
        return $chats->map(fn ($chat) => self::formModel($chat)->toArray())->all();
    }
}
