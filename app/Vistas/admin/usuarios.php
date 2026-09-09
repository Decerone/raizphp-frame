<?php if ($mensaje): ?><div class="alerta alerta-exito animar-entrada"><?= htmlspecialchars($mensaje) ?></div><?php endif; ?>
<h3>Usuarios</h3>
<div class="tarjeta mt-2">
    <table class="tabla">
        <thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Edad</th><th>Acciones</th></tr></thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?= $u->id ?></td>
                <td><?= htmlspecialchars($u->nombreCompleto()) ?></td>
                <td><?= htmlspecialchars($u->email) ?></td>
                <td><span class="resaltar <?= $u->rol==='admin'?'resaltar-primario':'resaltar-secundario' ?>"><?= $u->rol ?></span></td>
                <td><?= $u->edad ?></td>
                <td>
                    <div class="flex gap-1">
                        <a href="<?= $adminBase ?>/usuarios/editar?id=<?= $u->id ?>" class="boton boton-secundario texto-sm">Editar</a>
                        <form method="POST" action="<?= $adminBase ?>/usuarios/eliminar" style="display:inline;">
                            <input type="hidden" name="_token" value="<?= \App\Nucleo\HelperCsrf::obtenerToken() ?>">
                            <input type="hidden" name="id" value="<?= $u->id ?>">
                            <button type="submit" class="boton boton-error texto-sm" onclick="return confirm('¿Eliminar?')">Eliminar</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
