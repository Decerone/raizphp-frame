<?php
declare(strict_types=1);
namespace App\Nucleo;
class Enrutador
{
    private array $rutas = [];
    private PilaMiddleware $pila;
    private array $middlewaresRuta = [];
    public function __construct() { $this->pila = new PilaMiddleware(); }
    public function usarMiddleware(Middleware $middleware): void { $this->pila->agregar($middleware); }
    public function agregarRuta(string $metodo, string $ruta, string $manejador): void { $this->rutas[$metodo][$ruta] = $manejador; }
    public function agregarMiddlewareRuta(string $metodo, string $ruta, Middleware $middleware): void { $this->middlewaresRuta[$metodo][$ruta][] = $middleware; }
   
private function obtenerUriBase(): string
{
    $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $base = dirname(dirname(dirname($script)));
    if ($base === '/' || $base === '\\') $base = '';
    return $base;
}


    public function despachar(string $metodo, string $uri): void
    {
        

        $uri = parse_url($uri, PHP_URL_PATH); $uri = rtrim($uri, '/') ?: '/';
        $base = $this->obtenerUriBase();
        if ($base !== '' && strpos($uri, $base) === 0) { $uri = substr($uri, strlen($base)); $uri = '/' . ltrim($uri, '/'); }
        if ($uri === '' || $uri === false) $uri = '/';
        if (!isset($this->rutas[$metodo][$uri])) { http_response_code(404); echo '404 - Página no encontrada'; return; }
        if (isset($this->middlewaresRuta[$metodo][$uri])) { foreach ($this->middlewaresRuta[$metodo][$uri] as $m) $this->pila->agregar($m); }
        [$controlador, $accion] = explode('@', $this->rutas[$metodo][$uri]);
        $claseControlador = "App\\Controladores\\$controlador";
        if (!class_exists($claseControlador)) throw new \RuntimeException("Controlador $claseControlador no encontrado.");
        $destino = function() use ($claseControlador, $accion) { $instancia = new $claseControlador(); return call_user_func([$instancia, $accion]); };
        $this->pila->ejecutar($_SERVER, $destino);
    }
}
