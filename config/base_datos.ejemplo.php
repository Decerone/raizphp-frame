<?php
declare(strict_types=1);

return [
    'motor' => 'mysql',
    'host' => 'localhost',
    'puerto' => 3306,
    'nombre' => 'raiz_db',
    'usuario' => 'root',
    'clave' => '',
    'juego_caracteres' => 'utf8mb4',
    'opciones' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]
];
