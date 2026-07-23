<?php
session_start();
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit;
}
require_once '../config/conexion.php';

$mensaje = "";

// Procesar el formulario cuando se envía una nueva categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre_categoria'])) {
    $nombre = trim($_POST['nombre_categoria']);
    if (!empty($nombre)) {
        try {
            $stmt = $conexion->prepare("INSERT INTO categorias (nombre) VALUES (:nombre)");
            $stmt->execute([':nombre' => $nombre]);
            $mensaje = "¡Categoría creada con éxito!";
        } catch (PDOException $e) {
            $mensaje = "Error: Es posible que esta categoría ya exista.";
        }
    }
}

// Obtener todas las categorías de la base de datos
try {
    $stmt = $conexion->query("SELECT * FROM categorias");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categorias = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Categorías | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php"><i class="fas fa-cogs"></i> Admin Panel</a>
            <a href="../logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </div>
    </nav>

    <div class="container mt-4 mb-5">
        <h2><i class="fas fa-tags"></i> Gestionar Categorías</h2>
        <hr>
        <?php if(!empty($mensaje)): ?>
            <div class="alert alert-info"><?= $mensaje ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Formulario de Registro -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm p-4">
                    <h4 class="mb-3">Nueva Categoría</h4>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nombre de Categoría</label>
                            <input type="text" class="form-control" name="nombre_categoria" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Guardar Categoría</button>
                    </form>
                </div>
            </div>

            <!-- Listado de Categorías -->
            <div class="col-md-8">
                <div class="card shadow-sm p-4">
                    <h4 class="mb-3">Categorías Existentes</h4>
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categorias)): ?>
                                <tr>
                                    <td colspan="2" class="text-center text-muted">No hay categorías registradas.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($categorias as $cat): ?>
                                <tr>
                                    <td><?= $cat['id'] ?></td>
                                    <td><?= htmlspecialchars($cat['nombre']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="dashboard.php" class="btn btn-secondary">&larr; Volver al Dashboard</a>
        </div>
    </div>
</body>
</html>