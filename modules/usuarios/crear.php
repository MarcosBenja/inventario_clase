<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
verificarAdmin();

$pageTitle    = 'Nuevo Usuario';
$activeModule = 'usuarios';

$errores = [];
$datos   = ['nombre_completo' => '', 'usuario' => '', 'email' => '', 'rol' => 'operador', 'estado' => 1];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['nombre_completo'] = trim($_POST['nombre_completo'] ?? '');
    $datos['usuario']         = trim($_POST['usuario'] ?? '');
    $datos['email']           = trim($_POST['email'] ?? '');
    $datos['rol']             = $_POST['rol'] ?? 'operador';
    $datos['estado']          = isset($_POST['estado']) ? 1 : 0;
    $password                 = $_POST['password'] ?? '';
    $password_conf            = $_POST['password_conf'] ?? '';

    if ($datos['nombre_completo'] === '') $errores[] = 'El nombre completo es requerido.';
    if ($datos['usuario'] === '')         $errores[] = 'El nombre de usuario es requerido.';
    if ($datos['email'] === '')           $errores[] = 'El email es requerido.';
    if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) $errores[] = 'El email no es válido.';
    if ($password === '')                 $errores[] = 'La contraseña es requerida.';
    if ($password !== $password_conf)     $errores[] = 'Las contraseñas no coinciden.';
    if (!in_array($datos['rol'], ['admin', 'operador'])) $errores[] = 'Rol no válido.';

    if ($datos['usuario'] !== '') {
        $chk = $pdo->prepare('SELECT id FROM usuarios WHERE usuario = ?');
        $chk->execute([$datos['usuario']]);
        if ($chk->fetch()) $errores[] = 'El nombre de usuario ya está en uso.';
    }
    if ($datos['email'] !== '') {
        $chk = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
        $chk->execute([$datos['email']]);
        if ($chk->fetch()) $errores[] = 'El email ya está registrado.';
    }

    if (empty($errores)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare('INSERT INTO usuarios (nombre_completo, usuario, email, password, rol, estado) VALUES (?, ?, ?, ?, ?, ?)')
            ->execute([$datos['nombre_completo'], $datos['usuario'], $datos['email'], $hash, $datos['rol'], $datos['estado']]);
        header('Location: ' . BASE_URL . '/modules/usuarios/index.php?msg=creado');
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Nuevo Usuario</div>
        <div class="page-subtitle">Complete los datos del nuevo usuario</div>
    </div>
    <a href="<?= BASE_URL ?>/modules/usuarios/index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver
    </a>
</div>

<?php if (!empty($errores)): ?>
<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= implode('<br>', array_map('htmlspecialchars', $errores)) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body" style="padding:1.75rem">
        <form method="POST" id="form-crear-usuario">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="usr-nombre">Nombre Completo *</label>
                    <input type="text" class="form-control" id="usr-nombre" name="nombre_completo"
                           value="<?= htmlspecialchars($datos['nombre_completo']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="usr-usuario">Nombre de Usuario *</label>
                    <input type="text" class="form-control" id="usr-usuario" name="usuario"
                           value="<?= htmlspecialchars($datos['usuario']) ?>" required autocomplete="off">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="usr-email">Email *</label>
                    <input type="email" class="form-control" id="usr-email" name="email"
                           value="<?= htmlspecialchars($datos['email']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="usr-rol">Rol *</label>
                    <select class="form-select" id="usr-rol" name="rol" required>
                        <option value="operador" <?= $datos['rol'] === 'operador' ? 'selected' : '' ?>>Operador</option>
                        <option value="admin"    <?= $datos['rol'] === 'admin'    ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="estado" id="usr-estado" <?= $datos['estado'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="usr-estado">Activo</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="usr-password">Contraseña *</label>
                    <input type="password" class="form-control" id="usr-password" name="password" required autocomplete="new-password">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="usr-password-conf">Confirmar Contraseña *</label>
                    <input type="password" class="form-control" id="usr-password-conf" name="password_conf" required autocomplete="new-password">
                </div>
                <div class="col-12 pt-1">
                    <button type="submit" class="btn btn-primary" id="btn-guardar-usuario">
                        <i class="bi bi-check-lg me-2"></i>Guardar Usuario
                    </button>
                    <a href="<?= BASE_URL ?>/modules/usuarios/index.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
