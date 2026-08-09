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
    public function __construct(string $directorioRaiz)
    {
        $this->directorioRaiz = rtrim($directorioRaiz, '/');
        self::$instancia = $this;
        $this->cargarConfiguracion();
        $this->enrutador = new Enrutador();
        $this->registrarRutas();
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
    private function cargarConfiguracion(): void
    {
        $rutaBase = $this->directorioRaiz . '/config/base_datos.php';
        $rutaApp  = $this->directorioRaiz . '/config/aplicacion.php';
        if (!file_exists($rutaBase) || !file_exists($rutaApp)) throw new \RuntimeException("Archivos de configuración no encontrados.");
        $configBD = require $rutaBase;
        if (!is_array($configBD)) throw new \RuntimeException("base_datos.php debe devolver un array.");
        $configApp = require $rutaApp;
        if (!is_array($configApp)) throw new \RuntimeException("aplicacion.php debe devolver un array.");
        $this->configuracion = ['base_datos' => $configBD, 'app' => $configApp];
    }
    private function registrarRutas(): void
    {
        $this->enrutador->agregarRuta('GET','/','InicioControlador@index');
        $this->enrutador->agregarRuta('GET','/login','AuthControlador@formularioLogin');
        $this->enrutador->agregarRuta('POST','/login','AuthControlador@iniciarSesion');
        $this->enrutador->agregarRuta('GET','/registro','AuthControlador@formularioRegistro');
        $this->enrutador->agregarRuta('POST','/registro','AuthControlador@registrar');
        $this->enrutador->agregarRuta('GET','/logout','AuthControlador@cerrarSesion');
        $this->enrutador->agregarRuta('GET','/admin','AdminControlador@index');
        $this->enrutador->agregarRuta('GET','/admin/cache','AdminControlador@cache');
        $this->enrutador->agregarRuta('GET','/admin/cache/limpiar','AdminControlador@limpiarCache');
        $this->enrutador->agregarRuta('GET','/admin/usuarios','Admin\UsuarioAdminControlador@lista');
        $this->enrutador->agregarRuta('GET','/admin/usuarios/editar','Admin\UsuarioAdminControlador@editar');
        $this->enrutador->agregarRuta('POST','/admin/usuarios/actualizar','Admin\UsuarioAdminControlador@actualizar');
        $this->enrutador->agregarRuta('GET','/admin/usuarios/eliminar','Admin\UsuarioAdminControlador@eliminar');
        $this->enrutador->agregarRuta('GET','/recuperar','RecuperacionControlador@solicitar');
        $this->enrutador->agregarRuta('POST','/recuperar','RecuperacionControlador@enviarEnlace');
        $this->enrutador->agregarRuta('GET','/restablecer','RecuperacionControlador@restablecer');
        $this->enrutador->agregarRuta('POST','/restablecer','RecuperacionControlador@cambiarPassword');
        $this->enrutador->agregarRuta('POST','/api/login','AuthControlador@apiLogin');
        $this->enrutador->agregarRuta('GET','/api/usuarios','Api\UsuarioApiControlador@lista');
        $this->enrutador->agregarRuta('GET','/api/usuarios/mostrar','Api\UsuarioApiControlador@mostrar');
        $this->enrutador->agregarRuta('POST','/api/usuarios/crear','Api\UsuarioApiControlador@crear');
        $this->enrutador->agregarMiddlewareRuta('GET','/admin',new MiddlewareRol('admin'));
        $this->enrutador->agregarMiddlewareRuta('GET','/admin/cache',new MiddlewareRol('admin'));
        $this->enrutador->agregarMiddlewareRuta('GET','/admin/cache/limpiar',new MiddlewareRol('admin'));
        $this->enrutador->agregarMiddlewareRuta('GET','/admin/usuarios',new MiddlewareRol('admin'));
        $this->enrutador->agregarMiddlewareRuta('GET','/admin/usuarios/editar',new MiddlewareRol('admin'));
        $this->enrutador->agregarMiddlewareRuta('POST','/admin/usuarios/actualizar',new MiddlewareRol('admin'));
        $this->enrutador->agregarMiddlewareRuta('GET','/admin/usuarios/eliminar',new MiddlewareRol('admin'));
        $this->enrutador->agregarMiddlewareRuta('GET','/api/usuarios',new MiddlewareApiAuth());
        $this->enrutador->agregarMiddlewareRuta('GET','/api/usuarios/mostrar',new MiddlewareApiAuth());
        $this->enrutador->agregarMiddlewareRuta('POST','/api/usuarios/crear',new MiddlewareApiAuth());
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
    public function ejecutar(): void { $this->enrutador->despachar($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']); }
}
