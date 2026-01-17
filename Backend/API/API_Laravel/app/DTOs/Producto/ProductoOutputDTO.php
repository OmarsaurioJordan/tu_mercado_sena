<?php
namespace App\DTOs\Producto;

use App\Models\Producto;

final readonly class ProductoOutputDTO
{
    public function __construct(
        public int $id,
        public string $nombre,
        public string $descripcion,
        public float $precio,
        public int $disponibles,
        public bool $conImagen,
        public ?string $urlImagen,
        public array $subcategoria,
        public array $categoria,
        public array $integridad,
        public array $vendedor,
        public array $estado,
        public string $fechaRegistro,
        public string $fechaActualiza,
    ) {}

    public static function fromModel(Producto $producto): self
    {
        return new self(
            id: $producto->id,
            nombre: $producto->nombre,
            descripcion: $producto->descripcion,
            precio: (float) $producto->precio,
            disponibles: (int) $producto->disponibles,
            conImagen: (bool) $producto->con_imagen,
            urlImagen: $producto->con_imagen
                ? asset("storage/productos/img_{$producto->id}.jpg")
                : null,

            subcategoria: [
                'id' => $producto->subcategoria->id ?? null,
                'nombre' => $producto->subcategoria->nombre ?? null,
            ],

            categoria: [
                'id' => $producto->subcategoria->categoria->id ?? null,
                'nombre' => $producto->subcategoria->categoria->nombre ?? null,
            ],

            integridad: [
                'id' => $producto->integridad->id ?? null,
                'nombre' => $producto->integridad->nombre ?? null,
            ],

            vendedor: [
                'id' => $producto->vendedor->id ?? null,
                'nombre' => $producto->vendedor->nombre ?? null,
            ],

            estado: [
                'id' => $producto->estado->id ?? null,
                'nombre' => $producto->estado->nombre ?? null,
            ],

            fechaRegistro: $producto->fecha_registro,
            fechaActualiza: $producto->fecha_actualiza,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'precio' => $this->precio,
            'disponibles' => $this->disponibles,
            'con_imagen' => $this->conImagen,
            'url_imagen' => $this->urlImagen,
            'subcategoria' => $this->subcategoria,
            'categoria' => $this->categoria,
            'integridad' => $this->integridad,
            'vendedor' => $this->vendedor,
            'estado' => $this->estado,
            'fecha_registro' => $this->fechaRegistro,
            'fecha_actualiza' => $this->fechaActualiza,
        ];
    }
}
?>
