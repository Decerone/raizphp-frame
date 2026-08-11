<h3>Caché</h3>

<?php if (isset($_GET['limpiado'])): ?>
<div class="alerta alerta-exito animar-entrada">✅ Caché limpiada correctamente.</div>
<?php endif; ?>

<div class="tarjetas-estadisticas mt-2">
    <div class="tarjeta-estadistica">
        <div class="tarjeta-icono">📁</div>
        <div class="tarjeta-datos">
            <h3><?= $estadisticas['archivos'] ?></h3>
            <p>Archivos</p>
        </div>
    </div>
    <div class="tarjeta-estadistica">
        <div class="tarjeta-icono">💾</div>
        <div class="tarjeta-datos">
            <h3><?= $estadisticas['tamano_kb'] ?> KB</h3>
            <p>Tamaño</p>
        </div>
    </div>
    <div class="tarjeta-estadistica">
        <div class="tarjeta-icono">⏱️</div>
        <div class="tarjeta-datos">
            <h3><?= round($estadisticas['antiguedad'] / 60, 1) ?> min</h3>
            <p>Antigüedad</p>
        </div>
    </div>
</div>

<div class="tarjeta mt-2">
    <div class="flex gap-2">
        <a href="<?= $adminBase ?>/cache/limpiar" class="boton boton-error">🧹 Limpiar Caché</a>
        <a href="<?= $adminBase ?>" class="boton boton-esquema">← Volver</a>
    </div>
</div>
