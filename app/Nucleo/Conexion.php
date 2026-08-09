<?php
declare(strict_types=1);
namespace App\Nucleo;
use PDO; use PDOException;
class Conexion
{
    private PDO $pdo;
    public function __construct(array $config)
    {
        $motor = $config['motor'];
        $dsn = match ($motor) {
            'mysql' => sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $config['host'], $config['puerto'], $config['nombre'], $config['juego_caracteres']),
            'pgsql' => sprintf('pgsql:host=%s;port=%d;dbname=%s', $config['host'], $config['puerto'], $config['nombre']),
            default => throw new \InvalidArgumentException("Motor no soportado: $motor")
        };
        try { $this->pdo = new PDO($dsn, $config['usuario'], $config['clave'], $config['opciones'] ?? []); }
        catch (PDOException $e) { throw new PDOException("Error de conexión: " . $e->getMessage()); }
    }
    public function obtenerPDO(): PDO { return $this->pdo; }
    public function obtenerMotor(): string { return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME); }
}
