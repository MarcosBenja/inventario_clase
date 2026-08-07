<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
verificarAdmin();

$pageTitle    = 'Categorías';
$activeModule = 'categorias';

$buscar = trim($_GET['buscar'] ?? '');
$estado = $_GET['estado'] ?? '';

$sql    = 'SELECT c.*, COUNT(p.id) AS total_productos FROM categorias c LEFT JOIN productos p ON c.id = p.id_categoria WHERE 1=1';
$params = [];

if ($buscar !== '') {
    $sql .= ' AND c.nombre LIKE ?';
    $params[] = "%$buscar%";
}
if ($estado !== '') {
    $sql .= ' AND c.estado = ?';
    $params[] = $estado;
}
$sql .= ' GROUP BY c.id ORDER BY c.nombre';

$stmt      = $pdo->prepare($sql);
$stmt->execute($params);
$categorias = $stmt->fetchAll();

$msg = $_GET['msg'] ?? '';

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Categorías</div>
        <div class="page-subtitle"><?= count($categorias) ?> registros encontrados</div>
    </div>
    <a href="<?= BASE_URL ?>/modules/categorias/crear.php" class="btn btn-primary" id="btn-nueva-categoria">
        <i class="bi bi-plus-lg me-2"></i>Nueva Categoría
    </a>
</div>

<?php if ($msg === 'creado'): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i>Categoría creada exitosamente.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php elseif ($msg === 'editado'): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i>Categoría actualizada exitosamente.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php elseif ($msg === 'eliminado'): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i>Categoría eliminada exitosamente.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php elseif ($msg === 'con_productos'): ?>
<div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle-fill me-2"></i>No se puede eliminar: la categoría tiene productos asociados.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php elseif ($msg === 'error'): ?>
<div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle-fill me-2"></i>Ocurrió un error. Intente nuevamente.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card">
    <div class="card-body" style="padding:1.25rem">
        <form method="GET" class="row g-2 mb-3" id="form-filtro-categorias">
            <div class="col-md-7">
                <input type="text" name="buscar" class="form-control" id="input-buscar-cat" placeholder="Buscar por nombre..." value="<?= htmlspecialchars($buscar) ?>">
            </div>
            <div class="col-md-3">
                <select name="estado" class="form-select" id="sel-estado-cat">
                    <option value="">Todos los estados</option>
                    <option value="1" <?= $estado === '1' ? 'selected' : '' ?>>Activos</option>
                    <option value="0" <?= $estado === '0' ? 'selected' : '' ?>>Inactivos</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill" id="btn-buscar-cat"><i class="bi bi-search me-1"></i>Buscar</button>
                <a href="<?= BASE_URL ?>/modules/categorias/index.php" class="btn btn-outline-secondary" id="btn-limpiar-cat"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table" id="tabla-categorias">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Productos</th>
                        <th>Estado</th>
                        <th>Creado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categorias)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No se encontraron registros.</td></tr>
                    <?php else: ?>
                    <?php foreach ($categorias as $cat): ?>
                    <tr>
                        <td style="color:var(--text-secondary)"><?= $cat['id'] ?></td>
                        <td><strong><?= htmlspecialchars($cat['nombre']) ?></strong></td>
                        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($cat['descripcion'] ?? '—') ?></td>
                        <td><?= $cat['total_productos'] ?></td>
                        <td>
                            <?php if ($cat['estado']): ?>
                            <span class="bstatus b-activo">Activo</span>
                            <?php else: ?>
                            <span class="bstatus b-inactivo">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($cat['created_at'])) ?></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="<?= BASE_URL ?>/modules/categorias/editar.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-primary" id="btn-editar-cat-<?= $cat['id'] ?>" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="<?= BASE_URL ?>/modules/categorias/eliminar.php" onsubmit="return confirm('¿Seguro que desea eliminar esta categoría?')">
                                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" id="btn-eliminar-cat-<?= $cat['id'] ?>" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
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
