<?php
declare(strict_types=1);
namespace App\Nucleo;
use PDO;
class GestorMigraciones
{
    private string $directorioMigraciones; private Conexion $conexion; private PDO $pdo;
    public function __construct(string $directorioRaiz)
    {
        $this->directorioMigraciones = $directorioRaiz . '/app/Migraciones';
        $this->conexion = Aplicacion::obtenerInstancia()->obtenerConexion();
        $this->pdo = $this->conexion->obtenerPDO();
        $this->crearTablaMigraciones();
    }
    public function migrar(): array { $archivos = $this->obtenerArchivos(); $ejecutadas = $this->obtenerEjecutadas(); $pendientes = array_diff($archivos, $ejecutadas); $resultados = []; foreach ($pendientes as $a) { try { $c = $this->cargarMigracion($a); $c->subir(); $this->registrar($a); $resultados[] = "✅ $a"; } catch (\Throwable $e) { $resultados[] = "❌ $a: ".$e->getMessage(); break; } } if (empty($pendientes)) $resultados[] = "ℹ️ No hay migraciones pendientes."; return $resultados; }
    public function revertir(): array { $ejecutadas = $this->obtenerEjecutadas(); if (empty($ejecutadas)) return ["ℹ️ No hay migraciones para revertir."]; $ultima = end($ejecutadas); try { $c = $this->cargarMigracion($ultima); $c->bajar(); $this->eliminar($ultima); return ["✅ $ultima revertida."]; } catch (\Throwable $e) { return ["❌ $ultima: ".$e->getMessage()]; } }
    private function crearTablaMigraciones(): void { $this->pdo->exec("CREATE TABLE IF NOT EXISTS migraciones (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(255) NOT NULL UNIQUE, ejecutada_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); }
    private function obtenerArchivos(): array { if (!is_dir($this->directorioMigraciones)) return []; $archivos = scandir($this->directorioMigraciones); $archivos = array_filter($archivos, fn($a) => preg_match('/^\d{4}_\d{2}_\d{2}_\d{6}_.+\.php$/', $a)); sort($archivos); return $archivos; }
    private function obtenerEjecutadas(): array { $stmt = $this->pdo->query("SELECT nombre FROM migraciones ORDER BY id"); return $stmt->fetchAll(PDO::FETCH_COLUMN); }
    private function cargarMigracion(string $archivo): Migracion { require_once $this->directorioMigraciones . '/' . $archivo; $clase = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', pathinfo($archivo, PATHINFO_FILENAME)); $claseCompleta = "App\\Migraciones\\$clase"; return new $claseCompleta(); }
    private function registrar(string $nombre): void { $stmt = $this->pdo->prepare("INSERT INTO migraciones (nombre) VALUES (?)"); $stmt->execute([$nombre]); }
    private function eliminar(string $nombre): void { $stmt = $this->pdo->prepare("DELETE FROM migraciones WHERE nombre = ?"); $stmt->execute([$nombre]); }
}
