<?php
declare(strict_types=1);
namespace App\Nucleo;
abstract class Seeder { protected Conexion $conexion; protected \PDO $pdo; public function __construct() { $this->conexion = Aplicacion::obtenerInstancia()->obtenerConexion(); $this->pdo = $this->conexion->obtenerPDO(); } abstract public function ejecutar(): void; }
