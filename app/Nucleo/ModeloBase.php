<?php
declare(strict_types=1);
namespace App\Nucleo;
abstract class ModeloBase
{
    protected static string $tabla; protected static string $clavePrimaria = 'id'; protected array $atributos = [];
    public function __construct(array $datos = []) { $this->llenar($datos); }
    public function llenar(array $datos): void { foreach ($datos as $k=>$v) $this->atributos[$k] = $v; }
    public function __get(string $nombre) { return $this->atributos[$nombre] ?? null; }
    public function __set(string $nombre, $valor): void { $this->atributos[$nombre] = $valor; }
    public function guardar(): bool { $datos = $this->atributos; $pk = static::$clavePrimaria; if (isset($datos[$pk])&&$datos[$pk]) { $id = $datos[$pk]; unset($datos[$pk]); return static::consultar()->donde($pk,'=',$id)->actualizar($datos) > 0; } $id = static::consultar()->insertar($datos); if ($id) { $this->atributos[$pk] = $id; return true; } return false; }
    public function eliminar(): bool { $pk = static::$clavePrimaria; if (empty($this->atributos[$pk])) return false; return static::consultar()->donde($pk,'=',$this->atributos[$pk])->eliminar() > 0; }
    public static function consultar(): ConstructorConsulta { return new ConstructorConsulta(static::class, static::$tabla); }
    public static function encontrar($id): ?static { return static::consultar()->donde(static::$clavePrimaria,'=',$id)->primero(); }
    public static function todos(): array { return static::consultar()->obtener(); }
    public function aArray(): array { return $this->atributos; }
}
