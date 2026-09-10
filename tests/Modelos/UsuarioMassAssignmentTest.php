<?php
declare(strict_types=1);

use App\Modelos\Usuario;

class UsuarioMassAssignmentTest
{
    public function testRolNoSeAsignaPorMassAssignment(): void
    {
        $u = new Usuario(['nombre' => 'X', 'rol' => 'admin']);
        Ayuda::igual(null, $u->rol, 'rol no debe asignarse por mass assignment');
    }

    public function testApiTokenNoSeAsignaPorMassAssignment(): void
    {
        $u = new Usuario(['nombre' => 'X', 'api_token' => 'hack']);
        Ayuda::igual(null, $u->api_token, 'api_token no debe asignarse por mass assignment');
    }

    public function testNombreSiSeAsigna(): void
    {
        $u = new Usuario(['nombre' => 'X']);
        Ayuda::igual('X', $u->nombre);
    }

    public function testEmailSiSeAsigna(): void
    {
        $u = new Usuario(['email' => 'test@example.com']);
        Ayuda::igual('test@example.com', $u->email);
    }

    public function testArrayOcultoNoMuestraPassword(): void
    {
        $u = new Usuario(['nombre' => 'X']);
        $u->password = 'secreto';
        $array = $u->aArray();
        Ayuda::falso(isset($array['password']), 'password no debe estar en aArray()');
    }

    public function testArrayOcultoNoMuestraApiToken(): void
    {
        $u = new Usuario(['nombre' => 'X']);
        $u->api_token = 'token-secreto';
        $array = $u->aArray();
        Ayuda::falso(isset($array['api_token']), 'api_token no debe estar en aArray()');
    }

    public function testArrayCompletoSiMuestraPassword(): void
    {
        $u = new Usuario(['nombre' => 'X']);
        $u->password = 'secreto';
        $array = $u->aArrayCompleto();
        Ayuda::verdadero(isset($array['password']), 'password debe estar en aArrayCompleto()');
    }
}
