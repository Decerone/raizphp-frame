<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?? 'Panel' ?> - RaízPHP</title>
    <link rel="stylesheet" href="<?= $urlBase ?>/estilos/raiz.css">
    <link rel="stylesheet" href="<?= $urlBase ?>/estilos/admin.css">
</head>
<body>
    <div class="admin-contenedor">
        <aside class="admin-sidebar">
            <div class="admin-marca">
                <a href="<?= $adminBase ?>">🌱 RaízPHP</a>
                <small>Panel Admin</small>
            </div>
            <nav class="admin-menu">
                <a href="<?= $adminBase ?>" class="admin-menu-item <?= ($rutaActual===$adminBase)?'activo':'' ?>">📊 Dashboard</a>
                <a href="<?= $adminBase ?>/usuarios" class="admin-menu-item <?= strpos($rutaActual,'/usuarios')!==false?'activo':'' ?>">👥 Usuarios</a>
                <a href="<?= $adminBase ?>/cache" class="admin-menu-item <?= strpos($rutaActual,'/cache')!==false?'activo':'' ?>">⚡ Caché</a>
                <hr>
                <a href="<?= $urlBase ?>/" class="admin-menu-item">← Volver</a>
            </nav>
        </aside>
        <main class="admin-contenido">
            <header class="admin-header">
                <h2><?= $titulo ?? 'Dashboard' ?></h2>
                <div class="admin-usuario">
                    <span>👤 <?= htmlspecialchars($usuarioAdmin['nombre']??'Admin') ?></span>
                    <form method="POST" action="<?= $urlBase ?>/logout" style="display:inline;">
                        <input type="hidden" name="_token" value="<?= \App\Nucleo\HelperCsrf::obtenerToken() ?>">
                        <button type="submit" class="boton boton-esquema texto-sm">Salir</button>
                    </form>
                </div>
            </header>
            <div class="admin-pagina"><?= $contenido ?></div>
        </main>
    </div>
</body>
</html>
