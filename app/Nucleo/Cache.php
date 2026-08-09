<?php

declare(strict_types=1);

namespace App\Nucleo;

class Cache
{
    private string $directorioCache;

    public function __construct()
    {
        $this->directorioCache = dirname(__DIR__, 2) . '/almacenamiento/cache';
        
        // Crear directorio si no existe
        if (!is_dir($this->directorioCache)) {
            mkdir($this->directorioCache, 0775, true);
        }
    }

    /**
     * Obtiene un valor de la caché
     */
    public function obtener(string $clave): mixed
    {
        $archivo = $this->directorioCache . '/' . md5($clave) . '.cache';
        
        if (!file_exists($archivo)) {
            return null;
        }
        
        $datos = file_get_contents($archivo);
        if ($datos === false) {
            return null;
        }
        
        $cache = unserialize($datos);
        
        if ($cache === false) {
            return null;
        }
        
        // Verificar expiración
        if ($cache['expiracion'] < time()) {
            @unlink($archivo);
            return null;
        }
        
        return $cache['datos'];
    }

    /**
     * Guarda un valor en la caché
     */
    public function guardar(string $clave, mixed $datos, int $duracion = 3600): void
    {
        $archivo = $this->directorioCache . '/' . md5($clave) . '.cache';
        
        $cache = [
            'expiracion' => time() + $duracion,
            'datos'      => $datos
        ];
        
        file_put_contents($archivo, serialize($cache), LOCK_EX);
        @chmod($archivo, 0664);
    }

    /**
     * Elimina un valor específico de la caché
     */
    public function eliminar(string $clave): void
    {
        $archivo = $this->directorioCache . '/' . md5($clave) . '.cache';
        
        if (file_exists($archivo)) {
            @unlink($archivo);
        }
    }

    /**
     * Limpia toda la caché
     */
    public function limpiarTodo(): void
    {
        if (is_dir($this->directorioCache)) {
            $archivos = glob($this->directorioCache . '/*.cache');
            foreach ($archivos as $archivo) {
                @unlink($archivo);
            }
        }
    }

    /**
     * Limpia la caché de una tabla específica
     */
    public function limpiarTabla(string $tabla): void
    {
        if (is_dir($this->directorioCache)) {
            $archivos = glob($this->directorioCache . '/*.cache');
            foreach ($archivos as $archivo) {
                $nombre = basename($archivo);
                if (strpos($nombre, $tabla) !== false) {
                    @unlink($archivo);
                }
            }
        }
    }

    /**
     * Limpia la caché de vistas
     */
    public function limpiarVistas(): void
    {
        if (is_dir($this->directorioCache)) {
            $archivos = glob($this->directorioCache . '/*.cache');
            foreach ($archivos as $archivo) {
                $nombre = basename($archivo);
                if (strpos($nombre, 'vista_') !== false) {
                    @unlink($archivo);
                }
            }
        }
    }

    /**
     * Obtiene estadísticas de la caché
     */
    public function estadisticas(): array
    {
        if (!is_dir($this->directorioCache)) {
            return ['archivos' => 0, 'tamano' => 0, 'antiguedad' => 0];
        }
        
        $archivos = glob($this->directorioCache . '/*.cache');
        $total = count($archivos);
        $tamano = 0;
        $masAntiguo = time();
        
        foreach ($archivos as $archivo) {
            $tamano += filesize($archivo);
            $mtime = filemtime($archivo);
            if ($mtime < $masAntiguo) {
                $masAntiguo = $mtime;
            }
        }
        
        return [
            'archivos'   => $total,
            'tamano'     => $tamano,
            'tamano_kb'  => round($tamano / 1024, 2),
            'antiguedad' => $total > 0 ? time() - $masAntiguo : 0
        ];
    }

    /**
     * Genera clave de caché para una vista
     */
    public static function claveVista(string $vista, array $datos): string
    {
        return 'vista_' . md5($vista . serialize($datos));
    }

    /**
     * Genera clave de caché para una consulta
     */
    public static function claveConsulta(string $sql, array $parametros): string
    {
        return 'consulta_' . md5($sql . serialize($parametros));
    }
}
