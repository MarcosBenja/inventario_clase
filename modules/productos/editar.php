<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
verificarAdmin();

$pageTitle    = 'Editar Producto';
$activeModule = 'productos';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . BASE_URL . '/modules/productos/index.php'); exit; }

$stmt = $pdo->prepare('SELECT p.*, c.nombre AS cat_nombre FROM productos p JOIN categorias c ON p.id_categoria = c.id WHERE p.id = ?');
$stmt->execute([$id]);
$prod = $stmt->fetch();
if (!$prod) { header('Location: ' . BASE_URL . '/modules/productos/index.php'); exit; }

$categorias = $pdo->query('SELECT * FROM categorias ORDER BY nombre')->fetchAll();
$errores    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo           = trim(strtoupper($_POST['codigo'] ?? ''));
    $nombre           = trim($_POST['nombre'] ?? '');
    $descripcion      = trim($_POST['descripcion'] ?? '');
    $id_categoria     = (int)($_POST['id_categoria'] ?? 0);
    $existencia_minima= (int)($_POST['existencia_minima'] ?? 0);
    $estado           = isset($_POST['estado']) ? 1 : 0;

    if ($codigo === '') $errores[] = 'El código es requerido.';
    if ($nombre === '') $errores[] = 'El nombre es requerido.';
    if (!$id_categoria) $errores[] = 'Seleccione una categoría.';
    if ($existencia_minima < 0) $errores[] = 'La existencia mínima no puede ser negativa.';

    if ($codigo !== '') {
        $chk = $pdo->prepare('SELECT id FROM productos WHERE codigo = ? AND id <> ?');
        $chk->execute([$codigo, $id]);
        if ($chk->fetch()) $errores[] = 'El código ya está en uso por otro producto.';
    }

    if (empty($errores)) {
        $pdo->prepare('UPDATE productos SET codigo = ?, nombre = ?, descripcion = ?, id_categoria = ?, existencia_minima = ?, estado = ? WHERE id = ?')
            ->execute([$codigo, $nombre, $descripcion, $id_categoria, $existencia_minima, $estado, $id]);
        header('Location: ' . BASE_URL . '/modules/productos/index.php?msg=editado');
        exit;
    }
    $prod['codigo'] = $codigo; $prod['nombre'] = $nombre; $prod['descripcion'] = $descripcion;
    $prod['id_categoria'] = $id_categoria; $prod['existencia_minima'] = $existencia_minima; $prod['estado'] = $estado;
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Editar Producto</div>
        <div class="page-subtitle">ID #<?= $id ?> — La existencia solo se modifica mediante entradas y salidas.</div>
    </div>
    <a href="<?= BASE_URL ?>/modules/productos/index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver
    </a>
</div>

<?php if (!empty($errores)): ?>
<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= implode('<br>', array_map('htmlspecialchars', $errores)) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body" style="padding:1.75rem">
        <form method="POST" id="form-editar-producto">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="prod-codigo">Código *</label>
                    <input type="text" class="form-control" id="prod-codigo" name="codigo" value="<?= htmlspecialchars($prod['codigo']) ?>" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label" for="prod-nombre">Nombre *</label>
                    <input type="text" class="form-control" id="prod-nombre" name="nombre" value="<?= htmlspecialchars($prod['nombre']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Existencia Actual</label>
                    <input type="text" class="form-control" value="<?= $prod['existencia_actual'] ?>" readonly>
                    <small style="font-size:.7rem;color:var(--text-secondary)">Modificar con entradas/salidas</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="prod-minimo">Existencia Mínima</label>
                    <input type="number" class="form-control" id="prod-minimo" name="existencia_minima" value="<?= $prod['existencia_minima'] ?>" min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="prod-categoria">Categoría *</label>
                    <select class="form-select" id="prod-categoria" name="id_categoria" required>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $prod['id_categoria'] == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label" for="prod-descripcion">Descripción</label>
                    <textarea class="form-control" id="prod-descripcion" name="descripcion" rows="3"><?= htmlspecialchars($prod['descripcion'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="estado" id="prod-estado" <?= $prod['estado'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="prod-estado">Producto activo</label>
                    </div>
                </div>
                <div class="col-12 pt-1">
                    <button type="submit" class="btn btn-primary" id="btn-actualizar-producto">
                        <i class="bi bi-check-lg me-2"></i>Actualizar Producto
                    </button>
                    <a href="<?= BASE_URL ?>/modules/productos/index.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
