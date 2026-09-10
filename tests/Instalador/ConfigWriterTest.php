<?php
declare(strict_types=1);

class ConfigWriterTest
{
    private function crearConfigSeguro(string $db_nombre, string $db_usuario, string $db_clave): string
    {
        return "<?php\n\nreturn " . var_export([
            'motor' => 'mysql',
            'host' => 'localhost',
            'puerto' => 3306,
            'nombre' => $db_nombre,
            'usuario' => $db_usuario,
            'clave' => $db_clave,
            'juego_caracteres' => 'utf8mb4',
            'opciones' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        ], true) . ";\n";
    }
    
    public function testConfigSimple(): void
    {
        $config = $this->crearConfigSeguro('raiz_db', 'root', 'password');
        Ayuda::contiene("'nombre' => 'raiz_db'", $config);
        Ayuda::contiene("'usuario' => 'root'", $config);
    }
    
    public function testConfigConComillaSimple(): void
    {
        $config = $this->crearConfigSeguro('raiz_db', 'root', "pass'word");
        $archivoTemp = sys_get_temp_dir() . '/test_config_' . uniqid() . '.php';
        file_put_contents($archivoTemp, $config);
        $salida = shell_exec('php -l ' . escapeshellarg($archivoTemp) . ' 2>&1');
        @unlink($archivoTemp);
        Ayuda::contiene('No syntax errors', $salida, 'El PHP generado debe ser válido');
    }
    
    public function testConfigConComillaDoble(): void
    {
        $config = $this->crearConfigSeguro('raiz_db', 'root', 'pass"word');
        $archivoTemp = sys_get_temp_dir() . '/test_config_' . uniqid() . '.php';
        file_put_contents($archivoTemp, $config);
        $salida = shell_exec('php -l ' . escapeshellarg($archivoTemp) . ' 2>&1');
        @unlink($archivoTemp);
        Ayuda::contiene('No syntax errors', $salida);
    }
    
    public function testConfigConBackslash(): void
    {
        $config = $this->crearConfigSeguro('raiz_db', 'root', 'pass\\word');
        $archivoTemp = sys_get_temp_dir() . '/test_config_' . uniqid() . '.php';
        file_put_contents($archivoTemp, $config);
        $salida = shell_exec('php -l ' . escapeshellarg($archivoTemp) . ' 2>&1');
        @unlink($archivoTemp);
        Ayuda::contiene('No syntax errors', $salida);
    }
    
    public function testConfigSeCargaCorrectamente(): void
    {
        $claveOriginal = "pass'con\"comillas\\mixtas";
        $config = $this->crearConfigSeguro('mi_db', 'usuario', $claveOriginal);
        $archivoTemp = sys_get_temp_dir() . '/test_config_' . uniqid() . '.php';
        file_put_contents($archivoTemp, $config);
        $cargado = require $archivoTemp;
        @unlink($archivoTemp);
        Ayuda::igual($claveOriginal, $cargado['clave'], 'La clave debe mantenerse intacta');
        Ayuda::igual('mi_db', $cargado['nombre']);
        Ayuda::igual('usuario', $cargado['usuario']);
    }
    
    public function testConfigConNewlines(): void
    {
        $clave = "linea1\nlinea2";
        $config = $this->crearConfigSeguro('raiz_db', 'root', $clave);
        $archivoTemp = sys_get_temp_dir() . '/test_config_' . uniqid() . '.php';
        file_put_contents($archivoTemp, $config);
        $cargado = require $archivoTemp;
        @unlink($archivoTemp);
        Ayuda::igual($clave, $cargado['clave'], 'Los newlines deben mantenerse');
    }
}
