<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
verificarSesion();

$pageTitle    = 'Productos';
$activeModule = 'productos';

$buscar      = trim($_GET['buscar'] ?? '');
$id_categoria = $_GET['categoria'] ?? '';
$estado      = $_GET['estado'] ?? '';

$sql    = 'SELECT p.*, c.nombre AS cat_nombre FROM productos p JOIN categorias c ON p.id_categoria = c.id WHERE 1=1';
$params = [];

if ($buscar !== '') {
    $sql .= ' AND (p.nombre LIKE ? OR p.codigo LIKE ? OR p.descripcion LIKE ?)';
    $params[] = "%$buscar%"; $params[] = "%$buscar%"; $params[] = "%$buscar%";
}
if ($id_categoria !== '') { $sql .= ' AND p.id_categoria = ?'; $params[] = $id_categoria; }
if ($estado !== '')        { $sql .= ' AND p.estado = ?';        $params[] = $estado; }
$sql .= ' ORDER BY p.nombre';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

$categorias = $pdo->query('SELECT * FROM categorias ORDER BY nombre')->fetchAll();
$msg        = $_GET['msg'] ?? '';
$rol        = $_SESSION['usuario_rol'];

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Productos</div>
        <div class="page-subtitle"><?= count($productos) ?> registros encontrados</div>
    </div>
    <?php if ($rol === 'admin'): ?>
    <a href="<?= BASE_URL ?>/modules/productos/crear.php" class="btn btn-primary" id="btn-nuevo-producto">
        <i class="bi bi-plus-lg me-2"></i>Nuevo Producto
    </a>
    <?php endif; ?>
</div>

<?php if ($msg === 'creado'): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i>Producto creado exitosamente.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php elseif ($msg === 'editado'): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i>Producto actualizado exitosamente.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php elseif ($msg === 'eliminado'): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i>Producto eliminado exitosamente.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php elseif ($msg === 'con_movimientos'): ?>
<div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle-fill me-2"></i>No se puede eliminar: el producto tiene movimientos registrados.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php elseif ($msg === 'error'): ?>
<div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle-fill me-2"></i>Ocurrió un error. Intente nuevamente.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card">
    <div class="card-body" style="padding:1.25rem">
        <form method="GET" class="row g-2 mb-3" id="form-filtro-productos">
            <div class="col-md-4">
                <input type="text" name="buscar" class="form-control" id="input-buscar-prod" placeholder="Buscar código o nombre..." value="<?= htmlspecialchars($buscar) ?>">
            </div>
            <div class="col-md-3">
                <select name="categoria" class="form-select" id="sel-cat-prod">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $id_categoria == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="estado" class="form-select" id="sel-estado-prod">
                    <option value="">Todos</option>
                    <option value="1" <?= $estado === '1' ? 'selected' : '' ?>>Activos</option>
                    <option value="0" <?= $estado === '0' ? 'selected' : '' ?>>Inactivos</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill" id="btn-filtrar-prod"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="<?= BASE_URL ?>/modules/productos/index.php" class="btn btn-outline-secondary" id="btn-limpiar-prod"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table" id="tabla-productos">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Existencia</th>
                        <th>Mínimo</th>
                        <th>Inventario</th>
                        <th>Estado</th>
                        <?php if ($rol === 'admin'): ?><th>Acciones</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($productos)): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">No se encontraron productos.</td></tr>
                    <?php else: ?>
                    <?php foreach ($productos as $p): ?>
                    <tr>
                        <td><code style="font-size:.78rem"><?= htmlspecialchars($p['codigo']) ?></code></td>
                        <td>
                            <strong><?= htmlspecialchars($p['nombre']) ?></strong>
                            <?php if ($p['descripcion']): ?>
                            <div style="font-size:.75rem;color:var(--text-secondary)"><?= htmlspecialchars(mb_substr($p['descripcion'], 0, 40)) ?><?= strlen($p['descripcion']) > 40 ? '…' : '' ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['cat_nombre']) ?></td>
                        <td><strong style="font-size:1.0625rem"><?= $p['existencia_actual'] ?></strong></td>
                        <td><?= $p['existencia_minima'] ?></td>
                        <td>
                            <?php if ($p['existencia_actual'] == 0): ?>
                            <span class="bstatus b-agotado"><i class="bi bi-x-circle me-1"></i>Agotado</span>
                            <?php elseif ($p['existencia_actual'] <= $p['existencia_minima']): ?>
                            <span class="bstatus b-bajo"><i class="bi bi-exclamation-triangle me-1"></i>Stock bajo</span>
                            <?php else: ?>
                            <span class="bstatus b-normal"><i class="bi bi-check-circle me-1"></i>Normal</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['estado']): ?>
                            <span class="bstatus b-activo">Activo</span>
                            <?php else: ?>
                            <span class="bstatus b-inactivo">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <?php if ($rol === 'admin'): ?>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="<?= BASE_URL ?>/modules/productos/editar.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary" id="btn-editar-prod-<?= $p['id'] ?>" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="<?= BASE_URL ?>/modules/productos/eliminar.php" onsubmit="return confirm('¿Desea eliminar este producto?')">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" id="btn-eliminar-prod-<?= $p['id'] ?>" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
