<?php
session_start();
require_once __DIR__ . '/../src/Database/Conexion.php';

// Redirigir si ya está logueado
if (!empty($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $clave  = $_POST['clave'] ?? '';

    $conexion = Conexion::getInstance();

    $sql = "SELECT * FROM usuario WHERE correo = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$correo]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($clave, $usuario['clave'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['correo'] = $usuario['correo'];

        header("Location: index.php");
        exit;
    } else {
        $error = "Credenciales incorrectas";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — AnalizadorFirmas</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <script src="assets/js/theme-switcher.js"></script>
</head>

<body>
    <div class="login" role="main">
        <form method="POST" aria-label="Formulario de inicio de sesión" novalidate>
            <h1>Iniciar sesión</h1>

            <?php if ($error): ?>
                <div class="error" role="alert" aria-live="assertive">
                    ❌ <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <label for="correo">Correo electrónico</label>
            <input type="email" id="correo" name="correo"
                placeholder="tucorreo@ejemplo.com"
                autocomplete="email"
                aria-required="true"
                required>

            <label for="clave">Contraseña</label>
            <input type="password" id="clave" name="clave"
                placeholder="Tu contraseña"
                autocomplete="current-password"
                aria-required="true"
                required>

            <button type="submit" aria-label="Iniciar sesión">Ingresar</button>
        </form>

        <div class="link">
            ¿No tienes cuenta? <a class="link-iniciar-sesion" href="registro.php">Regístrate</a>
        </div>
        <div class="link">
            <a class="link-iniciar-sesion" href="index.php">← Volver al inicio</a>
        </div>
    </div>
</body>

</html>