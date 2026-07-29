<?php
session_start();
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit;
}
require_once '../config/conexion.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $conexion->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->execute([$id]);
}

// Redirige a la lista de productos enviando el parámetro para activar la alerta flotante
header("Location: productos.php?mensaje=eliminado");
exit;
?>