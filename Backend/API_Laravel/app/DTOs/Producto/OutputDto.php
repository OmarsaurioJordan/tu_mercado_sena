<?php

namespace App\DTOs\Producto;

use App\Models\Producto;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection as ModelCollection;

readonly class OutputDto implements Arrayable
{
    public function __construct(
        public int $id,
        public string $nombre,
        public int $subcategoria_id,
        public int $integridad_id,
        public int $vendedor_id,
        public int $estado_id,
        public string $descripcion,
        public float $precio,
        public int $disponibles,
        public string $fecha_registro,
        public string $fecha_actualiza,
        public ?array $vendedor = null,
        public ?array $subcategoria = null,
        public ?array $integridad = null,
        public ?array $estado = null,
        public ?array $fotos = null,
    ) {}

    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'subcategoria_id' => $this->subcategoria_id,
            'integridad_id' => $this->integridad_id,
            'vendedor_id' => $this->vendedor_id,
            'estado_id' => $this->estado_id,
            'descripcion' => $this->descripcion,
            'precio' => $this->precio,
            'disponibles' => $this->disponibles,
            'fecha_registro' => $this->fecha_registro,
            'fecha_actualiza' => $this->fecha_actualiza,
        ];

        if ($this->vendedor !== null) {
            $data['vendedor'] = $this->vendedor;
        }

        if ($this->subcategoria !== null) {
            $data['subcategoria'] = $this->subcategoria;
        }

        if ($this->integridad !== null) {
            $data['integridad'] = $this->integridad;
        }

        if ($this->estado !== null) {
            $data['estado'] = $this->estado;
        }

        if ($this->fotos !== null) {
            $data['fotos'] = $this->fotos;
        }

        return $data;
    }

    /**
     * Crea una instancia desde un modelo Producto
     */
    public static function fromModel(Producto $producto): self
    {
        return new self(
            id: $producto->id,
            nombre: $producto->nombre,
            subcategoria_id: $producto->subcategoria_id,
            integridad_id: $producto->integridad_id,
            vendedor_id: $producto->vendedor_id,
            estado_id: $producto->estado_id,
            descripcion: $producto->descripcion,
            precio: $producto->precio,
            disponibles: $producto->disponibles,
            fecha_registro: $producto->fecha_registro->format('Y-m-d H:i:s'),
            fecha_actualiza: $producto->fecha_actualiza->format('Y-m-d H:i:s'),
            vendedor: $producto->relationLoaded('vendedor') && $producto->vendedor ? [
                'id' => $producto->vendedor->id,
                'nickname' => $producto->vendedor->nickname,
                'imagen' => $producto->vendedor->imagen,
            ] : null,
            subcategoria: $producto->relationLoaded('subcategoria') && $producto->subcategoria ? [
                'id' => $producto->subcategoria->id,
                'nombre' => $producto->subcategoria->nombre,
                'categoria_id' => $producto->subcategoria->categoria_id,
                'categoria' => $producto->subcategoria->relationLoaded('categoria') && $producto->subcategoria->categoria ? [
                    'id' => $producto->subcategoria->categoria->id,
                    'nombre' => $producto->subcategoria->categoria->nombre,
                ] : null,
            ] : null,
            integridad: $producto->relationLoaded('integridad') && $producto->integridad ? [
                'id' => $producto->integridad->id,
                'nombre' => $producto->integridad->nombre,
                'descripcion' => $producto->integridad->descripcion,
            ] : null,
            estado: $producto->relationLoaded('estado') && $producto->estado ? [
                'id' => $producto->estado->id,
                'nombre' => $producto->estado->nombre,
                'descripcion' => $producto->estado->descripcion,
            ] : null,
            fotos: $producto->relationLoaded('fotos') ? 
                $producto->fotos->map(fn($foto) => [
                    'id' => $foto->id,
                    'url' => asset("storage/productos/{$foto->imagen}"),
                    'actualiza' => $foto->actualiza->format('Y-m-d H:i:s'),
                ])->toArray() 
            : null,
        );
    }

    /**
     * Crea un array de DTOs desde una colección de modelos
     */
    public static function fromModelCollection(ModelCollection $productos): array
    {
        return $productos->map(fn(Producto $producto) => 
            self::fromModel($producto)->toArray()
        )->all();
    }
}