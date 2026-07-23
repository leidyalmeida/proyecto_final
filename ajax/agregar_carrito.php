<?php
session_start();
require_once '../config/conexion.php';

// Limpiar cualquier salida previa para evitar errores en el JSON
if (ob_get_length()) ob_clean();
header('Content-Type: application/json');

if (isset($_POST['id'])) {
    $producto_id = intval($_POST['id']);

    try {
        // Verificar que el producto exista en la base de datos y esté activo
        $stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ? AND estado = 1");
        $stmt->execute([$producto_id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
            // Inicializar el carrito en sesión si aún no existe
            if (!isset($_SESSION['carrito'])) {
                $_SESSION['carrito'] = [];
            }

            // Si el producto ya se encuentra en el carrito, incrementamos la cantidad
            if (isset($_SESSION['carrito'][$producto_id])) {
                $_SESSION['carrito'][$producto_id]['cantidad'] += 1;
            } else {
                // Si no está, lo agregamos con cantidad 1
                $_SESSION['carrito'][$producto_id] = [
                    'id' => $producto['id'],
                    'codigo' => $producto['codigo'],
                    'descripcion' => $producto['descripcion'],
                    'precio' => $producto['precio'],
                    'imagen' => $producto['imagen'],
                    'cantidad' => 1
                ];
            }

            echo json_encode([
                'status' => 'success',
                'mensaje' => '¡El producto se agregó al carrito correctamente!'
            ]);
            exit;
        } else {
            echo json_encode([
                'status' => 'error',
                'mensaje' => 'El producto no está disponible o no existe.'
            ]);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'mensaje' => 'Error en el servidor: ' . $e->getMessage()
        ]);
        exit;
    }
} else {
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'No se recibió el ID del producto.'
    ]);
    exit;
}
?>