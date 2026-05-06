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

    if ($usuario && $usuario['clave'] === $clave) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['correo'] = $usuario['correo'];

        header("Location: index.php");
        exit;
    } else {
        $error = "Credenciales incorrectas";
    }
}
?>

<form method="POST">
    <h2>Login</h2>
    <input type="email" name="correo" placeholder="Correo" required><br><br>
    <input type="password" name="clave" placeholder="Clave" required><br><br>
    <button type="submit">Ingresar</button>

    <?php if ($error): ?>
        <p style="color:red;"><?= $error ?></p>
    <?php endif; ?>
</form>