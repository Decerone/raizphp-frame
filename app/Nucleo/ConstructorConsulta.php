<?php
declare(strict_types=1);
namespace App\Nucleo;
use PDO;
class ConstructorConsulta
{
    private string $claseModelo;
    private string $tabla;
    private array $clausulas = ['seleccion'=>'*','condiciones'=>[],'orden'=>[],'limite'=>null,'desplazamiento'=>null,'uniones'=>[]];
    private array $parametros = [];
    public function __construct(string $claseModelo, string $tabla) { $this->claseModelo = $claseModelo; $this->tabla = $tabla; }
    public function seleccionar(string $columnas = '*'): self { $this->clausulas['seleccion'] = $columnas; return $this; }
    public function donde(string $columna, string $operador, $valor): self { $this->clausulas['condiciones'][] = [$columna,$operador,$valor,'AND']; return $this; }
    public function oDonde(string $columna, string $operador, $valor): self { $this->clausulas['condiciones'][] = [$columna,$operador,$valor,'OR']; return $this; }
    public function ordenarPor(string $columna, string $direccion = 'ASC'): self { $this->clausulas['orden'][] = [$columna,strtoupper($direccion)]; return $this; }
    public function limite(int $limite): self { $this->clausulas['limite'] = $limite; return $this; }
    public function desplazamiento(int $desplazamiento): self { $this->clausulas['desplazamiento'] = $desplazamiento; return $this; }
    public function unir(string $tabla, string $colLocal, string $operador, string $colForanea, string $tipo = 'INNER'): self { $this->clausulas['uniones'][] = [$tabla,$colLocal,$operador,$colForanea,$tipo]; return $this; }
    
    // Métodos agregados previamente
    public function contar(): int { $sql = "SELECT COUNT(*) as total FROM {$this->tabla}" . $this->construirUniones() . $this->construirWhere(); $pdo = Aplicacion::obtenerInstancia()->obtenerConexion()->obtenerPDO(); $stmt = $pdo->prepare($sql); $stmt->execute($this->parametros); $resultado = $stmt->fetch(); return (int) ($resultado['total'] ?? 0); }
    public function existe(): bool { return $this->contar() > 0; }
    
    public function obtener(): array
    {
        $sql = $this->construirSelect();
        $cache = new Cache();
        $claveCache = Cache::claveConsulta($sql, $this->parametros);
        $resultadoCache = $cache->obtener($claveCache);
        if ($resultadoCache !== null) return $resultadoCache;
        $pdo = Aplicacion::obtenerInstancia()->obtenerConexion()->obtenerPDO();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($this->parametros);
        $filas = $stmt->fetchAll();
        $modelos = [];
        foreach ($filas as $fila) $modelos[] = new $this->claseModelo($fila);
        $cache->guardar($claveCache, $modelos, 300);
        return $modelos;
    }
    public function primero(): ?object { $this->limite(1); $resultados = $this->obtener(); return $resultados[0] ?? null; }
    
    public function insertar(array $datos): int|string
    {
        $columnas = implode(', ', array_keys($datos));
        $marcadores = implode(', ', array_fill(0, count($datos), '?'));
        $sql = "INSERT INTO {$this->tabla} ($columnas) VALUES ($marcadores)";
        $pdo = Aplicacion::obtenerInstancia()->obtenerConexion()->obtenerPDO();
        $stmt = $pdo->prepare($sql); $stmt->execute(array_values($datos));
        //$this->limpiarCacheTabla();
        return $pdo->lastInsertId();
    }
    
    public function actualizar(array $datos): int
    {
        $asignaciones = implode(', ', array_map(fn($col) => "$col = ?", array_keys($datos)));
        $valores = array_values($datos);
        $this->parametros = array_merge($valores, $this->parametros);
        $sql = "UPDATE {$this->tabla} SET $asignaciones" . $this->construirWhere();
        $pdo = Aplicacion::obtenerInstancia()->obtenerConexion()->obtenerPDO();
        $stmt = $pdo->prepare($sql); $stmt->execute($this->parametros);
        //$this->limpiarCacheTabla();
        return $stmt->rowCount();
    }
    
    public function eliminar(): int
    {
        $sql = "DELETE FROM {$this->tabla}" . $this->construirWhere();
        $pdo = Aplicacion::obtenerInstancia()->obtenerConexion()->obtenerPDO();
        $stmt = $pdo->prepare($sql); $stmt->execute($this->parametros);
        //$this->limpiarCacheTabla();
        return $stmt->rowCount();
    }
    
    // NUEVO: Limpiar caché de esta tabla
    private function limpiarCacheTabla(): void
    {
        $dirCache = dirname(__DIR__, 2) . '/almacenamiento/cache';
        if (is_dir($dirCache)) {
            $archivos = glob($dirCache . '/*');
            $tabla = $this->tabla;
            foreach ($archivos as $archivo) {
                $nombre = basename($archivo);
                if (strpos($nombre, $tabla) !== false || strpos($nombre, 'vista_') !== false) {
                    @unlink($archivo);
                }
            }
        }
    }
    
    private function construirSelect(): string { return "SELECT {$this->clausulas['seleccion']} FROM {$this->tabla}" . $this->construirUniones() . $this->construirWhere() . $this->construirOrden() . $this->construirLimite(); }
    private function construirUniones(): string { $sql = ''; foreach ($this->clausulas['uniones'] as $u) $sql .= " {$u[4]} JOIN {$u[0]} ON {$u[1]} {$u[2]} {$u[3]}"; return $sql; }
    private function construirWhere(): string { if (empty($this->clausulas['condiciones'])) return ''; $partes = []; foreach ($this->clausulas['condiciones'] as $i => $c) { $this->parametros[] = $c[2]; $partes[] = ($i===0?'':"{$c[3]} ") . "{$c[0]} {$c[1]} ?"; } return ' WHERE ' . implode(' ', $partes); }
    private function construirOrden(): string { if (empty($this->clausulas['orden'])) return ''; $ordenes = array_map(fn($o) => "$o[0] $o[1]", $this->clausulas['orden']); return ' ORDER BY ' . implode(', ', $ordenes); }
    private function construirLimite(): string { if ($this->clausulas['limite']===null) return ''; $sql = ' LIMIT '.$this->clausulas['limite']; if ($this->clausulas['desplazamiento']!==null) $sql .= ' OFFSET '.$this->clausulas['desplazamiento']; return $sql; }
}
