<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_URL', 'http://localhost/inventario');
define('BASE_PATH', dirname(__DIR__));

$host    = 'localhost';
$dbname  = 'inventario_db';
$db_user = 'root';
$db_pass = '';
$charset = 'utf8mb4';

$dsn     = "mysql:host=$host;dbname=$dbname;charset=$charset";
$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $opciones);
} catch (PDOException $e) {
    die('<div style="font-family:sans-serif;padding:2rem;color:#dc2626;background:#fef2f2;border-radius:8px;margin:2rem">
        <strong>Error de conexión:</strong> ' . $e->getMessage() . '
        <p style="margin-top:.5rem;color:#6b7280;font-size:.875rem">Verifique que XAMPP esté corriendo y la base de datos <code>inventario_db</code> exista.</p>
    </div>');
}
