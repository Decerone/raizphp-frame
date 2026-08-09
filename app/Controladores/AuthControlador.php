<?php
declare(strict_types=1);
namespace App\Controladores;
use App\Nucleo\ControladorBase;
use App\Nucleo\Autenticacion;
use App\Nucleo\Validador;
use App\Nucleo\LimitadorIntentos;
use App\Nucleo\Aplicacion;
use App\Nucleo\HelperCsrf;
use App\Modelos\Usuario;
class AuthControlador extends ControladorBase
{
    public function formularioLogin(): void { $this->renderizar('login',['titulo'=>'Iniciar Sesión','error'=>'']); }
    public function iniciarSesion(): void
    {
        $ip = $_SERVER['REMOTE_ADDR']??'127.0.0.1'; if ($ip==='::1') $ip='127.0.0.1';
        $cfg = Aplicacion::obtenerInstancia()->obtenerConfiguracion('app');
        $max = $cfg['seguridad']['maximos_intentos_login']??5; $tmp = $cfg['seguridad']['tiempo_bloqueo_login']??60;
        if (LimitadorIntentos::estaBloqueado($ip,$max,$tmp)) { $r = LimitadorIntentos::tiempoRestante($ip,$max,$tmp); $m = intdiv($r,60); $s = $r%60; $msg = $m>0?"Espere {$m}min {$s}s":"Espere {$s}s"; $this->renderizar('login',['titulo'=>'Iniciar Sesión','error'=>"Demasiados intentos. $msg"]); return; }
        $v = new Validador($_POST); $v->reglas(['email'=>['requerido','email'],'password'=>['requerido']]);
        if (!$v->validar()) { $this->renderizar('login',['titulo'=>'Iniciar Sesión','error'=>implode('<br>',$v->obtenerErrores())]); return; }
        $usuario = Usuario::buscarPorEmail($_POST['email']);
        if ($usuario && $usuario->verificarPassword($_POST['password'])) { LimitadorIntentos::reiniciarIntentos($ip); Autenticacion::iniciarSesion($usuario->aArray()); header('Location: '.$this->obtenerUrlBase().'/'); exit; }
        LimitadorIntentos::registrarIntento($ip); $this->renderizar('login',['titulo'=>'Iniciar Sesión','error'=>'Credenciales inválidas.']);
    }
    public function apiLogin(): void
    {
        $datos = json_decode(file_get_contents('php://input'), true)??[];
        if (empty($datos['email'])||empty($datos['password'])) { http_response_code(400); header('Content-Type: application/json; charset=UTF-8'); echo json_encode(['error'=>'Email y contraseña requeridos.']); exit; }
        $usuario = Usuario::buscarPorEmail($datos['email']);
        if ($usuario && $usuario->verificarPassword($datos['password'])) { $token = $usuario->generarToken(); header('Content-Type: application/json; charset=UTF-8'); echo json_encode(['mensaje'=>'Inicio exitoso.','token'=>$token,'usuario'=>['id'=>$usuario->id,'nombre'=>$usuario->nombreCompleto(),'email'=>$usuario->email,'rol'=>$usuario->rol]]); exit; }
        http_response_code(401); header('Content-Type: application/json; charset=UTF-8'); echo json_encode(['error'=>'Credenciales inválidas.']); exit;
    }
    public function formularioRegistro(): void { $this->renderizar('registro',['titulo'=>'Crear Cuenta','error'=>'']); }
    public function registrar(): void
    {
        $v = new Validador($_POST); $v->reglas(['nombre'=>['requerido','min:2','max:50'],'apellido'=>['requerido','min:2','max:50'],'email'=>['requerido','email','unico:usuarios,email'],'password'=>['requerido','seguro'],'edad'=>['requerido','numerico','min:1','max:120']]);
        if (!$v->validar()) { $this->renderizar('registro',['titulo'=>'Crear Cuenta','error'=>implode('<br>',$v->obtenerErrores())]); return; }
        $datos = $_POST; unset($datos['_token']); $datos['rol']='usuario'; $datos['password']=Autenticacion::hashearPassword($datos['password']);
        $usuario = new Usuario($datos);
        if ($usuario->guardar()) { Autenticacion::iniciarSesion($usuario->aArray()); header('Location: '.$this->obtenerUrlBase().'/'); exit; }
        $this->renderizar('registro',['titulo'=>'Crear Cuenta','error'=>'Error al guardar.']);
    }
    public function cerrarSesion(): void { HelperCsrf::destruirYRegenerar(); Autenticacion::cerrarSesion(); header('Location: '.$this->obtenerUrlBase().'/login'); exit; }
    private function obtenerUrlBase(): string { $s = dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))); return ($s==='/'||$s==='\\')?'':$s; }
}
