<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
verificarAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/modules/categorias/index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if (!$id) {
    header('Location: ' . BASE_URL . '/modules/categorias/index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM productos WHERE id_categoria = ?');
$stmt->execute([$id]);

if ($stmt->fetchColumn() > 0) {
    header('Location: ' . BASE_URL . '/modules/categorias/index.php?msg=con_productos');
    exit;
}

try {
    $pdo->prepare('DELETE FROM categorias WHERE id = ?')->execute([$id]);
    header('Location: ' . BASE_URL . '/modules/categorias/index.php?msg=eliminado');
} catch (PDOException $e) {
    header('Location: ' . BASE_URL . '/modules/categorias/index.php?msg=error');
}
exit;
