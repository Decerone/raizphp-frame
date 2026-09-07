<?php
declare(strict_types=1);
namespace App\Nucleo;
use App\Middleware\MiddlewareCors;
use App\Middleware\MiddlewareCsrf;
use App\Middleware\MiddlewareRegistro;
use App\Middleware\MiddlewareAutenticacion;
use App\Middleware\MiddlewareSeguridad;
use App\Middleware\MiddlewareRol;
use App\Middleware\MiddlewareHttps;
use App\Middleware\MiddlewareApiAuth;
class Aplicacion
{
    private static ?Aplicacion $instancia = null;
    private string $directorioRaiz;
    private Enrutador $enrutador;
    private array $configuracion;
    private ?Conexion $conexion = null;
    private Renderizador $renderizador;
    
    public function __construct(string $directorioRaiz)
    {
        $this->directorioRaiz = rtrim($directorioRaiz, '/');
        self::$instancia = $this;
        $this->cargarConfiguracion();
        
        $directorioVistas = $this->directorioRaiz . '/app/Vistas';
        $this->renderizador = new Renderizador($directorioVistas);
        
        $this->enrutador = new Enrutador();
        $this->enrutador->setRenderizador($this->renderizador);
        
        $this->cargarRutas();
        $this->registrarMiddlewares();
    }
    
    public static function obtenerInstancia(): self { return self::$instancia; }
    public function obtenerDirectorioRaiz(): string { return $this->directorioRaiz; }
    public function obtenerConfiguracion(string $clave, $porDefecto = null) { return $this->configuracion[$clave] ?? $porDefecto; }
    public function obtenerConexion(): Conexion
    {
        if ($this->conexion === null) $this->conexion = new Conexion($this->configuracion['base_datos']);
        return $this->conexion;
    }
    public function obtenerRenderizador(): Renderizador { return $this->renderizador; }
    
    private function cargarConfiguracion(): void
    {
        $rutaBase = $this->directorioRaiz . '/config/base_datos.php';
        $rutaApp  = $this->directorioRaiz . '/config/aplicacion.php';
        if (!file_exists($rutaBase) || !file_exists($rutaApp)) {
            throw new \RuntimeException("Archivos de configuración no encontrados. Copia config/*.ejemplo.php a config/*.php");
        }
        $configBD = require $rutaBase;
        if (!is_array($configBD)) throw new \RuntimeException("base_datos.php debe devolver un array.");
        $configApp = require $rutaApp;
        if (!is_array($configApp)) throw new \RuntimeException("aplicacion.php debe devolver un array.");
        $this->configuracion = ['base_datos' => $configBD, 'app' => $configApp];
    }
    
    private function cargarRutas(): void
    {
        $directorioRutas = $this->directorioRaiz . '/app/rutas';
        $cargador = new CargadorRutas($this->enrutador, $directorioRutas);
        $cargador->cargar();
    }
    
    private function registrarMiddlewares(): void
    {
        $this->enrutador->usarMiddleware(new MiddlewareRegistro());
        $this->enrutador->usarMiddleware(new MiddlewareHttps());
        $this->enrutador->usarMiddleware(new MiddlewareCors());
        $this->enrutador->usarMiddleware(new MiddlewareSeguridad());
        $this->enrutador->usarMiddleware(new MiddlewareCsrf());
        $this->enrutador->usarMiddleware(new MiddlewareAutenticacion());
    }
    
    public function ejecutar(): void
    {
        try {
            $this->enrutador->despachar($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
        } catch (\Throwable $e) {
            error_log('Error 500: ' . $e->getMessage());
            if (isset($this->renderizador)) {
                try {
                    http_response_code(500);
                    $this->renderizador->mostrar('errores/500', ['urlBase' => UrlHelper::base() ?: '/'], null, false);
                    return;
                } catch (\RuntimeException $ex) {}
            }
            http_response_code(500);
            echo '500 - Error interno del servidor';
        }
    }
}
