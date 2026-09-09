<?php
declare(strict_types=1);
namespace App\Nucleo;

class Cache
{
    private string $directorioCache;

    public function __construct()
    {
        $this->directorioCache = dirname(__DIR__, 2) . '/almacenamiento/cache';
        if (!is_dir($this->directorioCache)) {
            mkdir($this->directorioCache, 0775, true);
        }
    }

    public function obtener(string $clave): mixed
    {
        $archivo = $this->directorioCache . '/' . md5($clave) . '.cache';
        if (!file_exists($archivo)) return null;
        
        $datos = file_get_contents($archivo);
        if ($datos === false) return null;
        
        // SEGURO: JSON en lugar de unserialize
        $cache = json_decode($datos, true);
        if (!is_array($cache)) return null;
        
        if (($cache['expiracion'] ?? 0) < time()) {
            @unlink($archivo);
            return null;
        }
        
        return $cache['datos'] ?? null;
    }

    public function guardar(string $clave, mixed $datos, int $duracion = 3600): void
    {
        // Convertir objetos a arrays para JSON
        if (is_object($datos) && method_exists($datos, 'aArray')) {
            $datos = $datos->aArray();
        }
        
        $archivo = $this->directorioCache . '/' . md5($clave) . '.cache';
        $cache = [
            'expiracion' => time() + $duracion,
            'datos' => $datos
        ];
        
        // SEGURO: JSON en lugar de serialize
        file_put_contents($archivo, json_encode($cache, JSON_UNESCAPED_UNICODE), LOCK_EX);
        @chmod($archivo, 0664);
    }

    public function eliminar(string $clave): void
    {
        $archivo = $this->directorioCache . '/' . md5($clave) . '.cache';
        if (file_exists($archivo)) @unlink($archivo);
    }

    public function limpiarTodo(): void
    {
        if (is_dir($this->directorioCache)) {
            $archivos = glob($this->directorioCache . '/*.cache');
            foreach ($archivos as $archivo) @unlink($archivo);
        }
    }

    public function limpiarTabla(string $tabla): void
    {
        // NOTA: Los archivos se llaman md5(clave), no contienen el nombre de tabla.
        // Por eso limpiarTabla() no puede filtrar. Se limpia todo.
        $this->limpiarTodo();
    }

    public function limpiarVistas(): void
    {
        // NOTA: Igual que limpiarTabla. Los nombres son md5.
        $this->limpiarTodo();
    }

    public function estadisticas(): array
    {
        if (!is_dir($this->directorioCache)) {
            return ['archivos' => 0, 'tamano' => 0, 'tamano_kb' => 0, 'antiguedad' => 0];
        }
        
        $archivos = glob($this->directorioCache . '/*.cache');
        $total = count($archivos);
        $tamano = 0;
        $masAntiguo = time();
        
        foreach ($archivos as $archivo) {
            $tamano += filesize($archivo);
            $mtime = filemtime($archivo);
            if ($mtime < $masAntiguo) $masAntiguo = $mtime;
        }
        
        return [
            'archivos' => $total,
            'tamano' => $tamano,
            'tamano_kb' => round($tamano / 1024, 2),
            'antiguedad' => $total > 0 ? time() - $masAntiguo : 0
        ];
    }

    public static function claveVista(string $vista, array $datos): string
    {
        return 'vista_' . md5($vista . json_encode($datos));
    }

    public static function claveConsulta(string $sql, array $parametros): string
    {
        return 'consulta_' . md5($sql . json_encode($parametros));
    }
}
