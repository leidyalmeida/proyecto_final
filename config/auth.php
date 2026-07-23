<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function verificarAdmin() {
    // Verificar si el usuario ha iniciado sesión y si su rol es administrador (id_rol = 1 o rol 'ADMIN')
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'ADMIN') {
        header("Location: ../login.php");
        exit;
    }
}

function verificarCliente() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}
?>