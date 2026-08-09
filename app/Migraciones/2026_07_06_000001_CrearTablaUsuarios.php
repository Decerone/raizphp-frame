<?php
declare(strict_types=1);
namespace App\Migraciones;
use App\Nucleo\Migracion;
class CrearTablaUsuarios extends Migracion
{
    public function subir(): void { $this->pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(50) NOT NULL, apellido VARCHAR(50) NOT NULL, email VARCHAR(100) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL DEFAULT '', api_token VARCHAR(64) NULL UNIQUE, rol VARCHAR(20) NOT NULL DEFAULT 'usuario', edad INT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }
    public function bajar(): void { $this->pdo->exec("DROP TABLE IF EXISTS usuarios"); }
}
