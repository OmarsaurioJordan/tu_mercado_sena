<?php

namespace App\Contracts\Usuario\Repositories;

interface IUsuarioRepository
{
    public function findById(int $id);
    public function update(int $id,array $data);


}
