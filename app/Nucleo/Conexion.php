<?php
declare(strict_types=1);
namespace App\Nucleo;
use PDO;
use PDOException;

class Conexion
{
    private PDO $pdo;
    private string $motor;
    
    public function __construct(array $config)
    {
        $this->motor = $config['motor'] ?? 'mysql';
        
        $dsn = match ($this->motor) {
            'mysql' => sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $config['host'],
                (int)$config['puerto'],
                $config['nombre'],
                $config['juego_caracteres'] ?? 'utf8mb4'
            ),
            'pgsql' => sprintf(
                'pgsql:host=%s;port=%d;dbname=%s',
                $config['host'],
                (int)$config['puerto'],
                $config['nombre']
            ),
            'sqlite' => sprintf('sqlite:%s', $config['nombre']),
            default => throw new \InvalidArgumentException("Motor no soportado: {$this->motor}")
        };
        
        try {
            $this->pdo = new PDO($dsn, $config['usuario'] ?? null, $config['clave'] ?? null, $config['opciones'] ?? []);
        } catch (PDOException $e) {
            error_log('Error de conexión BD (' . $this->motor . '): ' . $e->getMessage());
            throw new \RuntimeException('No se pudo conectar a la base de datos.');
        }
    }
    
    public function obtenerPDO(): PDO { return $this->pdo; }
    public function obtenerMotor(): string { return $this->motor; }
    
    /**
     * Devuelve la función SQL para obtener el último ID insertado.
     * MySQL: no aplica (se usa lastInsertId)
     * PostgreSQL: usar RETURNING id (gestionado por el ORM)
     */
    public function soportaReturning(): bool
    {
        return in_array($this->motor, ['pgsql', 'sqlite'], true);
    }
    
    /**
     * Escapa un identificador SQL según el motor.
     */
    public function citar(string $identificador): string
    {
        return match ($this->motor) {
            'mysql' => '`' . str_replace('`', '``', $identificador) . '`',
            'pgsql', 'sqlite' => '"' . str_replace('"', '""', $identificador) . '"',
            default => $identificador
        };
    }
    
    /**
     * Devuelve la sintaxis de autoincremento según el motor.
     */
    public function autoincremento(): string
    {
        return match ($this->motor) {
            'mysql' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'pgsql' => 'SERIAL PRIMARY KEY',
            'sqlite' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
            default => 'INT AUTO_INCREMENT PRIMARY KEY'
        };
    }
    
    /**
     * Devuelve el sufijo de creación de tabla (charset/engine).
     */
    public function sufijoTabla(): string
    {
        return match ($this->motor) {
            'mysql' => ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
            'pgsql' => '',
            'sqlite' => '',
            default => ''
        };
    }
}
