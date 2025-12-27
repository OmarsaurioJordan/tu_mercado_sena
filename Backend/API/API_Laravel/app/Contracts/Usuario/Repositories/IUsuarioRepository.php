<?php

namespace App\Contracts\Usuario\Repositories;

interface IUsuarioReposory
{
    public function findById(int $id);
    public function update(int $id,array $data);
}
