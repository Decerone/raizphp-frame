<?php
declare(strict_types=1);
namespace App\Nucleo;

abstract class ModeloBase
{
    protected static string $tabla;
    protected static string $clavePrimaria = 'id';
    protected static array $rellenables = [];
    protected static array $ocultos = [];
    protected static array $casts = [];
    protected array $atributos = [];
    
    public function __construct(array $datos = []) { $this->llenar($datos); }
    
    public function llenar(array $datos): void
    {
        foreach ($datos as $k => $v) {
            if (empty(static::$rellenables) || in_array($k, static::$rellenables, true)) {
                $this->atributos[$k] = $this->aplicarCast($k, $v);
            }
        }
    }
    
    private function aplicarCast(string $clave, $valor)
    {
        if (!isset(static::$casts[$clave])) return $valor;
        if ($valor === null) return null;
        
        return match(static::$casts[$clave]) {
            'int' => (int) $valor,
            'float' => (float) $valor,
            'string' => (string) $valor,
            'bool' => (bool) $valor,
            'json' => is_string($valor) ? json_decode($valor, true) : $valor,
            default => $valor
        };
    }
    
    public function __get(string $nombre)
    {
        if (!isset($this->atributos[$nombre])) return null;
        return $this->aplicarCast($nombre, $this->atributos[$nombre]);
    }
    
    public function __set(string $nombre, $valor): void
    {
        $this->atributos[$nombre] = $valor;
    }
    
    public function __isset(string $nombre): bool
    {
        return isset($this->atributos[$nombre]);
    }
    
    public function guardar(): bool
    {
        $datos = $this->atributos;
        $pk = static::$clavePrimaria;
        
        // Preparar datos para BD (convertir tipos)
        foreach ($datos as $k => $v) {
            if (isset(static::$casts[$k])) {
                if (static::$casts[$k] === 'json' && is_array($v)) {
                    $datos[$k] = json_encode($v, JSON_UNESCAPED_UNICODE);
                }
            }
        }
        
        if (isset($datos[$pk]) && $datos[$pk]) {
            $id = $datos[$pk];
            unset($datos[$pk]);
            $resultado = static::consultar()->donde($pk, '=', $id)->actualizar($datos) > 0;
        } else {
            $id = static::consultar()->insertar($datos);
            if ($id) {
                $this->atributos[$pk] = $id;
                $resultado = true;
            } else {
                $resultado = false;
            }
        }
        
        if ($resultado) {
            static::invalidarCache();
        }
        
        return $resultado;
    }
    
    public function eliminar(): bool
    {
        $pk = static::$clavePrimaria;
        if (empty($this->atributos[$pk])) return false;
        
        $resultado = static::consultar()->donde($pk, '=', $this->atributos[$pk])->eliminar() > 0;
        
        if ($resultado) {
            static::invalidarCache();
        }
        
        return $resultado;
    }
    
    protected static function invalidarCache(): void
    {
        $cache = new Cache();
        $cache->limpiarTodo();
    }
    
    public static function consultar(): ConstructorConsulta { return new ConstructorConsulta(static::class, static::$tabla); }
    public static function encontrar($id): ?static { return static::consultar()->donde(static::$clavePrimaria, '=', $id)->primero(); }
    public static function todos(): array { return static::consultar()->obtener(); }
    
    /**
     * Convierte el modelo a array, respetando $ocultos.
     */
    public function aArray(): array
    {
        $datos = $this->atributos;
        foreach (static::$ocultos as $oculto) {
            unset($datos[$oculto]);
        }
        return $datos;
    }
    
    /**
     * Convierte a array incluyendo campos ocultos (uso interno).
     */
    public function aArrayCompleto(): array
    {
        return $this->atributos;
    }
}
