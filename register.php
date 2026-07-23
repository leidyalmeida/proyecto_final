<?php
session_start();
require_once 'config/conexion.php';

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula_ruc = trim($_POST['cedula_ruc']);
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);

    if (empty($cedula_ruc) || empty($nombres) || empty($apellidos) || empty($email) || empty($password)) {
        $error = "Por favor, completa todos los campos obligatorios.";
    } else {
        try {
            // Verificar si el correo o la cédula ya están registrados
            $stmt_check = $conexion->prepare("SELECT id FROM usuarios WHERE email = ? OR cedula_ruc = ?");
            $stmt_check->execute([$email, $cedula_ruc]);
            if ($stmt_check->rowCount() > 0) {
                $error = "El correo electrónico o la cédula/RUC ya se encuentran registrados.";
            } else {
                // Encriptar la contraseña de forma segura
                $password_hashed = password_hash($password, PASSWORD_DEFAULT);
                $rol_cliente_id = 2; // Rol 2 por defecto para CLIENTES según la BD

                // Inserción usando PDO con bindParam como exige la rúbrica
                $sql = "INSERT INTO usuarios (rol_id, cedula_ruc, nombres, apellidos, email, password, telefono, direccion) 
                        VALUES (:rol_id, :cedula_ruc, :nombres, :apellidos, :email, :password, :telefono, :direccion)";
                
                $stmt = $conexion->prepare($sql);
                $stmt->bindParam(':rol_id', $rol_cliente_id, PDO::PARAM_INT);
                $stmt->bindParam(':cedula_ruc', $cedula_ruc, PDO::PARAM_STR);
                $stmt->bindParam(':nombres', $nombres, PDO::PARAM_STR);
                $stmt->bindParam(':apellidos', $apellidos, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':password', $password_hashed, PDO::PARAM_STR);
                $stmt->bindParam(':telefono', $telefono, PDO::PARAM_STR);
                $stmt->bindParam(':direccion', $direccion, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    $exito = "¡Registro exitoso! Ya puedes iniciar sesión.";
                } else {
                    $error = "Ocurrió un error al registrar el usuario.";
                }
            }
        } catch (PDOException $e) {
            $error = "Error en la base de datos: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiTienda - Registro de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">

    <!-- Barra de Navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-store"></i> MiTienda</a>
            <div class="d-flex align-items-center">
                <a href="index.php" class="text-white text-decoration-none me-3"><i class="fas fa-arrow-left"></i> Volver a la Tienda</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="card shadow-sm border-0 p-4">
                    <h2 class="text-center mb-4 fw-bold text-secondary"><i class="fas fa-user-plus"></i> Registro de Cliente</h2>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <?= $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($exito)): ?>
                        <div class="alert alert-success text-center" role="alert">
                            <i class="fas fa-check-circle"></i> <?= $exito; ?><br>
                            <a href="login.php" class="btn btn-sm btn-success mt-3">Ir a Iniciar Sesión</a>
                        </div>
                    <?php else: ?>

                        <form action="register.php" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="cedula_ruc" class="form-label">Cédula o RUC *</label>
                                    <input type="text" class="form-control" id="cedula_ruc" name="cedula_ruc" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Correo Electrónico *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombres" class="form-label">Nombres *</label>
                                    <input type="text" class="form-control" id="nombres" name="nombres" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="apellidos" class="form-label">Apellidos *</label>
                                    <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Contraseña *</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" id="telefono" name="telefono">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección</label>
                                <textarea class="form-control" id="direccion" name="direccion" rows="2" placeholder="Calle principal, secundaria, número de casa..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                                <i class="fas fa-user-check"></i> Registrarse
                            </button>
                        </form>

                        <div class="text-center mt-3">
                            <p class="text-muted">¿Ya tienes una cuenta? <a href="login.php" class="text-decoration-none fw-bold">Inicia Sesión aquí</a></p>
                        </div>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie de página -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2026 MiTienda. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>