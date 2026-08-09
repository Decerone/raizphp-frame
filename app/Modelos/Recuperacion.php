<?php
declare(strict_types=1);
namespace App\Modelos;
use App\Nucleo\ModeloBase;
class Recuperacion extends ModeloBase
{
    protected static string $tabla = 'recuperaciones';
    protected static string $clavePrimaria = 'id';
    public static function buscarPorToken(string $token): ?self { return static::consultar()->donde('token','=',$token)->donde('usado','=',0)->donde('expiracion','>',time())->primero(); }
    public static function invalidarTokensAnteriores(int $usuarioId): void { $pdo = \App\Nucleo\Aplicacion::obtenerInstancia()->obtenerConexion()->obtenerPDO(); $stmt = $pdo->prepare("UPDATE recuperaciones SET usado=1 WHERE usuario_id=? AND usado=0"); $stmt->execute([$usuarioId]); }
}
