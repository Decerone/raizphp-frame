<?php

declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

$paso = $_GET['paso'] ?? '1';
$errores = [];
$exito = '';
$insertarDatos = false;
$dirRaiz = dirname(__DIR__);
$dirProyecto = dirname($dirRaiz);
$nombreProyecto = basename($dirProyecto);
$dirConfig = $dirRaiz . '/config';
$dirAlmacen = $dirRaiz . '/almacenamiento';
$dirRutas = $dirRaiz . '/app/rutas';
$dirErrores = $dirRaiz . '/app/Vistas/errores';
$dirApp = $dirRaiz . '/app';
$dirNucleo = $dirApp . '/Nucleo';
$dirMiddleware = $dirApp . '/Middleware';
$dirControladores = $dirApp . '/Controladores';
$dirModelos = $dirApp . '/Modelos';
$dirApi = $dirControladores . '/Api';
$dirAdmin = $dirControladores . '/Admin';
$dirPublic = $dirRaiz . '/public';

if (
    file_exists($dirConfig . '/base_datos.php') &&
    file_exists($dirConfig . '/aplicacion.php') &&
    ($_GET['reinstalar'] ?? '') !== '1'
) {
    echo "<!DOCTYPE html><html lang=\"es\"><head><meta charset=\"UTF-8\"><title>Ya instalado - RaízPHP</title>
    <style>:root{--color-primario:#2563eb;--redondeado:0.5rem}*{box-sizing:border-box;margin:0;padding:0}
    body{font-family:system-ui,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
    .contenedor{background:white;border-radius:1rem;padding:2.5rem;max-width:500px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,0.3);text-align:center}
    h1{color:var(--color-primario);margin-bottom:1rem}.icono{font-size:4rem;margin-bottom:1rem}
    p{color:#475569;margin-bottom:1.5rem}
    .boton{display:inline-block;padding:.8rem 1.5rem;background:var(--color-primario);color:white;border-radius:var(--redondeado);text-decoration:none;font-weight:600;margin:.3rem}
    .advertencia{background:#fef3c7;border:1px solid #fbbf24;color:#92400e;padding:.8rem;border-radius:var(--redondeado);margin:1rem 0;font-size:.85rem}
    .novedad{background:#ede9fe;border:1px solid #c4b5fd;color:#5b21b6;padding:.8rem;border-radius:var(--redondeado);margin:1rem 0;font-size:.85rem}
    </style></head><body><div class=\"contenedor\"><div class=\"icono\">🔒</div><h1>RaízPHP ya está instalado</h1>
    <p>Proyecto: <strong>$nombreProyecto</strong></p>
    <div class=\"advertencia\">Para reinstalar añade <code>?reinstalar=1</code></div>
    <div class=\"novedad\">🆕 <strong>v2.1:</strong> Rutas por módulos + URLs dinámicas + CLI 30 comandos</div>
    <a href=\"/$nombreProyecto/\" class=\"boton\">Ir a la aplicación →</a></div></body></html>";
    exit;
}

function verificarRequisitos(): array
{
    return [
        ['nombre' => 'PHP 8.0+', 'estado' => version_compare(PHP_VERSION, '8.0.0', '>='), 'actual' => PHP_VERSION],
        ['nombre' => 'PDO', 'estado' => extension_loaded('pdo'), 'actual' => extension_loaded('pdo') ? 'Instalado' : 'No'],
        ['nombre' => 'PDO MySQL', 'estado' => extension_loaded('pdo_mysql'), 'actual' => extension_loaded('pdo_mysql') ? 'Instalado' : 'No'],
        ['nombre' => 'OpenSSL', 'estado' => extension_loaded('openssl'), 'actual' => extension_loaded('openssl') ? 'Instalado' : 'No'],
        ['nombre' => 'mbstring', 'estado' => extension_loaded('mbstring'), 'actual' => extension_loaded('mbstring') ? 'Instalado' : 'No'],
        ['nombre' => 'JSON', 'estado' => extension_loaded('json'), 'actual' => extension_loaded('json') ? 'Instalado' : 'No'],
        ['nombre' => 'Permisos escritura', 'estado' => is_writable(__DIR__ . '/../almacenamiento'), 'actual' => is_writable(__DIR__ . '/../almacenamiento') ? 'Correcto' : 'Sin permisos'],
    ];
}
// ==================== NUEVO: Listar bases de datos existentes ====================
function listarBasesDatos(string $usuario, string $clave): array
{
    try {
        $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", $usuario, $clave, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);
        $stmt = $pdo->query("SHOW DATABASES");
        $bases = [];
        $sistema = ['information_schema', 'mysql', 'performance_schema', 'sys', 'phpmyadmin'];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $nombreBD = $row['Database'];
            if (!in_array(strtolower($nombreBD), $sistema)) {
                $bases[] = $nombreBD;
            }
        }
        sort($bases);
        return $bases;
    } catch (PDOException $e) {
        return [];
    }
}





if ($_SERVER['REQUEST_METHOD'] === 'POST' && $paso === 'instalar') {
    $db_nombre = $_POST['db_nombre'] ?? '';
    $db_usuario = $_POST['db_usuario'] ?? 'root';
    $db_clave = $_POST['db_clave'] ?? '';
    $app_nombre = $_POST['app_nombre'] ?? 'Mi Aplicación';
    $app_entorno = $_POST['app_entorno'] ?? 'desarrollo';
    $insertarDatos = isset($_POST['datos_prueba']) && $_POST['datos_prueba'] === '1';
    // NUEVO: Acción con la BD
    $accionBD = $_POST['accion_bd'] ?? 'crear';

    try {
        $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", $db_usuario, $db_clave, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        // ==================== NUEVO: Verificar si la BD ya existe ====================
        $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
        $stmt->execute([$db_nombre]);
        $bdExiste = (bool) $stmt->fetch();
        
        if ($bdExiste && $accionBD === 'crear') {
            throw new Exception(
                "⚠️ La base de datos <strong>\"{$db_nombre}\"</strong> ya existe.<br><br>" .
                "Opciones:<br>" .
                "• Regresa y selecciona <strong>\"Usar base de datos existente\"</strong><br>" .
                "• O elige un <strong>nombre diferente</strong> para crear una nueva."
            );
        }
        
        // Crear BD solo si se eligió "crear"
        if ($accionBD === 'crear') {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_nombre` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
        
        $pdo->exec("USE `$db_nombre`");

        $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(50) NOT NULL, apellido VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL DEFAULT '',
            api_token VARCHAR(64) NULL UNIQUE, rol VARCHAR(20) NOT NULL DEFAULT 'usuario', edad INT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS intentos_login (
            id INT AUTO_INCREMENT PRIMARY KEY, ip VARCHAR(45) NOT NULL,
            intentos INT NOT NULL DEFAULT 1, ultimo_intento INT NOT NULL, INDEX idx_ip (ip)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS recuperaciones (
            id INT AUTO_INCREMENT PRIMARY KEY, usuario_id INT NOT NULL, token VARCHAR(64) NOT NULL UNIQUE,
            expiracion INT NOT NULL, usado TINYINT(1) NOT NULL DEFAULT 0,
            creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS migraciones (
            id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(255) NOT NULL UNIQUE,
            ejecutada_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        if ($insertarDatos) {
            $pw = password_hash('password', PASSWORD_BCRYPT);
            $pdo->exec("INSERT INTO usuarios (nombre, apellido, email, password, rol, edad) VALUES 
                ('María','Pérez','maria@example.com','$pw','usuario',28),
                ('Carlos','Gómez','carlos@example.com','$pw','usuario',35),
                ('Ana','Martínez','ana@example.com','$pw','admin',22),
                ('Admin','Sistema','admin@raizphp.local','$pw','admin',30)
                ON DUPLICATE KEY UPDATE email=email");
        }

        $directorios = [$dirConfig, $dirAlmacen.'/logs', $dirAlmacen.'/correos', $dirAlmacen.'/cache',
            $dirRutas, $dirErrores, $dirNucleo, $dirMiddleware, $dirControladores, $dirModelos, $dirApi, $dirAdmin, $dirPublic];
        foreach ($directorios as $dir) { if (!is_dir($dir)) mkdir($dir, 0775, true); }

        // ==================== UrlHelper ====================
        $urlHelper = '<?php
declare(strict_types=1);
namespace App\Nucleo;
class UrlHelper {
    private static ?string $urlBase = null;
    private static ?string $urlBaseCompleta = null;
    public static function base(): string {
        if (self::$urlBase === null) {
            $scriptDir = dirname($_SERVER["SCRIPT_NAME"]);
            $scriptDir = preg_replace("#/raizphp/public$#", "", $scriptDir);
            self::$urlBase = ($scriptDir === "/" || $scriptDir === "\\\\") ? "" : $scriptDir;
        }
        return self::$urlBase;
    }
    public static function baseCompleta(): string {
        if (self::$urlBaseCompleta === null) {
            $protocolo = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
            self::$urlBaseCompleta = $protocolo . "://" . ($_SERVER["HTTP_HOST"] ?? "localhost") . self::base();
        }
        return self::$urlBaseCompleta;
    }
    public static function asset(string $ruta = ""): string { return dirname($_SERVER["SCRIPT_NAME"]) . "/" . ltrim($ruta, "/"); }
    public static function url(string $ruta = ""): string { return self::base() . "/" . ltrim($ruta, "/"); }
    public static function urlCompleta(string $ruta = ""): string { return self::baseCompleta() . "/" . ltrim($ruta, "/"); }
    public static function redirigir(string $ruta = ""): void { header("Location: " . self::url($ruta)); exit; }
}';
        file_put_contents($dirNucleo.'/UrlHelper.php', $urlHelper);
        @chmod($dirNucleo.'/UrlHelper.php', 0664);

        // ==================== Helpers ====================
        $helpers = '<?php
declare(strict_types=1);
use App\Nucleo\UrlHelper;
if (!function_exists("url")) { function url(string $ruta = ""): string { return UrlHelper::url($ruta); } }
if (!function_exists("asset")) { function asset(string $ruta): string { return UrlHelper::asset($ruta); } }
if (!function_exists("url_completa")) { function url_completa(string $ruta = ""): string { return UrlHelper::urlCompleta($ruta); } }
if (!function_exists("redirigir")) { function redirigir(string $ruta = ""): void { UrlHelper::redirigir($ruta); } }
';
        file_put_contents($dirNucleo.'/helpers.php', $helpers);
        @chmod($dirNucleo.'/helpers.php', 0664);

        // ==================== Rutas web ====================
        $web = '<?php
declare(strict_types=1);
use App\Nucleo\Enrutador;
return function (Enrutador $enrutador): void {
    $enrutador->agregarRuta("GET","/","InicioControlador@index");
    $enrutador->agregarRuta("GET","/login","AuthControlador@formularioLogin");
    $enrutador->agregarRuta("POST","/login","AuthControlador@iniciarSesion");
    $enrutador->agregarRuta("GET","/registro","AuthControlador@formularioRegistro");
    $enrutador->agregarRuta("POST","/registro","AuthControlador@registrar");
    $enrutador->agregarRuta("GET","/logout","AuthControlador@cerrarSesion");
    $enrutador->agregarRuta("GET","/recuperar","RecuperacionControlador@solicitar");
    $enrutador->agregarRuta("POST","/recuperar","RecuperacionControlador@enviarEnlace");
    $enrutador->agregarRuta("GET","/restablecer","RecuperacionControlador@restablecer");
    $enrutador->agregarRuta("POST","/restablecer","RecuperacionControlador@cambiarPassword");
};';
        file_put_contents($dirRutas.'/web.php', $web);
        @chmod($dirRutas.'/web.php', 0664);

        // ==================== Rutas admin ====================
        $admin = '<?php
declare(strict_types=1);
use App\Nucleo\Enrutador;
use App\Middleware\MiddlewareRol;
return function (Enrutador $enrutador): void {
    $enrutador->agregarRuta("GET","/admin","AdminControlador@index");
    $enrutador->agregarRuta("GET","/admin/cache","AdminControlador@cache");
    $enrutador->agregarRuta("GET","/admin/cache/limpiar","AdminControlador@limpiarCache");
    $enrutador->agregarRuta("GET","/admin/usuarios","Admin\\UsuarioAdminControlador@lista");
    $enrutador->agregarRuta("GET","/admin/usuarios/editar","Admin\\UsuarioAdminControlador@editar");
    $enrutador->agregarRuta("POST","/admin/usuarios/actualizar","Admin\\UsuarioAdminControlador@actualizar");
    $enrutador->agregarRuta("GET","/admin/usuarios/eliminar","Admin\\UsuarioAdminControlador@eliminar");
    $rutas=[["GET","/admin"],["GET","/admin/cache"],["GET","/admin/cache/limpiar"],["GET","/admin/usuarios"],["GET","/admin/usuarios/editar"],["POST","/admin/usuarios/actualizar"],["GET","/admin/usuarios/eliminar"]];
    foreach($rutas as [$m,$r]) $enrutador->agregarMiddlewareRuta($m,$r,new MiddlewareRol("admin"));
};';
        file_put_contents($dirRutas.'/admin.php', $admin);
        @chmod($dirRutas.'/admin.php', 0664);

        // ==================== Rutas api ====================
        $api = '<?php
declare(strict_types=1);
use App\Nucleo\Enrutador;
use App\Middleware\MiddlewareApiAuth;
return function (Enrutador $enrutador): void {
    $enrutador->agregarRuta("POST","/api/login","AuthControlador@apiLogin");
    $enrutador->agregarRuta("GET","/api/usuarios","Api\\UsuarioApiControlador@lista");
    $enrutador->agregarRuta("GET","/api/usuarios/mostrar","Api\\UsuarioApiControlador@mostrar");
    $enrutador->agregarRuta("POST","/api/usuarios/crear","Api\\UsuarioApiControlador@crear");
    $rutas=[["GET","/api/usuarios"],["GET","/api/usuarios/mostrar"],["POST","/api/usuarios/crear"]];
    foreach($rutas as [$m,$r]) $enrutador->agregarMiddlewareRuta($m,$r,new MiddlewareApiAuth());
};';
        file_put_contents($dirRutas.'/api.php', $api);
        @chmod($dirRutas.'/api.php', 0664);

        // ==================== CargadorRutas ====================
        $cargadorRutas = '<?php
declare(strict_types=1);
namespace App\Nucleo;
class CargadorRutas {
    private string $directorioRutas;
    private Enrutador $enrutador;
    public function __construct(Enrutador $enrutador, string $directorioRutas) { $this->enrutador=$enrutador; $this->directorioRutas=$directorioRutas; }
    public function cargar(?string $archivo=null): void {
        if($archivo!==null){ $this->cargarArchivo($this->directorioRutas."/".$archivo.".php"); }
        else{ foreach(glob($this->directorioRutas."/*.php") as $f) $this->cargarArchivo($f); }
    }
    private function cargarArchivo(string $f): void {
        if(!file_exists($f)) throw new \RuntimeException("Archivo de rutas no encontrado: $f");
        $cb=require $f; if(!is_callable($cb)) throw new \RuntimeException("El archivo de rutas debe retornar una funcion: $f");
        $cb($this->enrutador);
    }
}';
        file_put_contents($dirNucleo.'/CargadorRutas.php', $cargadorRutas);
        @chmod($dirNucleo.'/CargadorRutas.php', 0664);

        // ==================== Enrutador ====================
        $enrutador = '<?php
declare(strict_types=1);
namespace App\Nucleo;
class Enrutador {
    private array $rutas=[],$middlewaresRuta=[]; private PilaMiddleware $pila; private ?Renderizador $renderizador=null;
    public function __construct(){ $this->pila=new PilaMiddleware(); }
    public function setRenderizador(Renderizador $r):void{ $this->renderizador=$r; }
    public function usarMiddleware(Middleware $m):void{ $this->pila->agregar($m); }
    public function agregarRuta(string $met,string $ruta,string $man):void{ $this->rutas[$met][$ruta]=$man; }
    public function agregarMiddlewareRuta(string $met,string $ruta,Middleware $m):void{ $this->middlewaresRuta[$met][$ruta][]=$m; }
    private function obtenerUriBase():string{ $s=$_SERVER["SCRIPT_NAME"]??"/index.php"; $b=dirname(dirname(dirname($s))); if($b==="/"||$b==="\\\\")$b=""; return $b; }
    public function despachar(string $met,string $uri):void{
        try{
            $uri=parse_url($uri,PHP_URL_PATH); $uri=rtrim($uri,"/")?:"/"; $base=$this->obtenerUriBase();
            if($base!==""&&strpos($uri,$base)===0){$uri=substr($uri,strlen($base));$uri="/".ltrim($uri,"/");}
            if($uri===""||$uri===false)$uri="/";
            if(!isset($this->rutas[$met][$uri])){$this->mostrarError(404);return;}
            if(isset($this->middlewaresRuta[$met][$uri])) foreach($this->middlewaresRuta[$met][$uri] as $m) $this->pila->agregar($m);
            [$ctrl,$acc]=explode("@",$this->rutas[$met][$uri]); $cc="App\\\\Controladores\\\\$ctrl";
            if(!class_exists($cc)) throw new \RuntimeException("Controlador $cc no encontrado.");
            $dest=function()use($cc,$acc){$i=new $cc();return call_user_func([$i,$acc]);};
            $this->pila->ejecutar($_SERVER,$dest);
        }catch(\Throwable $e){ error_log("Error 500: ".$e->getMessage()); $this->mostrarError(500); }
    }
    private function mostrarError(int $codigo):void{
        http_response_code($codigo);
        if($this->renderizador!==null){ try{ $this->renderizador->mostrar("errores/$codigo",["urlBase"=>UrlHelper::base()?:"/"],null,false); return; }catch(\RuntimeException $e){} }
        echo match($codigo){404=>"404 - Pagina no encontrada",500=>"500 - Error interno del servidor",default=>"Error ".$codigo};
    }
}';
        file_put_contents($dirNucleo.'/Enrutador.php', $enrutador);
        @chmod($dirNucleo.'/Enrutador.php', 0664);

// ==================== Renderizador CORREGIDO ====================
$renderizador = '<?php
declare(strict_types=1);
namespace App\Nucleo;
class Renderizador {
    private string $directorioVistas;
    public function __construct(string $directorioVistas){ $this->directorioVistas=$directorioVistas; }
    public function mostrar(string $vista,array $datos=[],?string $plantilla="plantilla",bool $usarCache=true):void{
        extract($datos); $rv=$this->directorioVistas."/$vista.php";
        if(!file_exists($rv)) throw new \RuntimeException("Vista $vista no encontrada.");
        
        // No cachear vistas con formularios CSRF
        if($usarCache && strpos(file_get_contents($rv),"_token")!==false){ $usarCache=false; }
        
        $cache=new Cache(); $claveCache=Cache::claveVista($vista,$datos);
        if($usarCache){ $cc=$cache->obtener($claveCache); if($cc!==null){ echo $cc; return; } }
        ob_start(); require $rv; $contenido=ob_get_clean();
        if($plantilla){ $rp=$this->directorioVistas."/$plantilla.php"; if(file_exists($rp)){ ob_start(); require $rp; $contenido=ob_get_clean(); } }
        if($usarCache) $cache->guardar($claveCache,$contenido);
        echo $contenido;
    }
}';
file_put_contents($dirNucleo.'/Renderizador.php', $renderizador);
@chmod($dirNucleo.'/Renderizador.php', 0664);

        // ==================== Aplicacion ====================
        $aplicacion = '<?php
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
class Aplicacion {
    private static ?Aplicacion $instancia=null;
    private string $directorioRaiz;
    private Enrutador $enrutador;
    private array $configuracion;
    private ?Conexion $conexion=null;
    private Renderizador $renderizador;
    public function __construct(string $directorioRaiz){
        $this->directorioRaiz=rtrim($directorioRaiz,"/"); self::$instancia=$this;
        $this->cargarConfiguracion();
        $directorioVistas=$this->directorioRaiz."/app/Vistas";
        $this->renderizador=new Renderizador($directorioVistas);
        $this->enrutador=new Enrutador(); $this->enrutador->setRenderizador($this->renderizador);
        $this->cargarRutas(); $this->registrarMiddlewares();
    }
    public static function obtenerInstancia():self{ return self::$instancia; }
    public function obtenerDirectorioRaiz():string{ return $this->directorioRaiz; }
    public function obtenerConfiguracion(string $clave,$porDefecto=null){ return $this->configuracion[$clave]??$porDefecto; }
    public function obtenerConexion():Conexion{ if($this->conexion===null) $this->conexion=new Conexion($this->configuracion["base_datos"]); return $this->conexion; }
    public function obtenerRenderizador():Renderizador{ return $this->renderizador; }
    private function cargarConfiguracion():void{
        $rb=$this->directorioRaiz."/config/base_datos.php"; $ra=$this->directorioRaiz."/config/aplicacion.php";
        if(!file_exists($rb)||!file_exists($ra)) throw new \RuntimeException("Archivos de configuracion no encontrados.");
        $cb=require $rb; if(!is_array($cb)) throw new \RuntimeException("base_datos.php debe devolver un array.");
        $ca=require $ra; if(!is_array($ca)) throw new \RuntimeException("aplicacion.php debe devolver un array.");
        $this->configuracion=["base_datos"=>$cb,"app"=>$ca];
    }
    private function cargarRutas():void{
        $directorioRutas=$this->directorioRaiz."/app/rutas";
        $cargador=new CargadorRutas($this->enrutador,$directorioRutas); $cargador->cargar();
    }
    private function registrarMiddlewares():void{
        $this->enrutador->usarMiddleware(new MiddlewareRegistro()); $this->enrutador->usarMiddleware(new MiddlewareHttps());
        $this->enrutador->usarMiddleware(new MiddlewareCors()); $this->enrutador->usarMiddleware(new MiddlewareSeguridad());
        $this->enrutador->usarMiddleware(new MiddlewareCsrf()); $this->enrutador->usarMiddleware(new MiddlewareAutenticacion());
    }
    public function ejecutar():void{
        try{ $this->enrutador->despachar($_SERVER["REQUEST_METHOD"],$_SERVER["REQUEST_URI"]); }
        catch(\Throwable $e){
            error_log("Error 500: ".$e->getMessage());
            if(isset($this->renderizador)){ try{ http_response_code(500); $this->renderizador->mostrar("errores/500",["urlBase"=>UrlHelper::base()?:"/"],null,false); return; }catch(\RuntimeException $ex){} }
            http_response_code(500); echo "500 - Error interno del servidor";
        }
    }
}';
        file_put_contents($dirNucleo.'/Aplicacion.php', $aplicacion);
        @chmod($dirNucleo.'/Aplicacion.php', 0664);

// ==================== Index.php CORREGIDO ====================
$index = '<?php
declare(strict_types=1);
$directorioRaiz=dirname(__DIR__);
$configApp=require $directorioRaiz."/config/aplicacion.php";
ini_set("session.cookie_httponly","1"); ini_set("session.cookie_samesite","Lax");
if(!empty($configApp["seguridad"]["forzar_https"])) ini_set("session.cookie_secure","1");

// Iniciar sesión y generar token CSRF
session_start();
if (empty($_SESSION["_token_csrf"])) {
    $_SESSION["_token_csrf"] = bin2hex(random_bytes(32));
}

require_once $directorioRaiz."/app/Nucleo/ManejadorErrores.php";
$manejador=new App\Nucleo\ManejadorErrores($directorioRaiz,$configApp["entorno"]); $manejador->registrar();
require_once $directorioRaiz."/app/Nucleo/Autocargador.php"; App\Nucleo\Autocargador::registrar();
require_once $directorioRaiz."/app/Nucleo/UrlHelper.php";
require_once $directorioRaiz."/app/Nucleo/helpers.php";
require_once $directorioRaiz."/app/Nucleo/Aplicacion.php";
use App\Nucleo\Aplicacion,App\Nucleo\UrlHelper;
define("URL_BASE",UrlHelper::base()); define("URL_COMPLETA",UrlHelper::baseCompleta());
$app=new Aplicacion($directorioRaiz); $app->ejecutar();
';
file_put_contents($dirPublic.'/index.php', $index);
@chmod($dirPublic.'/index.php', 0664);
        // ==================== Vistas de error ====================
        $v404 = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>404 - No encontrada</title><style>body{font-family:system-ui,sans-serif;text-align:center;padding:4rem;color:#475569}h1{font-size:4rem;color:#64748b}p{margin:1rem 0;color:#94a3b8}a{color:#2563eb;text-decoration:none}</style></head><body><h1>404</h1><p>Pagina no encontrada</p><a href="<?= htmlspecialchars($urlBase ?? \'/\') ?>">← Volver al inicio</a></body></html>';
        file_put_contents($dirErrores.'/404.php', $v404);
        @chmod($dirErrores.'/404.php', 0664);

        $v500 = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>500 - Error</title><style>body{font-family:system-ui,sans-serif;text-align:center;padding:4rem;color:#475569}h1{font-size:4rem;color:#dc2626}p{margin:1rem 0;color:#94a3b8}a{color:#2563eb;text-decoration:none}</style></head><body><h1>500</h1><p>Error interno del servidor</p><a href="<?= htmlspecialchars($urlBase ?? \'/\') ?>">← Volver al inicio</a></body></html>';
        file_put_contents($dirErrores.'/500.php', $v500);
        @chmod($dirErrores.'/500.php', 0664);

        // ==================== Configuración ====================
        $cbd = "<?php\n\nreturn ['motor'=>'mysql','host'=>'localhost','puerto'=>3306,'nombre'=>'$db_nombre','usuario'=>'$db_usuario','clave'=>'$db_clave','juego_caracteres'=>'utf8mb4','opciones'=>[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]];\n";
        file_put_contents($dirConfig.'/base_datos.php', $cbd);
        @chmod($dirConfig.'/base_datos.php', 0664);

        $cap = "<?php\n\nreturn ['nombre'=>'$app_nombre','entorno'=>'$app_entorno','zona_horaria'=>'America/Caracas','api'=>['habilitada'=>true,'prefijo'=>'/api'],'cache'=>['habilitado'=>true,'duracion'=>3600],'seguridad'=>['maximos_intentos_login'=>5,'tiempo_bloqueo_login'=>60,'forzar_https'=>false,'correo'=>['modo'=>'archivo','remitente'=>'no-reply@localhost.local','directorio_prueba'=>'almacenamiento/correos','smtp'=>['host'=>'smtp.gmail.com','puerto'=>587,'usuario'=>'','clave'=>'','seguridad'=>'tls']]]];\n";
        file_put_contents($dirConfig.'/aplicacion.php', $cap);
        @chmod($dirConfig.'/aplicacion.php', 0664);

        // ==================== .htaccess ====================
        $hr = $dirProyecto.'/.htaccess';
        if (!file_exists($hr)) {
            file_put_contents($hr, "Options -Indexes\n<IfModule mod_rewrite.c>\nRewriteEngine On\nRewriteBase /$nombreProyecto/\nRewriteRule ^\$ raizphp/public/ [L]\nRewriteCond %{REQUEST_FILENAME} -f\nRewriteRule ^ - [L]\nRewriteRule ^(.*)\$ raizphp/public/\$1 [L]\n</IfModule>\n");
            @chmod($hr, 0664);
        }

        file_put_contents($dirPublic.'/.htaccess', "<IfModule mod_rewrite.c>\nRewriteEngine On\nRewriteCond %{HTTP:Authorization} ^(.+)$\nRewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]\nRewriteCond %{REQUEST_FILENAME} -f [OR]\nRewriteCond %{REQUEST_FILENAME} -d\nRewriteRule ^ - [L]\nRewriteRule ^ index.php [L]\n</IfModule>\n");
        @chmod($dirPublic.'/.htaccess', 0664);

        if (file_exists($dirRaiz.'/.htaccess')) unlink($dirRaiz.'/.htaccess');

        @chmod($dirConfig, 0775); @chmod($dirAlmacen, 0775); @chmod($dirRutas, 0775); @chmod($dirErrores, 0775);
        @exec("chown -R www-data:www-data $dirConfig $dirAlmacen $dirRutas $dirErrores 2>/dev/null");

        $exito = '¡Instalación completada!';
        $paso = 'finalizado';
    } catch (PDOException $e) { $errores[] = 'Error BD: ' . $e->getMessage();
    } catch (Exception $e) { $errores[] = 'Error: ' . $e->getMessage(); }
}

// ==================== NUEVO: Verificar BD en paso 2 ====================
$basesExistentes = [];
if ($paso === '2' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verificar_bd'])) {
    $basesExistentes = listarBasesDatos(
        $_POST['db_usuario'] ?? 'root',
        $_POST['db_clave'] ?? ''
    );
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador - RaízPHP v2.1</title>
    <style>
        :root{--color-primario:#2563eb;--color-exito:#16a34a;--color-error:#dc2626;--color-cancelar:#64748b;--redondeado:0.5rem}
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:system-ui,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .contenedor{background:white;border-radius:1rem;padding:2.5rem;max-width:620px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,0.3)}
        h1{text-align:center;color:var(--color-primario);margin-bottom:.3rem;font-size:2rem}
        .version{text-align:center;color:#94a3b8;font-size:.85rem;margin-bottom:.5rem}
        .subtitulo{text-align:center;color:#64748b;margin-bottom:2rem}
        .pasos{display:flex;justify-content:center;gap:1rem;margin-bottom:2rem}
        .paso{width:35px;height:35px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;background:#e2e8f0;color:#64748b}
        .paso.activo{background:var(--color-primario);color:white}
        .paso.completado{background:var(--color-exito);color:white}
        .campo{margin-bottom:1.2rem}
        label{display:block;font-weight:600;margin-bottom:.3rem;color:#1e293b}
        input[type="text"],input[type="password"],select{width:100%;padding:.7rem 1rem;border:2px solid #e2e8f0;border-radius:var(--redondeado);font-size:1rem;transition:border-color .2s}
        input:focus,select:focus{outline:none;border-color:var(--color-primario)}
        .checkbox-label{display:flex;align-items:center;gap:.5rem;cursor:pointer;font-weight:normal}
        .botones{display:flex;gap:1rem}
        .boton{flex:1;padding:.8rem;background:var(--color-primario);color:white;border:none;border-radius:var(--redondeado);font-size:1.1rem;font-weight:600;cursor:pointer;transition:all .2s;text-align:center;text-decoration:none}
        .boton:hover{filter:brightness(1.1);transform:translateY(-1px)}
        .boton-cancelar{background:var(--color-cancelar)}
        .exito{background:#dcfce7;color:var(--color-exito);padding:1rem;border-radius:var(--redondeado);margin-bottom:1rem;text-align:center}
        .error{background:#fee2e2;color:var(--color-error);padding:1rem;border-radius:var(--redondeado);margin-bottom:1rem}
        .requisito{display:flex;justify-content:space-between;align-items:center;padding:.5rem;border-bottom:1px solid #e2e8f0}
        .requisito.cumple{color:var(--color-exito)}.requisito.falla{color:var(--color-error)}
        .info-final{text-align:center;padding:2rem 0}.info-final h2{color:var(--color-exito);margin-bottom:1rem}
        .datos-prueba{background:#f0f9ff;border:1px solid #bae6fd;border-radius:var(--redondeado);padding:1rem;margin:1rem 0;text-align:left}
        .alerta{background:#fef3c7;border:1px solid #fbbf24;color:#92400e;padding:.8rem;border-radius:var(--redondeado);margin-bottom:1rem;font-size:.9rem}
        .novedad{background:#ede9fe;border:1px solid #c4b5fd;color:#5b21b6;padding:.8rem;border-radius:var(--redondeado);margin-bottom:1rem;font-size:.9rem}
        .ruta-info{background:#f1f5f9;padding:.8rem;border-radius:var(--redondeado);margin-bottom:1rem;font-size:.85rem;text-align:center;color:#475569}
        /* NUEVO: Estilos para opciones BD */
        .opcion-bd{display:block;padding:0.6rem;border:2px solid #e2e8f0;border-radius:0.5rem;margin-bottom:0.3rem;cursor:pointer;font-size:0.9rem}
        .opcion-bd:hover{border-color:var(--color-primario)}
    </style>
</head>
<body>
<div class="contenedor">
    <h1>🌱 RaízPHP</h1>
    <p class="version">v2.1 - Rutas por Módulos + URLs Dinámicas + CLI 30 comandos</p>
    <p class="subtitulo">Instalador Multiplataforma</p>






    

    <?php if ($paso === '1'): ?>
        <div class="pasos"><div class="paso activo">1</div><div class="paso">2</div><div class="paso">3</div></div>
        <h3 style="margin-bottom:1rem">Verificación de requisitos</h3>
        <div class="novedad">🚀 <strong>v2.1:</strong> Rutas por módulos · URLs dinámicas · Errores personalizados · CLI 30 comandos</div>
        <div class="ruta-info">📁 Proyecto: <strong><?= htmlspecialchars($nombreProyecto) ?></strong><br>🌐 URL: <code>http://localhost/<?= htmlspecialchars($nombreProyecto) ?></code></div>
        <?php foreach (verificarRequisitos() as $req): ?>
            <div class="requisito <?= $req['estado'] ? 'cumple' : 'falla' ?>"><span><?= htmlspecialchars($req['nombre']) ?></span><span><?= htmlspecialchars($req['actual']) ?></span></div>
        <?php endforeach; ?><br>
        <?php $ok = true; foreach (verificarRequisitos() as $req) if (!$req['estado']) $ok = false; ?>
        <?php if ($ok): ?>
            <div class="botones"><a href="/<?= htmlspecialchars($nombreProyecto) ?>/" class="boton boton-cancelar">Cancelar</a><a href="?paso=2" class="boton">Continuar →</a></div>
        <?php else: ?>
            <div class="alerta">⚠️ Corrige los requisitos antes de continuar.</div><a href="/<?= htmlspecialchars($nombreProyecto) ?>/" class="boton boton-cancelar" style="display:block">Cancelar</a>
        <?php endif; ?>

    <?php elseif ($paso === '2'): ?>
        <div class="pasos"><div class="paso completado">✓</div><div class="paso activo">2</div><div class="paso">3</div></div>
        <h3 style="margin-bottom:1rem">Configuración</h3>
        
        <?php if (!empty($basesExistentes)): ?>
        <div class="novedad" style="margin-bottom:1rem;">
            <strong>📂 Bases de datos encontradas:</strong>
            <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-top:0.5rem;">
                <?php foreach ($basesExistentes as $bd): ?>
                <span style="background:#ede9fe;color:#5b21b6;padding:0.3rem 0.7rem;border-radius:1rem;font-size:0.8rem;cursor:pointer;border:1px solid #c4b5fd;"
                      onclick="document.getElementById('db_nombre').value='<?= htmlspecialchars($bd) ?>';document.getElementById('accion_usar').checked=true;">
                    📁 <?= htmlspecialchars($bd) ?>
                </span>
                <?php endforeach; ?>
            </div>
            <small style="color:#64748b;display:block;margin-top:.5rem;">Haz clic en una para seleccionarla</small>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="?paso=instalar">
            <div class="campo"><label>Nombre de la aplicación</label><input type="text" name="app_nombre" value="Mi Aplicación" required></div>
            <div class="campo"><label>Nombre de la base de datos</label><input type="text" name="db_nombre" id="db_nombre" value="raiz_db" required></div>
            
            <div class="campo" style="background:#f8fafc;padding:.8rem;border-radius:0.5rem;border:1px solid #e2e8f0;">
                <label>🔄 Acción con la base de datos</label>
                <label class="opcion-bd"><input type="radio" name="accion_bd" value="crear" checked> <strong>🆕 Crear nueva base de datos</strong></label>
                <label class="opcion-bd"><input type="radio" name="accion_bd" value="usar_existente" id="accion_usar"> <strong>📂 Usar base de datos existente</strong></label>
            </div>
            
            <div class="campo"><label>Usuario MySQL</label><input type="text" name="db_usuario" id="db_usuario" value="root" required></div>
            <div class="campo"><label>Contraseña MySQL</label><input type="password" name="db_clave" id="db_clave"></div>
            <div class="campo"><label>Entorno</label><select name="app_entorno"><option value="desarrollo">Desarrollo</option><option value="produccion">Producción</option></select></div>
            <div class="campo"><label class="checkbox-label"><input type="checkbox" name="datos_prueba" value="1" checked>Insertar datos de prueba</label></div>
            
            <!-- Botón para verificar BD -->
            <div class="campo">
                <button type="button" class="boton" style="background:#7c3aed;font-size:0.9rem;padding:0.5rem;" onclick="verificarBases()">
                    🔍 Verificar bases de datos existentes
                </button>
            </div>
            
            <div class="botones"><a href="?paso=1" class="boton boton-cancelar">← Atrás</a><button type="submit" class="boton">Instalar 🚀</button></div>
        </form>
        
        <script>
        function verificarBases() {
            var usuario = document.getElementById('db_usuario').value;
            var clave = document.getElementById('db_clave').value;
            var form = document.createElement('form');
            form.method = 'POST'; form.action = '?paso=2';
            var i1 = document.createElement('input'); i1.type = 'hidden'; i1.name = 'db_usuario'; i1.value = usuario; form.appendChild(i1);
            var i2 = document.createElement('input'); i2.type = 'hidden'; i2.name = 'db_clave'; i2.value = clave; form.appendChild(i2);
            var i3 = document.createElement('input'); i3.type = 'hidden'; i3.name = 'verificar_bd'; i3.value = '1'; form.appendChild(i3);
            document.body.appendChild(form); form.submit();
        }
        </script>

    <?php elseif ($paso === 'finalizado'): ?>
        <div class="pasos"><div class="paso completado">✓</div><div class="paso completado">✓</div><div class="paso completado">✓</div></div>
        <div class="info-final"><h2>✅ ¡Instalación completada!</h2><p>RaízPHP v2.1 instalado correctamente.</p>
        <div class="ruta-info">📁 Proyecto: <strong><?= htmlspecialchars($nombreProyecto) ?></strong><br>🌐 URL: <code>http://localhost/<?= htmlspecialchars($nombreProyecto) ?></code></div>
        <div class="novedad">🚀 <strong>Admin:</strong> <code>/admin</code> | <strong>API:</strong> <code>/api/login</code> | <strong>CLI:</strong> <code>php raiz ayuda</code></div>
        <?php if ($insertarDatos): ?><div class="datos-prueba"><strong>👤 Usuarios (contraseña: password):</strong><ul><li><strong>admin@raizphp.local</strong> (admin)</li><li>maria@example.com</li><li>carlos@example.com</li><li>ana@example.com</li></ul></div>
        <?php else: ?><div class="alerta"><strong>📝 Instalación limpia</strong><br>Registra el primer usuario en: <code>/registro</code></div><?php endif; ?><br>
        <div class="botones"><a href="instalar.php" class="boton boton-cancelar">Reinstalar</a><a href="/<?= htmlspecialchars($nombreProyecto) ?>/" class="boton">Ir a la aplicación →</a></div></div>
    <?php endif; ?>

    <?php if ($exito): ?><div class="exito"><?= htmlspecialchars($exito) ?></div><?php endif; ?>
    <?php if ($errores): ?><?php foreach ($errores as $error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
    <div class="botones"><a href="?paso=2" class="boton boton-cancelar">← Volver</a><a href="/<?= htmlspecialchars($nombreProyecto) ?>/" class="boton">Salir</a></div><?php endif; ?>
</div>
</body>
</html>
