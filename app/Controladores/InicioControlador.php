<?php
declare(strict_types=1);
namespace App\Controladores;
use App\Nucleo\ControladorBase;
use App\Modelos\Usuario;
class InicioControlador extends ControladorBase
{
    public function index(): void { $usuarios = Usuario::todos(); $this->renderizar('inicio',['titulo'=>'Bienvenido a RaízPHP','usuarios'=>$usuarios]); }
}
