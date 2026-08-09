<?php
declare(strict_types=1);
namespace App\Seeders;
use App\Nucleo\Seeder;
use App\Nucleo\Autenticacion;
class UsuariosSeeder extends Seeder
{
    public function ejecutar(): void
    {
        $usuarios = [
            ['nombre'=>'María','apellido'=>'Pérez','email'=>'maria@example.com','password'=>Autenticacion::hashearPassword('Password1!'),'rol'=>'usuario','edad'=>28],
            ['nombre'=>'Carlos','apellido'=>'Gómez','email'=>'carlos@example.com','password'=>Autenticacion::hashearPassword('Password1!'),'rol'=>'usuario','edad'=>35],
            ['nombre'=>'Ana','apellido'=>'Martínez','email'=>'ana@example.com','password'=>Autenticacion::hashearPassword('Password1!'),'rol'=>'admin','edad'=>22],
            ['nombre'=>'Admin','apellido'=>'Sistema','email'=>'admin@raizphp.local','password'=>Autenticacion::hashearPassword('Admin123!'),'rol'=>'admin','edad'=>30],
        ];
        $stmt = $this->pdo->prepare("INSERT INTO usuarios (nombre,apellido,email,password,rol,edad) VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE email=email");
        foreach ($usuarios as $u) $stmt->execute([$u['nombre'],$u['apellido'],$u['email'],$u['password'],$u['rol'],$u['edad']]);
    }
}
