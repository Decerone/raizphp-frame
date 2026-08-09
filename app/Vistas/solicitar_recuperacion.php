<h1><?= htmlspecialchars($titulo) ?></h1>
<?php if ($error): ?><div class="alerta alerta-error"><?= $error ?></div><?php endif; ?>
<?php if ($exito): ?><div class="alerta alerta-exito"><?= $exito ?></div><?php endif; ?>
<form method="POST" action="<?= $urlBase ?>/recuperar"><div class="campo-formulario"><label class="etiqueta">Email</label><input type="email" name="email" class="entrada" required></div><?= \App\Nucleo\HelperCsrf::campoOculto() ?><button type="submit" class="boton">Enviar enlace</button></form>
<p class="mt-2"><a href="<?= $urlBase ?>/login">← Volver</a></p>
