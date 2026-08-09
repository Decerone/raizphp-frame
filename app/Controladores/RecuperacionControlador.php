<?php
declare(strict_types=1);
namespace App\Controladores;
use App\Nucleo\ControladorBase;
use App\Nucleo\Correo;
use App\Nucleo\Validador;
use App\Nucleo\Autenticacion;
use App\Modelos\Usuario;
use App\Modelos\Recuperacion;
class RecuperacionControlador extends ControladorBase
{
    public function solicitar(): void { $this->renderizar('solicitar_recuperacion',['titulo'=>'Recuperar Contraseña','error'=>'','exito'=>'']); }
    public function enviarEnlace(): void
    {
        $v = new Validador($_POST); $v->reglas(['email'=>['requerido','email']]);
        if (!$v->validar()) { $this->renderizar('solicitar_recuperacion',['titulo'=>'Recuperar Contraseña','error'=>implode('<br>',$v->obtenerErrores()),'exito'=>'']); return; }
        $usuario = Usuario::buscarPorEmail($_POST['email']);
        if ($usuario) {
            Recuperacion::invalidarTokensAnteriores($usuario->id);
            $token = bin2hex(random_bytes(32)); $expiracion = time()+3600;
            $rec = new Recuperacion(['usuario_id'=>$usuario->id,'token'=>$token,'expiracion'=>$expiracion,'usado'=>0]); $rec->guardar();
            $enlace = 'http://localhost'.$this->obtenerUrlBase().'/restablecer?token='.$token;
            (new Correo())->enviar($usuario->email,'Recuperación de contraseña',"<h2>Recuperación</h2><p><a href='$enlace'>$enlace</a></p><p>Expira en 1 hora.</p>");
        }
        $this->renderizar('solicitar_recuperacion',['titulo'=>'Recuperar Contraseña','error'=>'','exito'=>'Si el correo existe, recibirás un enlace.']);
    }
    public function restablecer(): void
    {
        $token = $_GET['token']??'';
        $rec = Recuperacion::buscarPorToken($token);
        if (!$rec) { $this->renderizar('restablecer',['titulo'=>'Restablecer','error'=>'Enlace inválido o expirado.','exito'=>'','token'=>'']); return; }
        $this->renderizar('restablecer',['titulo'=>'Restablecer','error'=>'','exito'=>'','token'=>$token]);
    }
    public function cambiarPassword(): void
    {
        $token = $_POST['token']??''; $rec = Recuperacion::buscarPorToken($token);
        if (!$rec) { $this->renderizar('restablecer',['titulo'=>'Restablecer','error'=>'Enlace inválido.','exito'=>'','token'=>'']); return; }
        $v = new Validador($_POST); $v->reglas(['password'=>['requerido','seguro']]);
        if (!$v->validar()) { $this->renderizar('restablecer',['titulo'=>'Restablecer','error'=>implode('<br>',$v->obtenerErrores()),'exito'=>'','token'=>$token]); return; }
        $usuario = Usuario::encontrar($rec->usuario_id);
        if ($usuario) { $usuario->password = Autenticacion::hashearPassword($_POST['password']); $usuario->guardar(); $rec->usado=1; $rec->guardar(); }
        $this->renderizar('restablecer',['titulo'=>'Restablecer','error'=>'','exito'=>'Contraseña actualizada. <a href="'.$this->obtenerUrlBase().'/login">Iniciar sesión</a>','token'=>'']);
    }
    private function obtenerUrlBase(): string { $s = dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))); return ($s==='/'||$s==='\\')?'':$s; }
}
