<?php
declare(strict_types=1);
namespace App\Controladores\Admin;
use App\Nucleo\ControladorBase;
use App\Modelos\Usuario;

class UsuarioAdminControlador extends ControladorBase
{
    public function lista(): void
    {
        $usuarios = Usuario::todos();
        $mensaje = $_GET['mensaje'] ?? '';
        $this->renderizarAdmin('admin/usuarios', [
            'titulo' => 'Usuarios',
            'usuarios' => $usuarios,
            'mensaje' => $mensaje,
            'adminBase' => $this->urlAdminBase()
        ]);
    }
    
    public function editar(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $usuario = Usuario::encontrar($id);
        if (!$usuario) {
            header('Location: ' . $this->urlAdminBase() . '/usuarios');
            exit;
        }
        $this->renderizarAdmin('admin/usuario_form', [
            'titulo' => 'Editar Usuario',
            'usuario' => $usuario,
            'adminBase' => $this->urlAdminBase()
        ]);
    }
    
    public function actualizar(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $usuario = Usuario::encontrar($id);
        if ($usuario) {
            $usuario->nombre = trim($_POST['nombre'] ?? '');
            $usuario->apellido = trim($_POST['apellido'] ?? '');
            $usuario->email = trim($_POST['email'] ?? '');
            $usuario->rol = $_POST['rol'] ?? 'usuario';
            $usuario->edad = (int)($_POST['edad'] ?? 0);
            $usuario->guardar();
        }
        header('Location: ' . $this->urlAdminBase() . '/usuarios?mensaje=Actualizado');
        exit;
    }
    
    public function eliminar(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $usuario = Usuario::encontrar($id);
        if ($usuario) $usuario->eliminar();
        header('Location: ' . $this->urlAdminBase() . '/usuarios?mensaje=Eliminado');
        exit;
    }
    
    private function renderizarAdmin(string $vista, array $datos = []): void
    {
        $datos['urlBase'] = $this->urlBase();
        $renderizador = new \App\Nucleo\Renderizador(\App\Nucleo\Aplicacion::obtenerInstancia()->obtenerDirectorioRaiz() . '/app/Vistas');
        $renderizador->mostrar($vista, $datos, 'admin/plantilla');
    }
    
    private function urlAdminBase(): string
    {
        $s = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
        return (($s === '/' || $s === '\\') ? '' : $s) . '/admin';
    }
    
    private function urlBase(): string
    {
        $s = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
        return ($s === '/' || $s === '\\') ? '' : $s;
    }
}
