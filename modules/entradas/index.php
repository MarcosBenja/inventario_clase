<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
verificarSesion();

$pageTitle    = 'Historial de Entradas';
$activeModule = 'entradas';

$buscar      = trim($_GET['buscar'] ?? '');
$id_producto = $_GET['producto'] ?? '';
$fecha_desde = $_GET['desde'] ?? '';
$fecha_hasta = $_GET['hasta'] ?? '';

$sql    = 'SELECT e.*, p.nombre AS prod_nombre, p.codigo, u.nombre_completo AS usuario FROM entradas e JOIN productos p ON e.id_producto = p.id JOIN usuarios u ON e.id_usuario = u.id WHERE 1=1';
$params = [];

if ($buscar !== '')    { $sql .= ' AND (p.nombre LIKE ? OR p.codigo LIKE ? OR e.motivo LIKE ?)'; $params[] = "%$buscar%"; $params[] = "%$buscar%"; $params[] = "%$buscar%"; }
if ($id_producto !== ''){ $sql .= ' AND e.id_producto = ?'; $params[] = $id_producto; }
if ($fecha_desde !== ''){ $sql .= ' AND e.fecha >= ?'; $params[] = $fecha_desde; }
if ($fecha_hasta !== ''){ $sql .= ' AND e.fecha <= ?'; $params[] = $fecha_hasta; }
$sql .= ' ORDER BY e.fecha DESC, e.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$entradas = $stmt->fetchAll();

$productos = $pdo->query('SELECT id, codigo, nombre FROM productos ORDER BY nombre')->fetchAll();
$msg       = $_GET['msg'] ?? '';

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Historial de Entradas</div>
        <div class="page-subtitle"><?= count($entradas) ?> registros</div>
    </div>
    <a href="<?= BASE_URL ?>/modules/entradas/crear.php" class="btn btn-primary" id="btn-nueva-entrada">
        <i class="bi bi-plus-lg me-2"></i>Registrar Entrada
    </a>
</div>

<?php if ($msg === 'creado'): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle-fill me-2"></i>Entrada registrada exitosamente. El stock fue actualizado.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card">
    <div class="card-body" style="padding:1.25rem">
        <form method="GET" class="row g-2 mb-3" id="form-filtro-entradas">
            <div class="col-md-3">
                <input type="text" name="buscar" class="form-control" placeholder="Buscar producto o motivo..." value="<?= htmlspecialchars($buscar) ?>">
            </div>
            <div class="col-md-3">
                <select name="producto" class="form-select">
                    <option value="">Todos los productos</option>
                    <?php foreach ($productos as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $id_producto == $p['id'] ? 'selected' : '' ?>>[<?= $p['codigo'] ?>] <?= htmlspecialchars($p['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="desde" class="form-control" value="<?= $fecha_desde ?>" placeholder="Desde">
            </div>
            <div class="col-md-2">
                <input type="date" name="hasta" class="form-control" value="<?= $fecha_hasta ?>" placeholder="Hasta">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Filtrar</button>
                <a href="<?= BASE_URL ?>/modules/entradas/index.php" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table" id="tabla-entradas">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th>Fecha</th>
                        <th>Cantidad</th>
                        <th>Motivo</th>
                        <th>Responsable</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($entradas)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No se encontraron registros.</td></tr>
                    <?php else: ?>
                    <?php foreach ($entradas as $e): ?>
                    <tr>
                        <td style="color:var(--text-secondary)"><?= $e['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($e['prod_nombre']) ?></strong>
                            <div style="font-size:.75rem;color:var(--text-secondary)"><?= htmlspecialchars($e['codigo']) ?></div>
                        </td>
                        <td><?= date('d/m/Y', strtotime($e['fecha'])) ?></td>
                        <td><span class="bstatus b-entrada">+<?= $e['cantidad'] ?></span></td>
                        <td><?= htmlspecialchars($e['motivo']) ?></td>
                        <td><?= htmlspecialchars($e['usuario']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
