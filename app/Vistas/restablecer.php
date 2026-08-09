<h1><?= htmlspecialchars($titulo) ?></h1>
<?php if ($error): ?><div class="alerta alerta-error"><?= $error ?></div><?php endif; ?>
<?php if ($exito): ?><div class="alerta alerta-exito"><?= $exito ?></div><?php endif; ?>
<?php if ($token): ?><form method="POST" action="<?= $urlBase ?>/restablecer"><input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>"><div class="campo-formulario"><label class="etiqueta">Nueva contraseña</label><input type="password" name="password" class="entrada" required></div><?= \App\Nucleo\HelperCsrf::campoOculto() ?><button type="submit" class="boton">Cambiar</button></form><?php endif; ?>
