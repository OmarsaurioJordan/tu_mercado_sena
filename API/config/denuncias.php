<?php

return [
    // número de denuncias abiertas que disparan el estado "denunciado".
    // se puede ajustar desde el .env (valor por defecto 3).
    'limite_para_denunciado' => env('DENUNCIAS_LIMITE_PARA_DENUNCIADO', 2),
];
