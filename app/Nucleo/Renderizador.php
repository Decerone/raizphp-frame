<?php
declare(strict_types=1);
namespace App\Nucleo;
class Renderizador
{
    private string $directorioVistas;
    public function __construct(string $directorioVistas) { $this->directorioVistas = $directorioVistas; }
    public function mostrar(string $vista, array $datos = [], ?string $plantilla = 'plantilla', bool $usarCache = true): void
    {
        extract($datos);
        $rutaVista = $this->directorioVistas . "/$vista.php";
        if (!file_exists($rutaVista)) throw new \RuntimeException("Vista $vista no encontrada.");
        
        // No cachear después de POST/PUT/DELETE
        $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array($metodo, ['POST', 'PUT', 'DELETE'])) {
            $usarCache = false;
        }
        // No cachear vistas con formularios
        if ($usarCache) {
            $contenidoArchivo = file_get_contents($rutaVista);
            if (strpos($contenidoArchivo, 'method="POST"') !== false || 
                strpos($contenidoArchivo, "_token") !== false ||
                strpos($contenidoArchivo, "method='POST'") !== false) {
                $usarCache = false;
            }
        }
        
        $cache = new Cache(); $claveCache = Cache::claveVista($vista, $datos); 
        if ($usarCache) {
            $contenidoCache = $cache->obtener($claveCache);
            if ($contenidoCache !== null) { echo $contenidoCache; return; }
        }
        ob_start(); require $rutaVista; $contenido = ob_get_clean();
        if ($plantilla) { $rutaPlantilla = $this->directorioVistas . "/$plantilla.php"; if (file_exists($rutaPlantilla)) { ob_start(); require $rutaPlantilla; $contenido = ob_get_clean(); } }
        if ($usarCache) $cache->guardar($claveCache, $contenido);
        echo $contenido;
    }
}
