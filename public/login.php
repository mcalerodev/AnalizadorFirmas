<?php
session_start();
require_once __DIR__ . '/../src/Database/Conexion.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'];
    $clave  = $_POST['clave'];

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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AnalizadorFirmas</title>
    <link rel="stylesheet" href="assets/css/theme.css">
</head>

<body>
    <div class="login">
        <form method="POST">
            <h1>Login</h1>
            <input type="email" name="correo" placeholder="Correo" required>
            <input type="password" name="clave" placeholder="Clave" required>
            <button class="button btn-upload " type="submit">Ingresar</button>

            <?php if ($error): ?>
            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

        </form>
    </div>
</body>

</html>