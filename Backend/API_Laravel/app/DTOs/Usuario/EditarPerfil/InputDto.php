<?php

namespace App\DTOs\Usuario\EditarPerfil;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\Usuario\EditarPerfilRequest;



class InputDto implements Arrayable
{
    public function __construct(
        public ?UploadedFile $imagen = null,
        public ?string $nickname = null,
        public ?string $descripcion = null,
        public ?string $link = null
    )
    {}

    public static function fromRequest(EditarPerfilRequest $request): self
    {
        $data = $request->validated();

        return new self(
            imagen: $request->file('imagen') ?? null,
            nickname: $data['nickname'] ?? null,
            descripcion: $data['descripcion'] ?? null,
            link: $data['link'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter(
            get_object_vars($this),
            fn ($value) => !is_null($value)
        );
    }
}
