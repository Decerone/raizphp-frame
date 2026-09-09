<div class="tarjetas-estadisticas">
    <div class="tarjeta-estadistica"><div class="tarjeta-icono">👥</div><div class="tarjeta-datos"><h3><?= $totalUsuarios ?></h3><p>Usuarios</p></div></div>
    <div class="tarjeta-estadistica"><div class="tarjeta-icono">👑</div><div class="tarjeta-datos"><h3><?= $totalAdmins ?></h3><p>Admins</p></div></div>
    <div class="tarjeta-estadistica"><div class="tarjeta-icono">⚡</div><div class="tarjeta-datos"><h3><?= $cacheActivo?'Activo':'Inactivo' ?></h3><p>Caché</p></div></div>
    <div class="tarjeta-estadistica"><div class="tarjeta-icono">📁</div><div class="tarjeta-datos"><h3><?= $archivosCache ?></h3><p>Archivos</p></div></div>
</div>
<div class="tarjeta mt-2">
    <h3>Acciones rápidas</h3>
    <div class="flex gap-2 mt-2">
        <a href="<?= $adminBase ?>/usuarios" class="boton">Usuarios</a>
        <a href="<?= $adminBase ?>/cache" class="boton boton-secundario">Caché</a>
        <form method="POST" action="<?= $adminBase ?>/cache/limpiar" style="display:inline;">
            <input type="hidden" name="_token" value="<?= \App\Nucleo\HelperCsrf::obtenerToken() ?>">
            <button type="submit" class="boton boton-esquema">Limpiar</button>
        </form>
    </div>
</div>
