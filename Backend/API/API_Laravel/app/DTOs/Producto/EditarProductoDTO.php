<?php
namespace App\DTOs\Producto;

final readonly class EditarProductoDTO
{
    public function __construct(
        public ?string $nombre,
        public ?string $descripcion,
        public ?float $precio,
        public ?int $disponibles,
        public ?int $subcategoriaId,
        public ?int $integridadId,
        public mixed $imagen,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            nombre: $data['nombre'] ?? null,
            descripcion: $data['descripcion'] ?? null,
            precio: isset($data['precio']) ? (float) $data['precio'] : null,
            disponibles: isset($data['disponibles']) ? (int) $data['disponibles'] : null,
            subcategoriaId: isset($data['subcategoria_id']) ? (int) $data['subcategoria_id'] : null,
            integridadId: isset($data['integridad_id']) ? (int) $data['integridad_id'] : null,
            imagen: $data['imagen'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'precio' => $this->precio,
            'disponibles' => $this->disponibles,
            'subcategoria_id' => $this->subcategoriaId,
            'integridad_id' => $this->integridadId,
        ];
    }
}
?>
