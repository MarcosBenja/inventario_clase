<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
verificarAdmin();

$pageTitle    = 'Editar Usuario';
$activeModule = 'usuarios';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . BASE_URL . '/modules/usuarios/index.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
$stmt->execute([$id]);
$usr = $stmt->fetch();
if (!$usr) { header('Location: ' . BASE_URL . '/modules/usuarios/index.php'); exit; }

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $usuario         = trim($_POST['usuario'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $rol             = $_POST['rol'] ?? 'operador';
    $estado          = isset($_POST['estado']) ? 1 : 0;
    $password        = $_POST['password'] ?? '';
    $password_conf   = $_POST['password_conf'] ?? '';

    if ($nombre_completo === '') $errores[] = 'El nombre completo es requerido.';
    if ($usuario === '')         $errores[] = 'El nombre de usuario es requerido.';
    if ($email === '')           $errores[] = 'El email es requerido.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'El email no es válido.';
    if ($password !== '' && $password !== $password_conf) $errores[] = 'Las contraseñas no coinciden.';
    if (!in_array($rol, ['admin', 'operador'])) $errores[] = 'Rol no válido.';

    if ($usuario !== '') {
        $chk = $pdo->prepare('SELECT id FROM usuarios WHERE usuario = ? AND id <> ?');
        $chk->execute([$usuario, $id]);
        if ($chk->fetch()) $errores[] = 'El nombre de usuario ya está en uso.';
    }
    if ($email !== '') {
        $chk = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? AND id <> ?');
        $chk->execute([$email, $id]);
        if ($chk->fetch()) $errores[] = 'El email ya está registrado.';
    }

    if (empty($errores)) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE usuarios SET nombre_completo = ?, usuario = ?, email = ?, password = ?, rol = ?, estado = ? WHERE id = ?')
                ->execute([$nombre_completo, $usuario, $email, $hash, $rol, $estado, $id]);
        } else {
            $pdo->prepare('UPDATE usuarios SET nombre_completo = ?, usuario = ?, email = ?, rol = ?, estado = ? WHERE id = ?')
                ->execute([$nombre_completo, $usuario, $email, $rol, $estado, $id]);
        }
        header('Location: ' . BASE_URL . '/modules/usuarios/index.php?msg=editado');
        exit;
    }
    $usr['nombre_completo'] = $nombre_completo;
    $usr['usuario'] = $usuario; $usr['email'] = $email;
    $usr['rol'] = $rol; $usr['estado'] = $estado;
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Editar Usuario</div>
        <div class="page-subtitle">ID #<?= $id ?> — Deje la contraseña en blanco para mantener la actual.</div>
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
        <form method="POST" id="form-editar-usuario">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="usr-nombre">Nombre Completo *</label>
                    <input type="text" class="form-control" id="usr-nombre" name="nombre_completo"
                           value="<?= htmlspecialchars($usr['nombre_completo']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="usr-usuario">Nombre de Usuario *</label>
                    <input type="text" class="form-control" id="usr-usuario" name="usuario"
                           value="<?= htmlspecialchars($usr['usuario']) ?>" required autocomplete="off">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="usr-email">Email *</label>
                    <input type="email" class="form-control" id="usr-email" name="email"
                           value="<?= htmlspecialchars($usr['email']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="usr-rol">Rol *</label>
                    <select class="form-select" id="usr-rol" name="rol">
                        <option value="operador" <?= $usr['rol'] === 'operador' ? 'selected' : '' ?>>Operador</option>
                        <option value="admin"    <?= $usr['rol'] === 'admin'    ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="estado" id="usr-estado" <?= $usr['estado'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="usr-estado">Activo</label>
                    </div>
                </div>
                <div style="border-top:1px solid var(--border-color);margin:.5rem 0;grid-column:1/-1"></div>
                <div class="col-12">
                    <p style="font-size:.8125rem;color:var(--text-secondary);margin-bottom:.75rem">
                        <i class="bi bi-info-circle me-1"></i>
                        Deje los campos de contraseña en blanco para conservar la actual.
                    </p>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="usr-password">Nueva Contraseña</label>
                    <input type="password" class="form-control" id="usr-password" name="password" autocomplete="new-password">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="usr-password-conf">Confirmar Contraseña</label>
                    <input type="password" class="form-control" id="usr-password-conf" name="password_conf" autocomplete="new-password">
                </div>
                <div class="col-12 pt-1">
                    <button type="submit" class="btn btn-primary" id="btn-actualizar-usuario">
                        <i class="bi bi-check-lg me-2"></i>Actualizar Usuario
                    </button>
                    <a href="<?= BASE_URL ?>/modules/usuarios/index.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
