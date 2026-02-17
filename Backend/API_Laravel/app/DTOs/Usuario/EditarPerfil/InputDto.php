<?php

namespace App\DTOs\Usuario\EditarPerfil;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;



class InputDto implements Arrayable
{
    public function __construct(
        public ?UploadedFile $imagen = null,
        public ?string $nickname = null,
        public ?string $descripcion = null,
        public ?string $link = null
    )
    {}

    public static function fromRequest(Request $request): self 
    {
        return new self(
            imagen: $request->file('imagen') ?? null,
            nickname: $request->input('nickname') ?? null,
            descripcion: $request->input('descripcion') ?? null,
            link: $request->input('link') ?? null
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
