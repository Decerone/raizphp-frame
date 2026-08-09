<?php
declare(strict_types=1);
namespace App\Nucleo;
abstract class Migracion { protected Conexion $conexion; protected \PDO $pdo; public function __construct() { $this->conexion = Aplicacion::obtenerInstancia()->obtenerConexion(); $this->pdo = $this->conexion->obtenerPDO(); } abstract public function subir(): void; abstract public function bajar(): void; }
