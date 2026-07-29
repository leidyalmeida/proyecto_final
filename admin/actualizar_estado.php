<?php
session_start();

// Verificar que sea administrador
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pedido_id']) && isset($_POST['nuevo_estado'])) {
    $pedido_id = intval($_POST['pedido_id']);
    $nuevo_estado = $_POST['nuevo_estado'];

    // Lista blanca de estados permitidos por seguridad
    $estados_permitidos = ['PENDIENTE', 'PROCESO', 'COMPLETADO', 'CANCELADO'];

    if (in_array($nuevo_estado, $estados_permitidos)) {
        try {
            $stmt = $conexion->prepare("UPDATE pedidos SET estado_pedido = ? WHERE id = ?");
            $stmt->execute([$nuevo_estado, $pedido_id]);
        } catch (PDOException $e) {
            // Manejo silencioso o registro de error si falla la BD
        }
    }
}

// Redireccionar de vuelta al dashboard
header("Location: dashboard.php");
exit();