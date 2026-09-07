<?php

declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

$paso = $_GET['paso'] ?? '1';
$errores = [];
$exito = '';
$insertarDatos = false;
$dirRaiz = dirname(__DIR__);
$dirProyecto = dirname($dirRaiz);
$nombreProyecto = basename($dirProyecto);
$dirConfig = $dirRaiz . '/config';
$dirAlmacen = $dirRaiz . '/almacenamiento';
$dirRutas = $dirRaiz . '/app/rutas';
$dirPublic = $dirRaiz . '/public';
$lockFile = $dirAlmacen . '/instalado.lock';

// ==================== BLOQUEO SI YA ESTÁ INSTALADO ====================
if (file_exists($lockFile)) {
    http_response_code(403);
    echo "<!DOCTYPE html><html lang=\"es\"><head><meta charset=\"UTF-8\"><title>Instalación completada</title>
    <style>body{font-family:system-ui,sans-serif;background:#f8fafc;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
    .caja{background:white;padding:2rem;border-radius:1rem;box-shadow:0 10px 30px rgba(0,0,0,0.1);text-align:center;max-width:450px}
    h1{color:#16a34a;font-size:1.5rem}p{color:#475569}a{color:#2563eb}</style></head><body>
    <div class=\"caja\"><h1>✅ RaízPHP ya está instalado</h1>
    <p>Por seguridad, elimina el archivo <code>public/instalar.php</code> del servidor.</p>
    <a href=\"/$nombreProyecto/\">Ir a la aplicación →</a></div></body></html>";
    exit;
}

// ==================== FUNCIONES ====================
function verificarRequisitos(): array
{
    return [
        ['nombre' => 'PHP 8.0+', 'estado' => version_compare(PHP_VERSION, '8.0.0', '>='), 'actual' => PHP_VERSION],
        ['nombre' => 'PDO', 'estado' => extension_loaded('pdo'), 'actual' => extension_loaded('pdo') ? 'Instalado' : 'No'],
        ['nombre' => 'PDO MySQL', 'estado' => extension_loaded('pdo_mysql'), 'actual' => extension_loaded('pdo_mysql') ? 'Instalado' : 'No'],
        ['nombre' => 'OpenSSL', 'estado' => extension_loaded('openssl'), 'actual' => extension_loaded('openssl') ? 'Instalado' : 'No'],
        ['nombre' => 'mbstring', 'estado' => extension_loaded('mbstring'), 'actual' => extension_loaded('mbstring') ? 'Instalado' : 'No'],
        ['nombre' => 'JSON', 'estado' => extension_loaded('json'), 'actual' => extension_loaded('json') ? 'Instalado' : 'No'],
        ['nombre' => 'Permisos escritura', 'estado' => is_writable(__DIR__ . '/../almacenamiento'), 'actual' => is_writable(__DIR__ . '/../almacenamiento') ? 'Correcto' : 'Sin permisos'],
    ];
}

function listarBasesDatos(string $usuario, string $clave): array
{
    try {
        $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", $usuario, $clave, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);
        $stmt = $pdo->query("SHOW DATABASES");
        $bases = [];
        $sistema = ['information_schema', 'mysql', 'performance_schema', 'sys', 'phpmyadmin'];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $nombreBD = $row['Database'];
            if (!in_array(strtolower($nombreBD), $sistema)) {
                $bases[] = $nombreBD;
            }
        }
        sort($bases);
        return $bases;
    } catch (PDOException $e) {
        return [];
    }
}

// ==================== PROCESAR INSTALACIÓN ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $paso === 'instalar') {
    $db_nombre = $_POST['db_nombre'] ?? '';
    $db_usuario = $_POST['db_usuario'] ?? 'root';
    $db_clave = $_POST['db_clave'] ?? '';
    $app_nombre = $_POST['app_nombre'] ?? 'Mi Aplicación';
    $app_entorno = $_POST['app_entorno'] ?? 'desarrollo';
    $insertarDatos = isset($_POST['datos_prueba']) && $_POST['datos_prueba'] === '1';
    $accionBD = $_POST['accion_bd'] ?? 'crear';

    // Validar nombre de BD
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $db_nombre)) {
        $errores[] = 'Nombre de base de datos inválido. Solo letras, números y guiones bajos.';
    }

    if (empty($errores)) {
        try {
            $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", $db_usuario, $db_clave, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            
            // Verificar si la BD existe
            $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
            $stmt->execute([$db_nombre]);
            $bdExiste = (bool) $stmt->fetch();
            
            if ($bdExiste && $accionBD === 'crear') {
                throw new Exception("La base de datos \"$db_nombre\" ya existe. Selecciona 'Usar existente' o elige otro nombre.");
            }
            
            if ($accionBD === 'crear') {
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_nombre` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }
            $pdo->exec("USE `$db_nombre`");

            // Crear tablas
            $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(50) NOT NULL, apellido VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL DEFAULT '',
                api_token VARCHAR(64) NULL UNIQUE, rol VARCHAR(20) NOT NULL DEFAULT 'usuario', edad INT NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $pdo->exec("CREATE TABLE IF NOT EXISTS intentos_login (
                id INT AUTO_INCREMENT PRIMARY KEY, ip VARCHAR(45) NOT NULL,
                intentos INT NOT NULL DEFAULT 1, ultimo_intento INT NOT NULL, INDEX idx_ip (ip)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $pdo->exec("CREATE TABLE IF NOT EXISTS recuperaciones (
                id INT AUTO_INCREMENT PRIMARY KEY, usuario_id INT NOT NULL, token VARCHAR(64) NOT NULL UNIQUE,
                expiracion INT NOT NULL, usado TINYINT(1) NOT NULL DEFAULT 0,
                creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $pdo->exec("CREATE TABLE IF NOT EXISTS migraciones (
                id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(255) NOT NULL UNIQUE,
                ejecutada_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // Datos de prueba
            if ($insertarDatos) {
                $pw = password_hash('password', PASSWORD_BCRYPT);
                $pdo->exec("INSERT INTO usuarios (nombre, apellido, email, password, rol, edad) VALUES 
                    ('María','Pérez','maria@example.com','$pw','usuario',28),
                    ('Carlos','Gómez','carlos@example.com','$pw','usuario',35),
                    ('Ana','Martínez','ana@example.com','$pw','admin',22),
                    ('Admin','Sistema','admin@raizphp.local','$pw','admin',30)
                    ON DUPLICATE KEY UPDATE email=email");
            }

            // Crear SOLO directorios de datos (NO toca app/Nucleo/)
            $directorios = [
                $dirConfig,
                $dirAlmacen . '/logs',
                $dirAlmacen . '/correos',
                $dirAlmacen . '/cache',
                $dirAlmacen . '/backups',
                $dirRutas
            ];
            foreach ($directorios as $dir) {
                if (!is_dir($dir)) mkdir($dir, 0775, true);
            }

            // Config con var_export (SEGURO - sin interpolación)
            $configBD = "<?php\n\nreturn " . var_export([
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
            file_put_contents($dirConfig . '/base_datos.php', $configBD);

            $configApp = "<?php\n\nreturn " . var_export([
                'nombre' => $app_nombre,
                'entorno' => $app_entorno,
                'zona_horaria' => 'America/Caracas',
                'api' => ['habilitada' => true, 'prefijo' => '/api'],
                'cache' => ['habilitado' => true, 'duracion' => 3600],
                'seguridad' => [
                    'maximos_intentos_login' => 5,
                    'tiempo_bloqueo_login' => 60,
                    'forzar_https' => false,
                    'correo' => [
                        'modo' => 'archivo',
                        'remitente' => 'no-reply@localhost.local',
                        'directorio_prueba' => 'almacenamiento/correos',
                        'smtp' => ['host' => 'smtp.gmail.com', 'puerto' => 587, 'usuario' => '', 'clave' => '', 'seguridad' => 'tls']
                    ]
                ]
            ], true) . ";\n";
            file_put_contents($dirConfig . '/aplicacion.php', $configApp);

            // Crear .htaccess si no existe
            $hr = $dirProyecto . '/.htaccess';
            if (!file_exists($hr)) {
                file_put_contents($hr, "Options -Indexes\n<IfModule mod_rewrite.c>\nRewriteEngine On\nRewriteBase /$nombreProyecto/\nRewriteRule ^\$ raizphp/public/ [L]\nRewriteCond %{REQUEST_FILENAME} -f\nRewriteRule ^ - [L]\nRewriteRule ^(.*)\$ raizphp/public/\$1 [L]\n</IfModule>\n");
                chmod($hr, 0664);
            }

            // Crear lock de instalación
            file_put_contents($lockFile, date('Y-m-d H:i:s'));

            $exito = '¡Instalación completada! Por seguridad, elimina public/instalar.php.';
            $paso = 'finalizado';
            
        } catch (PDOException $e) {
            $errores[] = 'Error BD: ' . $e->getMessage();
        } catch (Exception $e) {
            $errores[] = 'Error: ' . $e->getMessage();
        }
    }
}

// ==================== VERIFICAR BD EN PASO 2 ====================
$basesExistentes = [];
if ($paso === '2' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verificar_bd'])) {
    $basesExistentes = listarBasesDatos(
        $_POST['db_usuario'] ?? 'root',
        $_POST['db_clave'] ?? ''
    );
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador - RaízPHP v2.1</title>
    <style>
        :root{--color-primario:#2563eb;--color-exito:#16a34a;--color-error:#dc2626;--color-cancelar:#64748b;--redondeado:0.5rem}
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:system-ui,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .contenedor{background:white;border-radius:1rem;padding:2.5rem;max-width:620px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,0.3)}
        h1{text-align:center;color:var(--color-primario);margin-bottom:.3rem;font-size:2rem}
        .version{text-align:center;color:#94a3b8;font-size:.85rem;margin-bottom:.5rem}
        .subtitulo{text-align:center;color:#64748b;margin-bottom:2rem}
        .pasos{display:flex;justify-content:center;gap:1rem;margin-bottom:2rem}
        .paso{width:35px;height:35px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;background:#e2e8f0;color:#64748b}
        .paso.activo{background:var(--color-primario);color:white}
        .paso.completado{background:var(--color-exito);color:white}
        .campo{margin-bottom:1.2rem}
        label{display:block;font-weight:600;margin-bottom:.3rem;color:#1e293b}
        input[type="text"],input[type="password"],select{width:100%;padding:.7rem 1rem;border:2px solid #e2e8f0;border-radius:var(--redondeado);font-size:1rem;transition:border-color .2s}
        input:focus,select:focus{outline:none;border-color:var(--color-primario)}
        .checkbox-label{display:flex;align-items:center;gap:.5rem;cursor:pointer;font-weight:normal}
        .botones{display:flex;gap:1rem}
        .boton{flex:1;padding:.8rem;background:var(--color-primario);color:white;border:none;border-radius:var(--redondeado);font-size:1.1rem;font-weight:600;cursor:pointer;transition:all .2s;text-align:center;text-decoration:none}
        .boton:hover{filter:brightness(1.1);transform:translateY(-1px)}
        .boton-cancelar{background:var(--color-cancelar)}
        .exito{background:#dcfce7;color:var(--color-exito);padding:1rem;border-radius:var(--redondeado);margin-bottom:1rem;text-align:center}
        .error{background:#fee2e2;color:var(--color-error);padding:1rem;border-radius:var(--redondeado);margin-bottom:1rem}
        .requisito{display:flex;justify-content:space-between;align-items:center;padding:.5rem;border-bottom:1px solid #e2e8f0}
        .requisito.cumple{color:var(--color-exito)}.requisito.falla{color:var(--color-error)}
        .info-final{text-align:center;padding:2rem 0}.info-final h2{color:var(--color-exito);margin-bottom:1rem}
        .datos-prueba{background:#f0f9ff;border:1px solid #bae6fd;border-radius:var(--redondeado);padding:1rem;margin:1rem 0;text-align:left}
        .alerta{background:#fef3c7;border:1px solid #fbbf24;color:#92400e;padding:.8rem;border-radius:var(--redondeado);margin-bottom:1rem;font-size:.9rem}
        .novedad{background:#ede9fe;border:1px solid #c4b5fd;color:#5b21b6;padding:.8rem;border-radius:var(--redondeado);margin-bottom:1rem;font-size:.9rem}
        .ruta-info{background:#f1f5f9;padding:.8rem;border-radius:var(--redondeado);margin-bottom:1rem;font-size:.85rem;text-align:center;color:#475569}
        .opcion-bd{display:block;padding:0.6rem;border:2px solid #e2e8f0;border-radius:0.5rem;margin-bottom:0.3rem;cursor:pointer;font-size:0.9rem}
        .opcion-bd:hover{border-color:var(--color-primario)}
    </style>
</head>
<body>
<div class="contenedor">
    <h1>🌱 RaízPHP</h1>
    <p class="version">v2.1 - Instalador Seguro</p>
    <p class="subtitulo">Solo crea config y tablas · El núcleo NO se modifica</p>

    <?php if ($paso === '1'): ?>
        <div class="pasos"><div class="paso activo">1</div><div class="paso">2</div><div class="paso">3</div></div>
        <h3 style="margin-bottom:1rem">Verificación de requisitos</h3>
        <div class="ruta-info">📁 Proyecto: <strong><?= htmlspecialchars($nombreProyecto) ?></strong><br>🌐 URL: <code>http://localhost/<?= htmlspecialchars($nombreProyecto) ?></code></div>
        <?php foreach (verificarRequisitos() as $req): ?>
            <div class="requisito <?= $req['estado'] ? 'cumple' : 'falla' ?>"><span><?= htmlspecialchars($req['nombre']) ?></span><span><?= htmlspecialchars($req['actual']) ?></span></div>
        <?php endforeach; ?><br>
        <?php $ok = true; foreach (verificarRequisitos() as $req) if (!$req['estado']) $ok = false; ?>
        <?php if ($ok): ?>
            <div class="botones"><a href="/<?= htmlspecialchars($nombreProyecto) ?>/" class="boton boton-cancelar">Cancelar</a><a href="?paso=2" class="boton">Continuar →</a></div>
        <?php else: ?>
            <div class="alerta">⚠️ Corrige los requisitos antes de continuar.</div><a href="/<?= htmlspecialchars($nombreProyecto) ?>/" class="boton boton-cancelar" style="display:block">Cancelar</a>
        <?php endif; ?>

    <?php elseif ($paso === '2'): ?>
        <div class="pasos"><div class="paso completado">✓</div><div class="paso activo">2</div><div class="paso">3</div></div>
        <h3 style="margin-bottom:1rem">Configuración</h3>
        
        <?php if (!empty($basesExistentes)): ?>
        <div class="novedad" style="margin-bottom:1rem;">
            <strong>📂 Bases de datos encontradas:</strong>
            <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-top:0.5rem;">
                <?php foreach ($basesExistentes as $bd): ?>
                <span style="background:#ede9fe;color:#5b21b6;padding:0.3rem 0.7rem;border-radius:1rem;font-size:0.8rem;cursor:pointer;border:1px solid #c4b5fd;"
                      onclick="document.getElementById('db_nombre').value='<?= htmlspecialchars($bd) ?>';document.getElementById('accion_usar').checked=true;">
                    📁 <?= htmlspecialchars($bd) ?>
                </span>
                <?php endforeach; ?>
            </div>
            <small style="color:#64748b;display:block;margin-top:.5rem;">Haz clic en una para seleccionarla</small>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="?paso=instalar">
            <div class="campo"><label>Nombre de la aplicación</label><input type="text" name="app_nombre" value="Mi Aplicación" required></div>
            <div class="campo"><label>Nombre de la base de datos</label><input type="text" name="db_nombre" id="db_nombre" value="raiz_db" required pattern="[a-zA-Z0-9_]+"></div>
            
            <div class="campo" style="background:#f8fafc;padding:.8rem;border-radius:0.5rem;border:1px solid #e2e8f0;">
                <label>🔄 Acción con la base de datos</label>
                <label class="opcion-bd"><input type="radio" name="accion_bd" value="crear" checked> <strong>🆕 Crear nueva base de datos</strong></label>
                <label class="opcion-bd"><input type="radio" name="accion_bd" value="usar_existente" id="accion_usar"> <strong>📂 Usar base de datos existente</strong></label>
            </div>
            
            <div class="campo"><label>Usuario MySQL</label><input type="text" name="db_usuario" id="db_usuario" value="root" required></div>
            <div class="campo"><label>Contraseña MySQL</label><input type="password" name="db_clave" id="db_clave"></div>
            <div class="campo"><label>Entorno</label><select name="app_entorno"><option value="desarrollo">Desarrollo</option><option value="produccion">Producción</option></select></div>
            <div class="campo"><label class="checkbox-label"><input type="checkbox" name="datos_prueba" value="1" checked>Insertar datos de prueba</label></div>
            
            <div class="campo">
                <button type="button" class="boton" style="background:#7c3aed;font-size:0.9rem;padding:0.5rem;" onclick="verificarBases()">
                    🔍 Verificar bases de datos existentes
                </button>
            </div>
            
            <div class="botones"><a href="?paso=1" class="boton boton-cancelar">← Atrás</a><button type="submit" class="boton">Instalar 🚀</button></div>
        </form>
        
        <script>
        function verificarBases() {
            var usuario = document.getElementById('db_usuario').value;
            var clave = document.getElementById('db_clave').value;
            var form = document.createElement('form');
            form.method = 'POST'; form.action = '?paso=2';
            var i1 = document.createElement('input'); i1.type = 'hidden'; i1.name = 'db_usuario'; i1.value = usuario; form.appendChild(i1);
            var i2 = document.createElement('input'); i2.type = 'hidden'; i2.name = 'db_clave'; i2.value = clave; form.appendChild(i2);
            var i3 = document.createElement('input'); i3.type = 'hidden'; i3.name = 'verificar_bd'; i3.value = '1'; form.appendChild(i3);
            document.body.appendChild(form); form.submit();
        }
        </script>

    <?php elseif ($paso === 'finalizado'): ?>
        <div class="pasos"><div class="paso completado">✓</div><div class="paso completado">✓</div><div class="paso completado">✓</div></div>
        <div class="info-final"><h2>✅ ¡Instalación completada!</h2><p>RaízPHP v2.1 instalado correctamente.</p>
        <div class="ruta-info">📁 Proyecto: <strong><?= htmlspecialchars($nombreProyecto) ?></strong><br>🌐 URL: <code>http://localhost/<?= htmlspecialchars($nombreProyecto) ?></code></div>
        <div class="alerta"><strong>⚠️ Por seguridad:</strong> Elimina <code>public/instalar.php</code> del servidor.</div>
        <?php if ($insertarDatos): ?><div class="datos-prueba"><strong>👤 Usuarios (contraseña: password):</strong><ul><li><strong>admin@raizphp.local</strong> (admin)</li><li>maria@example.com</li><li>carlos@example.com</li><li>ana@example.com</li></ul></div>
        <?php else: ?><div class="alerta"><strong>📝 Instalación limpia</strong><br>Registra el primer usuario en: <code>/registro</code></div><?php endif; ?><br>
        <div class="botones"><a href="/<?= htmlspecialchars($nombreProyecto) ?>/" class="boton">Ir a la aplicación →</a></div></div>
    <?php endif; ?>

    <?php if ($exito): ?><div class="exito"><?= htmlspecialchars($exito) ?></div><?php endif; ?>
    <?php if ($errores): ?><?php foreach ($errores as $error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
    <div class="botones"><a href="?paso=2" class="boton boton-cancelar">← Volver</a><a href="/<?= htmlspecialchars($nombreProyecto) ?>/" class="boton">Salir</a></div><?php endif; ?>
</div>
</body>
</html>
