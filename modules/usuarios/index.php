<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
verificarAdmin();

$pageTitle    = 'Usuarios';
$activeModule = 'usuarios';

$buscar = trim($_GET['buscar'] ?? '');
$rol    = $_GET['rol'] ?? '';
$estado = $_GET['estado'] ?? '';

$sql    = 'SELECT * FROM usuarios WHERE 1=1';
$params = [];

if ($buscar !== '') {
    $sql .= ' AND (nombre_completo LIKE ? OR usuario LIKE ? OR email LIKE ?)';
    $params[] = "%$buscar%"; $params[] = "%$buscar%"; $params[] = "%$buscar%";
}
if ($rol !== '')    { $sql .= ' AND rol = ?';    $params[] = $rol; }
if ($estado !== '') { $sql .= ' AND estado = ?'; $params[] = $estado; }
$sql .= ' ORDER BY nombre_completo';

$stmt    = $pdo->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll();

$msg = $_GET['msg'] ?? '';

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Usuarios</div>
        <div class="page-subtitle"><?= count($usuarios) ?> usuarios registrados</div>
    </div>
    <a href="<?= BASE_URL ?>/modules/usuarios/crear.php" class="btn btn-primary" id="btn-nuevo-usuario">
        <i class="bi bi-person-plus-fill me-2"></i>Nuevo Usuario
    </a>
</div>

<?php if ($msg === 'creado'): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i>Usuario creado exitosamente.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php elseif ($msg === 'editado'): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i>Usuario actualizado exitosamente.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php elseif ($msg === 'eliminado'): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i>Usuario eliminado exitosamente.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php elseif ($msg === 'no_autoeliminarse'): ?>
<div class="alert alert-warning alert-dismissible fade show"><i class="bi bi-exclamation-triangle-fill me-2"></i>No puedes eliminar tu propia cuenta.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php elseif ($msg === 'error'): ?>
<div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle-fill me-2"></i>Ocurrió un error. Intente nuevamente.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card">
    <div class="card-body" style="padding:1.25rem">
        <form method="GET" class="row g-2 mb-3" id="form-filtro-usuarios">
            <div class="col-md-5">
                <input type="text" name="buscar" class="form-control" id="input-buscar-usr" placeholder="Buscar por nombre, usuario o email..." value="<?= htmlspecialchars($buscar) ?>">
            </div>
            <div class="col-md-2">
                <select name="rol" class="form-select" id="sel-rol-usr">
                    <option value="">Todos los roles</option>
                    <option value="admin"    <?= $rol === 'admin'    ? 'selected' : '' ?>>Administrador</option>
                    <option value="operador" <?= $rol === 'operador' ? 'selected' : '' ?>>Operador</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="estado" class="form-select" id="sel-estado-usr">
                    <option value="">Todos</option>
                    <option value="1" <?= $estado === '1' ? 'selected' : '' ?>>Activos</option>
                    <option value="0" <?= $estado === '0' ? 'selected' : '' ?>>Inactivos</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill" id="btn-buscar-usr"><i class="bi bi-search me-1"></i>Buscar</button>
                <a href="<?= BASE_URL ?>/modules/usuarios/index.php" class="btn btn-outline-secondary" id="btn-limpiar-usr"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table" id="tabla-usuarios">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre Completo</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Registrado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">No se encontraron usuarios.</td></tr>
                    <?php else: ?>
                    <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td style="color:var(--text-secondary)"><?= $u['id'] ?></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:.625rem">
                                <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#818cf8);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:700;flex-shrink:0">
                                    <?= strtoupper(mb_substr($u['nombre_completo'], 0, 1)) ?>
                                </div>
                                <strong><?= htmlspecialchars($u['nombre_completo']) ?></strong>
                            </div>
                        </td>
                        <td><code style="font-size:.8rem"><?= htmlspecialchars($u['usuario']) ?></code></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <?php if ($u['rol'] === 'admin'): ?>
                            <span class="bstatus b-admin"><i class="bi bi-shield-check me-1"></i>Administrador</span>
                            <?php else: ?>
                            <span class="bstatus b-operador"><i class="bi bi-person me-1"></i>Operador</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u['estado']): ?>
                            <span class="bstatus b-activo">Activo</span>
                            <?php else: ?>
                            <span class="bstatus b-inactivo">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="<?= BASE_URL ?>/modules/usuarios/editar.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary" id="btn-editar-usr-<?= $u['id'] ?>" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($u['id'] !== $_SESSION['usuario_id']): ?>
                                <form method="POST" action="<?= BASE_URL ?>/modules/usuarios/eliminar.php" onsubmit="return confirm('¿Desea eliminar este usuario?')">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" id="btn-eliminar-usr-<?= $u['id'] ?>" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
