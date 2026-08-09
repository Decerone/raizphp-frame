<?php
declare(strict_types=1);
namespace App\Controladores\Admin;
use App\Nucleo\ControladorBase;
use App\Nucleo\Autenticacion;
use App\Nucleo\Validador;
use App\Modelos\Usuario;
class UsuarioAdminControlador extends ControladorBase
{
    private function datosAdmin(): array { return ['usuarioAdmin'=>Autenticacion::obtenerUsuario(),'rutaActual'=>parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)]; }
    public function lista(): void { $usuarios = Usuario::todos(); $mensaje = $_GET['mensaje']??''; $this->renderizarAdmin('admin/usuarios',['titulo'=>'Usuarios','usuarios'=>$usuarios,'mensaje'=>$mensaje,'adminBase'=>$this->urlAdminBase()]); }
    public function editar(): void { $id = (int)($_GET['id']??0); $usuario = Usuario::encontrar($id); if (!$usuario) { header('Location: '.$this->urlAdminBase().'/usuarios?mensaje=No encontrado'); exit; } $this->renderizarAdmin('admin/editar-usuario',['titulo'=>'Editar','usuario'=>$usuario,'error'=>'','adminBase'=>$this->urlAdminBase()]); }
    public function actualizar(): void
    {
        $id = (int)($_POST['id']??0); $usuario = Usuario::encontrar($id);
        if (!$usuario) { header('Location: '.$this->urlAdminBase().'/usuarios?mensaje=No encontrado'); exit; }
        $v = new Validador($_POST); $v->reglas(['nombre'=>['requerido','min:2'],'apellido'=>['requerido','min:2'],'email'=>['requerido','email'],'rol'=>['requerido']]);
        if (!$v->validar()) { $this->renderizarAdmin('admin/editar-usuario',['titulo'=>'Editar','usuario'=>$usuario,'error'=>implode('<br>',$v->obtenerErrores()),'adminBase'=>$this->urlAdminBase()]); return; }
        $usuario->nombre=$_POST['nombre']; $usuario->apellido=$_POST['apellido']; $usuario->email=$_POST['email']; $usuario->rol=$_POST['rol']; $usuario->edad=(int)($_POST['edad']??0);
        if (!empty($_POST['password'])) $usuario->password = Autenticacion::hashearPassword($_POST['password']);
        $usuario->guardar(); header('Location: '.$this->urlAdminBase().'/usuarios?mensaje=Actualizado'); exit;
    }
    public function eliminar(): void { $id = (int)($_GET['id']??0); $usuario = Usuario::encontrar($id); if ($usuario) $usuario->eliminar(); header('Location: '.$this->urlAdminBase().'/usuarios?mensaje=Eliminado'); exit; }
    private function renderizarAdmin(string $vista, array $datos = []): void
    {
        $scriptName = $_SERVER['SCRIPT_NAME']??'/index.php'; $urlBase = rtrim(dirname(dirname(dirname($scriptName))), '/');
        if ($urlBase==='/'||$urlBase==='\\') $urlBase = '';
        $datos['urlBase'] = $urlBase; $datos = array_merge($this->datosAdmin(), $datos);
        $renderizador = new \App\Nucleo\Renderizador(\App\Nucleo\Aplicacion::obtenerInstancia()->obtenerDirectorioRaiz().'/app/Vistas');
        $renderizador->mostrar($vista, $datos, 'admin/plantilla');
    }
    private function urlAdminBase(): string { $s = dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))); return (($s==='/'||$s==='\\')?'':$s).'/admin'; }
}
