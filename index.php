<?php
if (session_status() === PHP_SESSION_NONE) session_start();

define('BASE_URL', 'http://localhost/inventario');

if (isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/modules/dashboard/index.php');
} else {
    header('Location: ' . BASE_URL . '/modules/auth/login.php');
}
exit;
