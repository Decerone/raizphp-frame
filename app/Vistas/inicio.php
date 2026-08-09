<h1><?= htmlspecialchars($titulo) ?></h1>
<div class="fila mt-2">
<?php foreach ($usuarios as $usuario): ?>
<div class="columna columna-4"><div class="tarjeta"><h3><?= htmlspecialchars($usuario->nombreCompleto()) ?><span class="resaltar <?= $usuario->rol==='admin'?'resaltar-primario':'resaltar-secundario' ?> texto-sm"><?= $usuario->rol ?></span></h3><p><?= htmlspecialchars($usuario->email) ?></p></div></div>
<?php endforeach; ?>
</div>
