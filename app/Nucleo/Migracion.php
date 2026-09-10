<?php
declare(strict_types=1);
namespace App\Nucleo;

abstract class Migracion
{
    protected Conexion $conexion;
    protected \PDO $pdo;
    protected string $motor;
    
    public function __construct()
    {
        $this->conexion = Aplicacion::obtenerInstancia()->obtenerConexion();
        $this->pdo = $this->conexion->obtenerPDO();
        $this->motor = $this->conexion->obtenerMotor();
    }
    
    abstract public function subir(): void;
    abstract public function bajar(): void;
    
    /**
     * Devuelve la sintaxis de autoincremento según el motor.
     */
    protected function id(): string
    {
        return $this->conexion->autoincremento();
    }
    
    /**
     * Sufijo de tabla según el motor (ENGINE/CHARSET).
     */
    protected function sufijoTabla(): string
    {
        return $this->conexion->sufijoTabla();
    }
    
    /**
     * Tipo de texto según motor.
     */
    protected function tipoTexto(int $longitud = 255): string
    {
        return "VARCHAR($longitud)";
    }
    
    /**
     * Tipo de entero.
     */
    protected function tipoEntero(): string
    {
        return 'INT';
    }
    
    /**
     * Tipo de texto largo.
     */
    protected function tipoTextoLargo(): string
    {
        return 'TEXT';
    }
    
    /**
     * Tipo de fecha/hora.
     */
    protected function tipoFechaHora(): string
    {
        return match ($this->motor) {
            'mysql' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
            'pgsql' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'sqlite' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
            default => 'DATETIME'
        };
    }
}
