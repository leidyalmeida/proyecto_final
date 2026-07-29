<?php
session_start();
require_once '../config/conexion.php';

// Limpiar cualquier salida previa para evitar errores en el JSON
if (ob_get_length()) ob_clean();
header('Content-Type: application/json');

if (isset($_POST['id'])) {
    $producto_id = intval($_POST['id']);
    // Capturar la cantidad enviada por POST; por defecto es 1
    $cantidad_a_agregar = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 1;
    if ($cantidad_a_agregar < 1) {
        $cantidad_a_agregar = 1;
    }

    // Capturar la talla enviada por POST (si aplica para la categoría de ropa)
    $talla = isset($_POST['talla']) ? trim($_POST['talla']) : '';

    try {
        // Verificar que el producto exista en la base de datos y esté activo (incluyendo la categoría)
        $stmt = $conexion->prepare("SELECT p.*, c.nombre as categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.id = ? AND p.estado = 1");
        $stmt->execute([$producto_id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
            // Si la categoría es tecnología u hogar, ignoramos la talla limpiándola
            $categoria = strtolower(trim($producto['categoria'] ?? ''));
            if ($categoria === 'tecnologia' || $categoria === 'hogar' || $categoria === 'tecnología') {
                $talla = '';
            }

            // Generar clave única combinando ID y Talla (ej. "5_M" o solo "5" si no aplica talla)
            $key = !empty($talla) ? $producto_id . '_' . $talla : $producto_id;

            // Inicializar el carrito en sesión si aún no existe
            if (!isset($_SESSION['carrito'])) {
                $_SESSION['carrito'] = [];
            }

            // Calcular cuántas unidades ya existen de esta misma variante específica en el carrito
            $cantidad_actual_en_carrito = isset($_SESSION['carrito'][$key]) ? $_SESSION['carrito'][$key]['cantidad'] : 0;
            $total_solicitado = $cantidad_actual_en_carrito + $cantidad_a_agregar;

            // Validar que el stock total no sea superado
            if ($producto['stock'] < $total_solicitado) {
                echo json_encode([
                    'status' => 'error',
                    'mensaje' => 'No hay suficiente stock disponible. Stock actual: ' . $producto['stock']
                ]);
                exit;
            }

            // Si el producto con esta misma variante/talla ya se encuentra en el carrito, incrementamos la cantidad
            if (isset($_SESSION['carrito'][$key])) {
                $_SESSION['carrito'][$key]['cantidad'] += $cantidad_a_agregar;
            } else {
                // Si es una variante nueva, lo agregamos de forma independiente guardando su talla solo si aplica
                $_SESSION['carrito'][$key] = [
                    'id' => $producto['id'],
                    'codigo' => $producto['codigo'],
                    'descripcion' => $producto['descripcion'],
                    'precio' => $producto['precio'],
                    'imagen' => $producto['imagen'],
                    'cantidad' => $cantidad_a_agregar,
                    'talla' => $talla
                ];
            }

            echo json_encode([
                'status' => 'success',
                'mensaje' => '¡El producto se agregó al carrito correctamente!',
                'total_items' => count($_SESSION['carrito'])
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