<?php
require_once 'config/conexion.php';
session_start();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo']);
    $password = trim($_POST['password']);

    if (!empty($correo) && !empty($password)) {
        // Consulta usando PDO con bindParam como pide la rúbrica
        $sql = "SELECT * FROM usuarios WHERE email = :correo";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Validamos la contraseña usando password_verify() en lugar de comparación directa
        if ($usuario && password_verify($password, $usuario['password'])) {
            // Guardar datos en sesión
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_nombre'] = $usuario['nombres'] . ' ' . $usuario['apellidos'];
            $_SESSION['user_correo'] = $usuario['email'];
            
            // Verificamos si es administrador (rol_id = 1)
            if (isset($usuario['rol_id']) && $usuario['rol_id'] == 1) {
                $_SESSION['user_rol'] = 'ADMIN';
                header("Location: admin/dashboard.php");
                exit;
            } else {
                $_SESSION['user_rol'] = 'CLIENTE';
                header("Location: index.php");
                exit;
            }
        } else {
            $error = "Correo o contraseña incorrectos.";
        }
    } else {
        $error = "Por favor completa todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión | E-Commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4"><i class="fas fa-lock"></i> Iniciar Sesión</h3>
                    
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" name="correo" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-sign-in-alt"></i> Ingresar</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="register.php" class="text-decoration-none">¿No tienes cuenta? Regístrate aquí</a><br>
                        <a href="index.php" class="text-decoration-none">&larr; Volver al inicio</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>