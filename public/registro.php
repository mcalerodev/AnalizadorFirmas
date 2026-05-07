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
                $exito = "Cuenta creada correctamente. <a href='login.php'>Inicia sesión</a>.";
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
    <title>Registro — AnalizadorFirmas</title>
    <style>
        body   { font-family: Arial, sans-serif; max-width: 400px; margin: 80px auto; }
        h2     { text-align: center; }
        input  { width: 100%; padding: 10px; margin: 8px 0; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 10px; background: #388e3c; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .error { background: #ffebee; border: 1px solid #f44336; padding: 10px; border-radius: 4px; margin-bottom: 12px; }
        .ok    { background: #e8f5e9; border: 1px solid #4caf50; padding: 10px; border-radius: 4px; margin-bottom: 12px; }
        .link  { text-align: center; margin-top: 14px; font-size: 0.9em; }
    </style>
</head>
<body>
    <h2>📝 Crear cuenta</h2>

    <?php if ($error): ?>
        <div class="error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($exito): ?>
        <div class="ok">✅ <?= $exito ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="email"    name="correo"   placeholder="Correo electrónico" required>
        <input type="password" name="clave"    placeholder="Contraseña (mín. 6 caracteres)" required>
        <input type="password" name="repetir"  placeholder="Repetir contraseña" required>
        <button type="submit">Registrarse</button>
    </form>

    <div class="link">
        ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
    </div>
</body>
</html>
