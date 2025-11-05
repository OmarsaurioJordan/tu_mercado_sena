<?php

namespace App\DTOs\Auth;

final readonly class LoginDTO
{
    public function __construct(
        public string $correo_id,
        public string $password,
        public string $device_name,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            correo_id: $data['correo_id'],
            password: $data['password'],
            device_name: $data['device_name'] ?? 'web',
        );
    }

    /**
     * 
     * 
     */

    public function toArray(): array
    {
        return [
            'correo_id' => $this->correo_id,
            'password' => $this->password,
            'device_name' => $this->device_name,
        ];
    }
}
