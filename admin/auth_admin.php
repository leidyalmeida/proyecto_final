<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ha iniciado sesión y si su rol es ADMIN
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit;
}
?>