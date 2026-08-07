<?php
require_once __DIR__ . '/../../config/database.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/modules/dashboard/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario === '' || $password === '') {
        $error = 'Complete todos los campos.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE usuario = ? AND estado = 1');
        $stmt->execute([$usuario]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['usuario_id']      = $user['id'];
            $_SESSION['usuario_nombre']  = $user['nombre_completo'];
            $_SESSION['usuario_usuario'] = $user['usuario'];
            $_SESSION['usuario_rol']     = $user['rol'];
            $_SESSION['usuario_email']   = $user['email'];
            header('Location: ' . BASE_URL . '/modules/dashboard/index.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos, o cuenta inactiva.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Iniciar sesión en el Sistema de Inventario">
    <title>Iniciar Sesión | Sistema de Inventario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="login-body">

<div class="login-wrap">
    <div class="login-left">
        <div class="login-left-content">
            <i class="bi bi-boxes"></i>
            <h1>Sistema de Inventario</h1>
            <p>Plataforma de control de inventario para gestión eficiente de productos y movimientos.</p>
            <ul class="login-features">
                <li><i class="bi bi-check-circle-fill"></i> Control de entradas y salidas</li>
                <li><i class="bi bi-check-circle-fill"></i> Gestión de categorías y productos</li>
                <li><i class="bi bi-check-circle-fill"></i> Historial de movimientos</li>
                <li><i class="bi bi-check-circle-fill"></i> Alertas de stock bajo</li>
            </ul>
        </div>
    </div>

    <div class="login-right">
        <h2>Bienvenido</h2>
        <p>Ingresa tus credenciales para acceder al sistema</p>

        <?php if ($error): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="" id="form-login" novalidate>
            <div class="mb-3">
                <label class="form-label" for="login-usuario">Usuario</label>
                <div class="field-icon">
                    <i class="bi bi-person"></i>
                    <input type="text" class="form-control" id="login-usuario" name="usuario"
                           placeholder="Ingrese su usuario"
                           value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"
                           autocomplete="username" required>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label" for="login-password">Contraseña</label>
                <div class="field-icon">
                    <i class="bi bi-lock"></i>
                    <input type="password" class="form-control" id="login-password" name="password"
                           placeholder="Ingrese su contraseña"
                           autocomplete="current-password" required>
                </div>
            </div>
            <button type="submit" class="btn-login" id="btn-ingresar">
                <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar al Sistema
            </button>
        </form>
        <p class="text-center mt-4" style="font-size:.75rem;color:#94a3b8">
            Marcos Benjamin Morazan Rivas &mdash; Sistema de Inventario
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
