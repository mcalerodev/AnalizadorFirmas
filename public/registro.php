<?php
session_start();

require_once __DIR__ . '/../src/Database/Conexion.php';

$error  = null;
$exito  = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo  = trim($_POST['correo']   ?? '');
    $clave   = trim($_POST['clave']    ?? '');
    $repetir = trim($_POST['repetir']  ?? '');

    if (empty($correo) || empty($clave) || empty($repetir)) {
        $error = "Completa todos los campos.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Correo electrónico no válido.";
    } elseif (strlen($clave) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } elseif ($clave !== $repetir) {
        $error = "Las contraseñas no coinciden.";
    } else {
        try {
            $conexion = Conexion::getInstance();

            // Verificar que el correo no esté ya registrado
            $stmt = $conexion->prepare("SELECT id FROM usuario WHERE correo = ? LIMIT 1");
            $stmt->execute([$correo]);
            if ($stmt->fetch()) {
                $error = "Ese correo ya está registrado.";
            } else {
                // Guardar con hash seguro
                $hash = password_hash($clave, PASSWORD_DEFAULT);
                $stmt = $conexion->prepare("INSERT INTO usuario (correo, clave) VALUES (?, ?)");
                $stmt->execute([$correo, $hash]);
                $exito = "Cuenta creada correctamente. <a href='login.php' class='link-iniciar-sesion'>Inicia sesión</a>";
            }
        } catch (Exception $e) {
            $error = "Error del servidor: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro — AnalizadorFirmas</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <script src="assets/js/theme-switcher.js"></script>
</head>

<body>
    <div class="login">
        <h1>Crear cuenta</h1>

        <?php if ($error): ?>
            <div class="error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($exito): ?>
            <div class="ok">✅ <?= ($exito) ?></div>
        <?php endif; ?>
        <form method="POST" aria-label="Formulario de registro" novalidate>
            <label for="reg-correo">Correo electrónico</label>
            <input type="email" id="reg-correo" name="correo"
                placeholder="tucorreo@ejemplo.com"
                autocomplete="email"
                aria-required="true"
                required>

            <label for="reg-clave">Contraseña <small>(mín. 6 caracteres)</small></label>
            <input type="password" id="reg-clave" name="clave"
                placeholder="Mínimo 6 caracteres"
                autocomplete="new-password"
                minlength="6"
                aria-required="true"
                aria-describedby="hint-clave"
                required>
            <small id="hint-clave" style="color:#9CA3AF;display:block;margin-bottom:6px">Al menos 6 caracteres.</small>

            <label for="reg-repetir">Repetir contraseña</label>
            <input type="password" id="reg-repetir" name="repetir"
                placeholder="Repite tu contraseña"
                autocomplete="new-password"
                minlength="6"
                aria-required="true"
                required>

            <button type="submit" aria-label="Crear cuenta">Registrarse</button>
        </form>
        <div class="link">
            ¿Ya tienes cuenta? <a class="link-iniciar-sesion" href="login.php">Inicia sesión</a>
        </div>
    </div>
</body>

</html>