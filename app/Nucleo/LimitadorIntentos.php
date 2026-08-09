<?php
declare(strict_types=1);
namespace App\Nucleo;
use PDO;
class LimitadorIntentos
{
    private static string $tabla = 'intentos_login';
    public static function registrarIntento(string $ip): void { $conexion = Aplicacion::obtenerInstancia()->obtenerConexion(); $pdo = $conexion->obtenerPDO(); $stmt = $pdo->prepare("SELECT id FROM ".self::$tabla." WHERE ip = ?"); $stmt->execute([$ip]); if ($stmt->fetch()) { $stmt = $pdo->prepare("UPDATE ".self::$tabla." SET intentos = intentos + 1, ultimo_intento = ? WHERE ip = ?"); $stmt->execute([time(), $ip]); } else { $stmt = $pdo->prepare("INSERT INTO ".self::$tabla." (ip, intentos, ultimo_intento) VALUES (?, 1, ?)"); $stmt->execute([$ip, time()]); } }
    public static function estaBloqueado(string $ip, int $max = 5, int $tiempo = 60): bool { $conexion = Aplicacion::obtenerInstancia()->obtenerConexion(); $pdo = $conexion->obtenerPDO(); $stmt = $pdo->prepare("SELECT intentos, ultimo_intento FROM ".self::$tabla." WHERE ip = ?"); $stmt->execute([$ip]); $r = $stmt->fetch(PDO::FETCH_ASSOC); if (!$r) return false; $intentos = (int)$r['intentos']; $retardo = pow(2, $intentos); $tiempoReal = max($tiempo, $retardo); if (time() - (int)$r['ultimo_intento'] > $tiempoReal) { self::reiniciarIntentos($ip); return false; } return $intentos >= $max; }
    public static function tiempoRestante(string $ip, int $max = 5, int $tiempo = 60): int { $conexion = Aplicacion::obtenerInstancia()->obtenerConexion(); $pdo = $conexion->obtenerPDO(); $stmt = $pdo->prepare("SELECT intentos, ultimo_intento FROM ".self::$tabla." WHERE ip = ?"); $stmt->execute([$ip]); $r = $stmt->fetch(PDO::FETCH_ASSOC); if (!$r) return 0; $intentos = (int)$r['intentos']; $retardo = pow(2, $intentos); $tiempoReal = max($tiempo, $retardo); return max(0, $tiempoReal - (time() - (int)$r['ultimo_intento'])); }
    public static function reiniciarIntentos(string $ip): void { $conexion = Aplicacion::obtenerInstancia()->obtenerConexion(); $pdo = $conexion->obtenerPDO(); $stmt = $pdo->prepare("DELETE FROM ".self::$tabla." WHERE ip = ?"); $stmt->execute([$ip]); }
}
