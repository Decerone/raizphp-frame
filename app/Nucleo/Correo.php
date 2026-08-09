<?php
declare(strict_types=1);
namespace App\Nucleo;
class Correo
{
    private string $modo; private string $remitente; private string $directorioPrueba; private array $configSmtp;
    public function __construct()
    {
        $configCorreo = Aplicacion::obtenerInstancia()->obtenerConfiguracion('app')['seguridad']['correo'] ?? [];
        $this->modo = $configCorreo['modo'] ?? 'archivo';
        $this->remitente = $configCorreo['remitente'] ?? 'no-reply@localhost.local';
        $this->configSmtp = $configCorreo['smtp'] ?? [];
        $this->directorioPrueba = Aplicacion::obtenerInstancia()->obtenerDirectorioRaiz() . '/' . ($configCorreo['directorio_prueba'] ?? 'almacenamiento/correos');
    }
    public function enviar(string $destinatario, string $asunto, string $cuerpoHtml): bool
    {
        return match ($this->modo) {
            'archivo' => $this->enviarArchivo($destinatario, $asunto, $cuerpoHtml),
            'mail' => $this->enviarMail($destinatario, $asunto, $cuerpoHtml),
            default => throw new \RuntimeException("Modo no soportado: {$this->modo}")
        };
    }
    private function enviarArchivo(string $destinatario, string $asunto, string $cuerpoHtml): bool
    {
        if (!is_dir($this->directorioPrueba)) mkdir($this->directorioPrueba, 0775, true);
        $contenido = "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>$asunto</title></head><body><h2>Correo de prueba</h2><p><strong>Para:</strong> $destinatario</p><p><strong>Asunto:</strong> $asunto</p><hr>$cuerpoHtml</body></html>";
        return file_put_contents($this->directorioPrueba . '/correo-' . date('Y-m-d-His') . '.html', $contenido) !== false;
    }
    private function enviarMail(string $destinatario, string $asunto, string $cuerpoHtml): bool
    {
        $cabeceras = "From: {$this->remitente}\r\nContent-Type: text/html; charset=UTF-8\r\n";
        return mail($destinatario, $asunto, $cuerpoHtml, $cabeceras);
    }
}
