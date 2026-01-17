<?php
namespace App\DTOs\Producto;

final readonly class ListarProductosDTO
{
    public function __construct(
        public ?int $categoriaId,
        public ?int $subcategoriaId,
        public ?float $precioMin,
        public ?float $precioMax,
        public ?int $integridadId,
        public ?int $vendedorId,
        public string $orderBy,
        public string $orderDir,
        public int $perPage,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            categoriaId: isset($data['categoria_id']) ? (int) $data['categoria_id'] : null,
            subcategoriaId: isset($data['subcategoria_id']) ? (int) $data['subcategoria_id'] : null,
            precioMin: isset($data['precio_min']) ? (float) $data['precio_min'] : null,
            precioMax: isset($data['precio_max']) ? (float) $data['precio_max'] : null,
            integridadId: isset($data['integridad_id']) ? (int) $data['integridad_id'] : null,
            vendedorId: isset($data['vendedor_id']) ? (int) $data['vendedor_id'] : null,
            orderBy: $data['order_by'] ?? 'fecha_registro',
            orderDir: $data['order_dir'] ?? 'desc',
            perPage: isset($data['per_page']) ? (int) $data['per_page'] : 20,
        );
    }
    public function toArray(): array
    {
        return [
            'categoria_id' => $this->categoriaId,
            'subcategoria_id' => $this->subcategoriaId,
            'precio_min' => $this->precioMin,
            'precio_max' => $this->precioMax,
            'integridad_id' => $this->integridadId,
            'vendedor_id' => $this->vendedorId,
            'order_by' => $this->orderBy,
            'order_dir' => $this->orderDir,
            'per_page' => $this->perPage,
        ];
    }
}
