<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// 1. Conexión a la base de datos MySQL (XAMPP por defecto)
$host = 'localhost';
$usuario = 'root';
$password = '';
$base_datos = 'ecommerce_db';

$conexion = new mysqli($host, $usuario, $password, $base_datos);
$conexion->set_charset("utf8");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// 2. Consulta real a tu tabla 'productos' (usando las columnas que tienes en tu SQL)
$sql = "SELECT codigo, descripcion, precio, stock FROM productos";
$resultado = $conexion->query($sql);

// 3. Crear instancia de Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Inventario de Productos');

// 4. Estilos personalizados para la cabecera
$estiloCabecera = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '4F46E5'], // Color azul elegante
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

// Aplicar cabeceras ajustadas a tu tabla de productos
$sheet->setCellValue('A1', 'Código');
$sheet->setCellValue('B1', 'Descripción');
$sheet->setCellValue('C1', 'Precio ($)');
$sheet->setCellValue('D1', 'Stock');

$sheet->getStyle('A1:D1')->applyFromArray($estiloCabecera);
$sheet->getRowDimension(1)->setRowHeight(25);

// 5. Llenar los datos dinámicamente desde la base de datos real
$fila = 2;
if ($resultado->num_rows > 0) {
    while ($producto = $resultado->fetch_assoc()) {
        $sheet->setCellValue('A' . $fila, $producto['codigo']);
        $sheet->setCellValue('B' . $fila, $producto['descripcion']);
        $sheet->setCellValue('C' . $fila, $producto['precio']);
        $sheet->setCellValue('D' . $fila, $producto['stock']);
        
        // Dar formato de moneda a la columna de precios
        $sheet->getStyle('C' . $fila)->getNumberFormat()->setFormatCode('$#,##0.00');
        
        $fila++;
    }
}

// Autoajustar el ancho de las columnas automáticamente
foreach (range('A', 'D') as $columna) {
    $sheet->getColumnDimension($columna)->setAutoSize(true);
}

// 6. Configurar las cabeceras HTTP para forzar la descarga directa en el navegador
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Reporte_Productos_Hito3.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

// Cerrar conexión
$conexion->close();
exit;
?>