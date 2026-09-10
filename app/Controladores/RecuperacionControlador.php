<?php
declare(strict_types=1);
namespace App\Controladores;
use App\Nucleo\ControladorBase;
use App\Nucleo\Correo;
use App\Nucleo\Validador;
use App\Nucleo\Autenticacion;
use App\Nucleo\UrlHelper;
use App\Modelos\Usuario;
use App\Modelos\Recuperacion;

class RecuperacionControlador extends ControladorBase
{
    public function solicitar(): void
    {
        $this->renderizar('solicitar_recuperacion', [
            'titulo' => 'Recuperar Contraseña',
            'error' => '',
            'exito' => ''
        ], 'plantilla', false);
    }
    
    public function enviarEnlace(): void
    {
        $v = new Validador($_POST);
        $v->reglas(['email' => ['requerido', 'email']]);
        if (!$v->validar()) {
            $this->renderizar('solicitar_recuperacion', [
                'titulo' => 'Recuperar Contraseña',
                'error' => implode('<br>', $v->obtenerErrores()),
                'exito' => ''
            ], 'plantilla', false);
            return;
        }
        
        $usuario = Usuario::buscarPorEmail($_POST['email']);
        if ($usuario) {
            Recuperacion::invalidarTokensAnteriores($usuario->id);
            
            $token = bin2hex(random_bytes(32));
            $expiracion = time() + 3600;
            
            // C8: Guardar HASH del token, no el token en claro
            $tokenHash = hash('sha256', $token);
            
            $rec = new Recuperacion([
                'usuario_id' => $usuario->id,
                'token' => $tokenHash,
                'expiracion' => $expiracion,
                'usado' => 0
            ]);
            $rec->guardar();
            
            // C8: URL real con UrlHelper
            $enlace = UrlHelper::urlCompleta('/restablecer?token=' . $token);
            
            (new Correo())->enviar(
                $usuario->email,
                'Recuperación de contraseña',
                "<h2>Recuperación</h2><p>Haz clic en el enlace para restablecer tu contraseña:</p><p><a href='" . htmlspecialchars($enlace, ENT_QUOTES, 'UTF-8') . "'>Restablecer contraseña</a></p><p>Este enlace expira en 1 hora.</p>"
            );
        }
        
        $this->renderizar('solicitar_recuperacion', [
            'titulo' => 'Recuperar Contraseña',
            'error' => '',
            'exito' => 'Si el correo existe, recibirás un enlace.'
        ], 'plantilla', false);
    }
    
    public function restablecer(): void
    {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            $this->renderizar('restablecer', [
                'titulo' => 'Restablecer',
                'error' => 'Enlace inválido.',
                'exito' => '',
                'token' => ''
            ], 'plantilla', false);
            return;
        }
        
        $tokenHash = hash('sha256', $token);
        $rec = Recuperacion::buscarPorToken($tokenHash);
        
        if (!$rec) {
            $this->renderizar('restablecer', [
                'titulo' => 'Restablecer',
                'error' => 'Enlace inválido o expirado.',
                'exito' => '',
                'token' => ''
            ], 'plantilla', false);
            return;
        }
        
        $this->renderizar('restablecer', [
            'titulo' => 'Restablecer',
            'error' => '',
            'exito' => '',
            'token' => $token
        ], 'plantilla', false);
    }
    
    public function cambiarPassword(): void
    {
        $token = $_POST['token'] ?? '';
        $tokenHash = hash('sha256', $token);
        $rec = Recuperacion::buscarPorToken($tokenHash);
        
        if (!$rec) {
            $this->renderizar('restablecer', [
                'titulo' => 'Restablecer',
                'error' => 'Enlace inválido.',
                'exito' => '',
                'token' => ''
            ], 'plantilla', false);
            return;
        }
        
        $v = new Validador($_POST);
        $v->reglas(['password' => ['requerido', 'seguro']]);
        if (!$v->validar()) {
            $this->renderizar('restablecer', [
                'titulo' => 'Restablecer',
                'error' => implode('<br>', $v->obtenerErrores()),
                'exito' => '',
                'token' => $token
            ], 'plantilla', false);
            return;
        }
        
        $usuario = Usuario::encontrar($rec->usuario_id);
        if ($usuario) {
            $usuario->password = Autenticacion::hashearPassword($_POST['password']);
            $usuario->guardar();
            
            $rec->usado = 1;
            $rec->guardar();
            
            // C8: Invalidar sesión activa al cambiar password
            Autenticacion::cerrarSesion();
        }
        
        $this->renderizar('restablecer', [
            'titulo' => 'Restablecer',
            'error' => '',
            'exito' => 'Contraseña actualizada. <a href="' . UrlHelper::url('/login') . '">Iniciar sesión</a>',
            'token' => ''
        ], 'plantilla', false);
    }
}
