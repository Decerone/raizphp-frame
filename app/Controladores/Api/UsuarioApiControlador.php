<?php
declare(strict_types=1);
namespace App\Controladores\Api;
use App\Nucleo\ControladorApi;
use App\Modelos\Usuario;
use App\Nucleo\Autenticacion;
class UsuarioApiControlador extends ControladorApi
{
    public function lista(): void { $usuarios = Usuario::todos(); $datos = []; foreach ($usuarios as $u) $datos[] = ['id'=>$u->id,'nombre'=>$u->nombreCompleto(),'email'=>$u->email,'rol'=>$u->rol,'edad'=>$u->edad]; $this->exito($datos); }
    public function mostrar(): void { $id = (int)($_GET['id']??0); $u = Usuario::encontrar($id); if (!$u) $this->error('No encontrado.',404); $this->exito(['id'=>$u->id,'nombre'=>$u->nombreCompleto(),'email'=>$u->email,'rol'=>$u->rol,'edad'=>$u->edad]); }
    public function crear(): void { $datos = $this->obtenerJson(); $err = $this->verificarCampos($datos,['nombre','apellido','email','password','edad']); if ($err) $this->error($err); $datos['password'] = Autenticacion::hashearPassword($datos['password']); $datos['rol'] = 'usuario'; $u = new Usuario($datos); if ($u->guardar()) $this->exito(['id'=>$u->id,'nombre'=>$u->nombreCompleto(),'email'=>$u->email],'Creado.'); $this->error('Error al crear.',500); }
}
