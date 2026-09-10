<?php
declare(strict_types=1);

$dirRaiz = dirname(__DIR__);
require_once $dirRaiz . '/app/Nucleo/Autocargador.php';
App\Nucleo\Autocargador::registrar();
require_once __DIR__ . '/Ayuda.php';

define('T_VERDE', "\033[32m");
define('T_ROJO', "\033[31m");
define('T_AMARILLO', "\033[33m");
define('T_CYAN', "\033[36m");
define('T_MORADO', "\033[35m");
define('T_RESET', "\033[0m");
define('T_NEGRITA', "\033[1m");

echo PHP_EOL . T_VERDE . T_NEGRITA;
echo "  🌱  RaízPHP — Suite de Tests" . PHP_EOL;
echo T_RESET . T_CYAN . "  " . str_repeat('─', 45) . T_RESET . PHP_EOL . PHP_EOL;

$directorioTests = __DIR__;
$archivos = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($directorioTests, RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($iterator as $archivo) {
    if ($archivo->isFile() && preg_match('/Test\.php$/', $archivo->getFilename())) {
        $archivos[] = $archivo->getPathname();
    }
}
sort($archivos);

if (empty($archivos)) {
    echo T_AMARILLO . "⚠️  No se encontraron tests." . T_RESET . PHP_EOL;
    exit(0);
}

$totalTests = 0; $totalPasados = 0; $totalFallados = 0; $fallos = [];

foreach ($archivos as $archivoTest) {
    $nombreClase = pathinfo($archivoTest, PATHINFO_FILENAME);
    $nombreRelativo = str_replace($directorioTests . '/', '', $archivoTest);
    echo T_MORADO . T_NEGRITA . "📄 " . $nombreRelativo . T_RESET . PHP_EOL;
    require_once $archivoTest;
    if (!class_exists($nombreClase)) {
        echo T_ROJO . "   ❌ Clase no encontrada: $nombreClase" . T_RESET . PHP_EOL;
        continue;
    }
    $instancia = new $nombreClase();
    foreach (get_class_methods($instancia) as $metodo) {
        if (strpos($metodo, 'test') !== 0) continue;
        $totalTests++;
        Ayuda::reiniciar();
        try {
            $instancia->$metodo();
            $totalPasados++;
            echo T_VERDE . "   ✅ $metodo" . T_RESET . PHP_EOL;
        } catch (Throwable $e) {
            $totalFallados++;
            $fallos[] = ['archivo' => $nombreRelativo, 'test' => $metodo, 'error' => $e->getMessage()];
            echo T_ROJO . "   ❌ $metodo — " . $e->getMessage() . T_RESET . PHP_EOL;
        }
    }
    echo PHP_EOL;
}

echo T_CYAN . str_repeat('─', 45) . T_RESET . PHP_EOL;
if ($totalFallados === 0) {
    echo T_VERDE . T_NEGRITA . "✅ TODOS LOS TESTS PASARON" . T_RESET . PHP_EOL;
} else {
    echo T_ROJO . T_NEGRITA . "❌ HAY FALLOS" . T_RESET . PHP_EOL . PHP_EOL;
    foreach ($fallos as $f) {
        echo T_ROJO . "   • " . $f['archivo'] . " → " . $f['test'] . T_RESET . PHP_EOL;
        echo "     " . $f['error'] . PHP_EOL;
    }
}
echo PHP_EOL;
echo "  Total:     " . T_NEGRITA . $totalTests . T_RESET . PHP_EOL;
echo "  Pasados:   " . T_VERDE . $totalPasados . T_RESET . PHP_EOL;
echo "  Fallados:  " . ($totalFallados > 0 ? T_ROJO : T_VERDE) . $totalFallados . T_RESET . PHP_EOL . PHP_EOL;
exit($totalFallados > 0 ? 1 : 0);
