<?php

namespace App\DTOs\Auth;

final readonly class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public string $device_name,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password'],
            device_name: $data['device_name'] ?? 'web',
        );
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
            'device_name' => $this->device_name,
        ];
    }
}
