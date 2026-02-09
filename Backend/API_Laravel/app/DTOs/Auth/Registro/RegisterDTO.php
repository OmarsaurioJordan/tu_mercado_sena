<?php

namespace App\DTOs\Auth\Registro;

use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;


final class RegisterDTO
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $email,
        public string $password,
        public string $nickname,
        public ?UploadedFile $imagen,
        public ?string $ruta_imagen,
        public int $estado_id,
        public int $rol_id,
        public ?string $descripcion = null,
        public ?string $link = null,
        public string $device_name
    ){}

    // Crear una instacia del DTO a partir de un array de datos (procedente del request)
    public static function fromRequest(Request $request): self {
        return new self(
            email: $request->input('email'),
            password: $request->input('password'),
            nickname: $request->input('nickname'),
            imagen: $request->file('imagen'), // 👈 UploadedFile|null
            ruta_imagen: null,
            rol_id: $request->input('rol_id'),
            estado_id: $request->input('estado_id'),
            descripcion: $request->input('descripcion'),
            link: $request->input('link'),
            device_name: $request->input('device_name', 'web')
        );
    }

    // Convertir el DTO a un array (para usar en la creación del usuario)
    public function toArray(?string $rutaImagen = null): array {
        return [
            'email' => $this->email,
            'password' => $this->password,
            'nickname' => $this->nickname,
            'imagen' => null,
            'ruta_imagen' => $rutaImagen,
            'rol_id' => $this->rol_id,
            'estado_id' => $this->estado_id,
            'descripcion' => $this->descripcion,
            'link' => $this->link,
            'device_name' => $this->device_name
        ];
    }

    public static function fromArray(array $data): self {
        return new self(
            email: $data['email'],
            password: $data['password'],
            nickname: $data['nickname'],
            imagen: null,
            ruta_imagen: $data['ruta_imagen'],
            rol_id: $data['rol_id'],
            estado_id: $data['estado_id'],
            descripcion: $data['descripcion'] ?? null,
            link: $data['link'] ?? null,
            device_name: $data['device_name'] ?? 'web'
        );
    }
}
