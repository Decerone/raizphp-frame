<?php
declare(strict_types=1);
namespace App\Nucleo;

class Peticion
{
    private array $query;
    private array $post;
    private array $server;
    private array $cookies;
    private array $headers;
    private ?array $json = null;
    private array $parametros = [];
    private string $cuerpoCrudo;
    
    public function __construct()
    {
        $this->query = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->cookies = $_COOKIE;
        $this->headers = $this->extraerHeaders();
        $this->cuerpoCrudo = file_get_contents('php://input') ?: '';
    }
    
    private function extraerHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $nombre = str_replace('_', '-', substr($key, 5));
                $headers[strtolower($nombre)] = $value;
            }
        }
        return $headers;
    }
    
    public function metodo(): string { return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET'); }
    public function uri(): string { return parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'; }
    public function ip(): string { $ip = $this->server['REMOTE_ADDR'] ?? '127.0.0.1'; return $ip === '::1' ? '127.0.0.1' : $ip; }
    public function host(): string { return $this->server['HTTP_HOST'] ?? 'localhost'; }
    public function esHttps(): bool { return (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off'); }
    
    public function obtener(string $clave, $defecto = null) { return $this->query[$clave] ?? $defecto; }
    public function post(string $clave, $defecto = null) { return $this->post[$clave] ?? $defecto; }
    public function entrada(string $clave, $defecto = null) { return $this->post[$clave] ?? $this->query[$clave] ?? $defecto; }
    public function header(string $nombre, ?string $defecto = null): ?string { return $this->headers[strtolower($nombre)] ?? $defecto; }
    public function cookie(string $nombre, ?string $defecto = null): ?string { return $this->cookies[$nombre] ?? $defecto; }
    
    public function json(): array
    {
        if ($this->json === null) {
            $this->json = json_decode($this->cuerpoCrudo, true) ?? [];
        }
        return $this->json;
    }
    
    public function cuerpoCrudo(): string { return $this->cuerpoCrudo; }
    
    public function establecerParametros(array $parametros): void { $this->parametros = $parametros; }
    public function parametro(string $nombre, $defecto = null) { return $this->parametros[$nombre] ?? $defecto; }
    public function parametros(): array { return $this->parametros; }
    
    public function esPost(): bool { return $this->metodo() === 'POST'; }
    public function esGet(): bool { return $this->metodo() === 'GET'; }
    public function esAjax(): bool { return strtolower($this->header('X-Requested-With', '')) === 'xmlhttprequest'; }
    public function aceptaJson(): bool { return strpos($this->header('Accept', ''), 'application/json') !== false; }
}
