<?php
$dirRaiz = dirname(__DIR__);
require_once $dirRaiz . '/app/Nucleo/UrlHelper.php';
require_once $dirRaiz . '/app/Nucleo/helpers.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌱 Documentación - RaízPHP v2.1</title>
    <style>:root{--primario:#2563eb;--secundario:#7c3aed;--fondo:#f8fafc;--texto:#1e293b;--borde:#e2e8f0}*{box-sizing:border-box;margin:0;padding:0}body{font-family:system-ui,sans-serif;background:var(--fondo);color:var(--texto);line-height:1.8}.sidebar{position:fixed;top:0;left:0;bottom:0;width:260px;background:linear-gradient(180deg,#1e1b4b 0%,#312e81 100%);color:white;overflow-y:auto;padding:1.5rem;z-index:100}.sidebar h2{font-size:1.3rem;margin-bottom:1rem;color:#a78bfa}.sidebar a{display:block;color:rgba(255,255,255,0.8);text-decoration:none;padding:.4rem 0;font-size:.9rem}.sidebar a:hover{color:white}.sidebar .btn-volver{display:inline-block;padding:.5rem 1rem;background:rgba(255,255,255,0.15);color:white;border-radius:.5rem;text-decoration:none;font-weight:600;font-size:.85rem;margin-bottom:1.5rem}.sidebar .btn-volver:hover{background:rgba(255,255,255,0.25)}.contenido{margin-left:260px;padding:3rem;max-width:1000px}h1{color:var(--primario);font-size:2.2rem;border-bottom:3px solid var(--primario);padding-bottom:.8rem;margin-bottom:2rem}h2{color:var(--secundario);margin-top:3rem;margin-bottom:1rem;font-size:1.6rem;border-bottom:1px solid var(--borde);padding-bottom:.5rem}pre{background:#1e293b;color:#e2e8f0;padding:1.5rem;border-radius:.5rem;overflow-x:auto;font-size:.85rem;margin-bottom:1rem}code{background:#f1f5f9;padding:.2rem .5rem;border-radius:.25rem;font-size:.9rem;color:#dc2626}pre code{background:none;padding:0;color:#e2e8f0}table{width:100%;border-collapse:collapse;margin:1.5rem 0;background:white;border-radius:.5rem;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.1)}th,td{padding:.75rem 1rem;text-align:left;border-bottom:1px solid var(--borde)}th{background:#f1f5f9;font-weight:600;color:#475569}tr:hover td{background:#f8fafc}.nota{background:#fef3c7;border:1px solid #f59e0b;color:#92400e;padding:1rem;border-radius:.5rem;margin:1rem 0}.exito{background:#dcfce7;border:1px solid #86efac;color:#166534;padding:1rem;border-radius:.5rem;margin:1rem 0}.nuevo{background:#ede9fe;border:1px solid #c4b5fd;color:#5b21b6;padding:1rem;border-radius:.5rem;margin:1rem 0}.badge-nuevo{display:inline-block;background:#7c3aed;color:white;padding:.15rem .5rem;border-radius:.25rem;font-size:.75rem;font-weight:700;margin-left:.3rem}@media(max-width:768px){.sidebar{position:relative;width:100%;height:auto}.contenido{margin-left:0;padding:1.5rem}}</style>
</head>
<body>
<nav class="sidebar">
    <a href="<?= url() ?>" class="btn-volver">← Volver a la aplicación</a>
    <h2>🌱 RaízPHP</h2><p style="font-size:.75rem;color:rgba(255,255,255,0.5)">v2.1 - Multiplataforma</p>
    <a href="#1">1. ¿Qué es RaízPHP?</a><a href="#2">2. Requisitos</a><a href="#3">3. Estructura</a>
    <a href="#4">4. Instalación</a><a href="#5">5. Configuración</a><a href="#6">6. MVC</a>
    <a href="#7">7. Enrutamiento <span class="badge-nuevo">NUEVO</span></a><a href="#8">8. Controladores</a>
    <a href="#9">9. Modelos/ORM</a><a href="#10">10. Vistas/URLs <span class="badge-nuevo">NUEVO</span></a>
    <a href="#11">11. Framework CSS</a><a href="#12">12. Middleware</a><a href="#13">13. Auth</a>
    <a href="#16">16. API REST</a><a href="#17">17. Panel Admin</a><a href="#18">18. Caché</a>
    <a href="#20">20. CLI <span class="badge-nuevo">NUEVO</span></a><a href="#22">22. Errores <span class="badge-nuevo">NUEVO</span></a>
    <a href="<?= asset('tutorial.html') ?>">📖 Tutorial</a>
    <br><a href="<?= url() ?>" class="btn-volver">← Volver</a>
</nav>
<main class="contenido">
<h1>🌱 RaízPHP v2.1</h1>
<p><strong>Multiplataforma:</strong> Windows · macOS · Linux | <strong>0 dependencias</strong></p>
<div class="nuevo">🆕 <strong>v2.1:</strong> Rutas por módulos · URLs dinámicas · Errores personalizados · CLI 30 comandos</div>

<h2 id="1">1. ¿Qué es RaízPHP?</h2>
<p>Framework MVC PHP 8.0+ con ORM, API REST, Panel Admin, Caché, Middleware, Seguridad 10/10, CLI completo y 0 dependencias.</p>

<h2 id="2">2. Requisitos</h2>
<table><tr><th>Componente</th><th>Requisito</th></tr><tr><td>PHP</td><td>8.0+</td></tr><tr><td>Extensiones</td><td>PDO, PDO_MySQL, OpenSSL, mbstring, JSON</td></tr><tr><td>BD</td><td>MySQL 5.7+ / PostgreSQL 10+</td></tr><tr><td>Servidor</td><td>Apache 2.4+ (mod_rewrite)</td></tr></table>

<h2 id="3">3. Estructura</h2>
<pre><code>proyecto/
├── .htaccess
└── raizphp/
    ├── raizphp              ← CLI
    ├── app/
    │   ├── Controladores/
    │   ├── Middleware/
    │   ├── Modelos/
    │   ├── rutas/            ← NUEVO
    │   ├── Vistas/errores/   ← NUEVO
    │   └── Nucleo/
    ├── config/
    └── public/</code></pre>

<h2 id="4">4. Instalación</h2>
<p>Accede a <code>public/instalar.php</code> y sigue los pasos.</p>

<h2 id="7">7. Enrutamiento <span class="badge-nuevo">NUEVO</span></h2>
<p>Rutas en <code>app/rutas/</code>. Cada archivo se carga automáticamente:</p>
<pre><code>// app/rutas/blog.php
&lt;?php
use App\Nucleo\Enrutador;
return function (Enrutador $enrutador): void {
    $enrutador->agregarRuta('GET', '/blog', 'BlogControlador@index');
};</code></pre>

<h2 id="10">10. Vistas y URLs <span class="badge-nuevo">NUEVO</span></h2>
<pre><code>&lt;a href="&lt;?= url() ?&gt;"&gt;Inicio&lt;/a&gt;
&lt;a href="&lt;?= url('login') ?&gt;"&gt;Login&lt;/a&gt;
&lt;link href="&lt;?= asset('css/raiz.css') ?&gt;"&gt;
&lt;?php redirigir('admin') ?&gt;</code></pre>

<h2 id="20">20. CLI - Interfaz de Línea de Comandos <span class="badge-nuevo">NUEVO</span></h2>

<div class="nuevo">
    🆤 <strong>30 comandos disponibles</strong> para crear controladores, modelos, rutas, migraciones, seeders, gestionar caché, usuarios, base de datos y más.
</div>

<h3>20.1 Activar el CLI</h3>
<ol>
    <li>Navega a la carpeta <code>raizphp/</code> del proyecto:
        <pre><code>cd /var/www/pr1/raizphp</code></pre>
    </li>
    <li>Da permisos de ejecución al archivo (solo primera vez):
        <pre><code>chmod +x raizphp</code></pre>
        <div class="nota">En Windows no necesitas este paso. Usa: <code>php raizphp ayuda</code></div>
    </li>
    <li>Ejecuta el CLI:
        <pre><code>php raizphp ayuda</code></pre>
    </li>
</ol>

<h3>20.2 Ubicación del archivo</h3>
<pre><code>mi-proyecto/
└── raizphp/
    └── raizphp    ← El archivo CLI está aquí</code></pre>
<p>Siempre debes ejecutar los comandos <strong>desde dentro de la carpeta <code>raizphp/</code></strong>.</p>

<h3>20.3 Comandos disponibles</h3>

<h4>Crear archivos</h4>
<pre><code>php raizphp crear:controlador Nombre    → Crea controlador + vista
php raizphp crear:modelo Nombre         → Crea modelo ORM
php raizphp crear:vista nombre          → Crea archivo de vista
php raizphp crear:middleware Nombre     → Crea middleware
php raizphp crear:ruta nombre           → Crea archivo en app/rutas/
php raizphp crear:api Nombre            → Crea controlador API
php raizphp crear:admin Nombre          → Crea controlador admin
php raizphp crear:migracion nombre      → Crea archivo de migración
php raizphp crear:seeder nombre         → Crea archivo seeder</code></pre>

<h4>Base de datos</h4>
<pre><code>php raizphp migrar                      → Ejecuta migraciones pendientes
php raizphp revertir                    → Revierte última migración
php raizphp migrar:estado               → Muestra estado de migraciones
php raizphp migrar:fuerza               → Fuerza migraciones
php raizphp seed                        → Ejecuta seeders
php raizphp db:respaldar                → Genera backup SQL
php raizphp db:restaurar archivo.sql    → Restaura backup</code></pre>

<h4>Caché y logs</h4>
<pre><code>php raizphp cache:limpiar               → Limpia toda la caché
php raizphp cache:estado                → Muestra estado de caché
php raizphp log:limpiar                 → Limpia archivos de log
php raizphp log:ver [líneas]            → Ver últimas líneas del log</code></pre>

<h4>Listar información</h4>
<pre><code>php raizphp lista:rutas                 → Muestra todas las rutas
php raizphp lista:middlewares           → Lista middlewares
php raizphp lista:controladores         → Lista controladores</code></pre>

<h4>Usuarios y seguridad</h4>
<pre><code>php raizphp usuario:crear               → Crea usuario (interactivo)
php raizphp usuario:rol email rol       → Cambia rol (admin/usuario)
php raizphp token:generar email         → Genera API token
php raizphp clave:generar               → Genera clave de aplicación</code></pre>

<h4>Utilidades</h4>
<pre><code>php raizphp servir [puerto]             → Servidor de desarrollo
php raizphp proyecto:info               → Info del proyecto
php raizphp proyecto:entorno [e]        → Ver/cambiar entorno
php raizphp ayuda                       → Mostrar esta ayuda</code></pre>

<h3>20.4 Ejemplos prácticos</h3>

<div class="exito">
    <strong>Crear un módulo completo:</strong>
    <pre><code>php raizphp crear:controlador Producto
php raizphp crear:modelo Producto
php raizphp crear:ruta productos
php raizphp crear:migracion crear_tabla_productos
php raizphp crear:seeder Producto
php raizphp migrar
php raizphp seed</code></pre>
</div>

<h3>20.5 Solución de problemas</h3>
<table>
    <tr><th>Error</th><th>Solución</th></tr>
    <tr><td><code>Permission denied</code></td><td><code>chmod +x raizphp</code></td></tr>
    <tr><td><code>Command not found</code></td><td>Usa <code>php raizphp</code> en lugar de <code>./raizphp</code></td></tr>
    <tr><td><code>No such file</code></td><td>Asegúrate de estar en la carpeta <code>raizphp/</code></td></tr>
    <tr><td>Windows no reconoce el comando</td><td>Usa <code>php raizphp ayuda</code></td></tr>
</table>

<h2 id="22">22. Errores <span class="badge-nuevo">NUEVO</span></h2>
<p>Edita <code>app/Vistas/errores/404.php</code> y <code>500.php</code>. Cambios inmediatos (sin caché).</p>

<hr><blockquote>🌱 RaízPHP v2.1 - Framework PHP en español, multiplataforma, sin dependencias.</blockquote>
</main></body></html>
