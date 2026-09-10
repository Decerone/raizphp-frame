<?php
declare(strict_types=1);
namespace App\Nucleo;
use PDO;

class LimitadorIntentos
{
    private static string $tabla = 'intentos_login';
    private const TECHO_SEGUNDOS = 3600; // 1 hora máximo
    
    private static function identidad(string $ip, ?string $email = null): string
    {
        // Identidad: IP + email cuando exista (más robusto)
        return $email ? $ip . '|' . strtolower(trim($email)) : $ip;
    }
    
    private static function calcularRetardo(int $intentos, int $tiempoBase): int
    {
        // Retardo exponencial con techo
        $retardo = $tiempoBase * (2 ** min($intentos - 1, 10));
        return min($retardo, self::TECHO_SEGUNDOS);
    }
    
    public static function registrarIntento(string $ip, ?string $email = null): void
    {
        $conexion = Aplicacion::obtenerInstancia()->obtenerConexion();
        $pdo = $conexion->obtenerPDO();
        $clave = self::identidad($ip, $email);
        
        $stmt = $pdo->prepare("SELECT id FROM " . self::$tabla . " WHERE ip = ?");
        $stmt->execute([$clave]);
        
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("UPDATE " . self::$tabla . " SET intentos = intentos + 1, ultimo_intento = ? WHERE ip = ?");
            $stmt->execute([time(), $clave]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO " . self::$tabla . " (ip, intentos, ultimo_intento) VALUES (?, 1, ?)");
            $stmt->execute([$clave, time()]);
        }
    }
    
    public static function estaBloqueado(string $ip, ?string $email = null, int $max = 5, int $tiempo = 60): bool
    {
        $conexion = Aplicacion::obtenerInstancia()->obtenerConexion();
        $pdo = $conexion->obtenerPDO();
        $clave = self::identidad($ip, $email);
        
        $stmt = $pdo->prepare("SELECT intentos, ultimo_intento FROM " . self::$tabla . " WHERE ip = ?");
        $stmt->execute([$clave]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$r) return false;
        
        $intentos = (int)$r['intentos'];
        if ($intentos < $max) return false;
        
        $retardo = self::calcularRetardo($intentos, $tiempo);
        $transcurrido = time() - (int)$r['ultimo_intento'];
        
        if ($transcurrido > $retardo) {
            self::reiniciarIntentos($ip, $email);
            return false;
        }
        
        return true;
    }
    
    public static function tiempoRestante(string $ip, ?string $email = null, int $max = 5, int $tiempo = 60): int
    {
        $conexion = Aplicacion::obtenerInstancia()->obtenerConexion();
        $pdo = $conexion->obtenerPDO();
        $clave = self::identidad($ip, $email);
        
        $stmt = $pdo->prepare("SELECT intentos, ultimo_intento FROM " . self::$tabla . " WHERE ip = ?");
        $stmt->execute([$clave]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$r) return 0;
        
        $intentos = (int)$r['intentos'];
        $retardo = self::calcularRetardo($intentos, $tiempo);
        $transcurrido = time() - (int)$r['ultimo_intento'];
        
        return max(0, $retardo - $transcurrido);
    }
    
    public static function reiniciarIntentos(string $ip, ?string $email = null): void
    {
        $conexion = Aplicacion::obtenerInstancia()->obtenerConexion();
        $pdo = $conexion->obtenerPDO();
        $clave = self::identidad($ip, $email);
        $stmt = $pdo->prepare("DELETE FROM " . self::$tabla . " WHERE ip = ?");
        $stmt->execute([$clave]);
    }
}
