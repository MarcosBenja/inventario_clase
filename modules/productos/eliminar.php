<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
verificarAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/modules/productos/index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if (!$id) { header('Location: ' . BASE_URL . '/modules/productos/index.php'); exit; }

$chkE = $pdo->prepare('SELECT COUNT(*) FROM entradas WHERE id_producto = ?');
$chkE->execute([$id]);
$chkS = $pdo->prepare('SELECT COUNT(*) FROM salidas WHERE id_producto = ?');
$chkS->execute([$id]);

if ($chkE->fetchColumn() > 0 || $chkS->fetchColumn() > 0) {
    header('Location: ' . BASE_URL . '/modules/productos/index.php?msg=con_movimientos');
    exit;
}

try {
    $pdo->prepare('DELETE FROM productos WHERE id = ?')->execute([$id]);
    header('Location: ' . BASE_URL . '/modules/productos/index.php?msg=eliminado');
} catch (PDOException $e) {
    header('Location: ' . BASE_URL . '/modules/productos/index.php?msg=error');
}
exit;
