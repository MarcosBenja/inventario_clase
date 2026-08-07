<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
verificarAdmin();

$pageTitle    = 'Nuevo Producto';
$activeModule = 'productos';

$categorias = $pdo->query('SELECT * FROM categorias WHERE estado = 1 ORDER BY nombre')->fetchAll();

$errores = [];
$datos   = ['codigo' => '', 'nombre' => '', 'descripcion' => '', 'id_categoria' => '', 'existencia_minima' => 5, 'estado' => 1];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['codigo']           = trim(strtoupper($_POST['codigo'] ?? ''));
    $datos['nombre']           = trim($_POST['nombre'] ?? '');
    $datos['descripcion']      = trim($_POST['descripcion'] ?? '');
    $datos['id_categoria']     = (int)($_POST['id_categoria'] ?? 0);
    $datos['existencia_minima']= (int)($_POST['existencia_minima'] ?? 0);
    $datos['estado']           = isset($_POST['estado']) ? 1 : 0;

    if ($datos['codigo'] === '')   $errores[] = 'El código es requerido.';
    if ($datos['nombre'] === '')   $errores[] = 'El nombre es requerido.';
    if (!$datos['id_categoria'])   $errores[] = 'Seleccione una categoría.';
    if ($datos['existencia_minima'] < 0) $errores[] = 'La existencia mínima no puede ser negativa.';

    if ($datos['codigo'] !== '') {
        $chk = $pdo->prepare('SELECT id FROM productos WHERE codigo = ?');
        $chk->execute([$datos['codigo']]);
        if ($chk->fetch()) $errores[] = 'El código ya está registrado.';
    }

    if (empty($errores)) {
        $pdo->prepare('INSERT INTO productos (codigo, nombre, descripcion, id_categoria, existencia_actual, existencia_minima, estado) VALUES (?, ?, ?, ?, 0, ?, ?)')
            ->execute([$datos['codigo'], $datos['nombre'], $datos['descripcion'], $datos['id_categoria'], $datos['existencia_minima'], $datos['estado']]);
        header('Location: ' . BASE_URL . '/modules/productos/index.php?msg=creado');
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <div>
        <div class="page-title">Nuevo Producto</div>
        <div class="page-subtitle">La existencia inicial será 0. Use entradas para agregar stock.</div>
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
        <form method="POST" id="form-crear-producto">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="prod-codigo">Código *</label>
                    <input type="text" class="form-control" id="prod-codigo" name="codigo"
                           value="<?= htmlspecialchars($datos['codigo']) ?>"
                           placeholder="Ej: ELEC-001" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label" for="prod-nombre">Nombre *</label>
                    <input type="text" class="form-control" id="prod-nombre" name="nombre"
                           value="<?= htmlspecialchars($datos['nombre']) ?>"
                           placeholder="Nombre del producto" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="prod-categoria">Categoría *</label>
                    <select class="form-select" id="prod-categoria" name="id_categoria" required>
                        <option value="">Seleccione una categoría...</option>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $datos['id_categoria'] == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="prod-minimo">Existencia Mínima</label>
                    <input type="number" class="form-control" id="prod-minimo" name="existencia_minima"
                           value="<?= $datos['existencia_minima'] ?>" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="estado" id="prod-estado" <?= $datos['estado'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="prod-estado">Activo</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label" for="prod-descripcion">Descripción</label>
                    <textarea class="form-control" id="prod-descripcion" name="descripcion" rows="3"
                              placeholder="Descripción del producto..."><?= htmlspecialchars($datos['descripcion']) ?></textarea>
                </div>
                <div class="col-12 pt-1">
                    <button type="submit" class="btn btn-primary" id="btn-guardar-producto">
                        <i class="bi bi-check-lg me-2"></i>Guardar Producto
                    </button>
                    <a href="<?= BASE_URL ?>/modules/productos/index.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
