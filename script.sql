USE ecommerce_db;

INSERT INTO productos (categoria_id, codigo, descripcion, precio, stock, imagen, estado) VALUES 
-- Hogar
(2, 'HOG-001', 'Juego de Sábanas King Size de Microfibra Suave', 29.99, 20, 'sabanas.jpg', 1),
(2, 'HOG-002', 'Lámpara de Mesa LED con Regulador de Intensidad', 24.50, 15, 'lampara.jpg', 1),
(2, 'HOG-003', 'Set de Ollas Antiadherentes de 5 Piezas', 59.99, 10, 'ollas.jpg', 1),
(2, 'HOG-004', 'Organizador Giratorio de Especias para Cocina', 19.99, 25, 'organizador.jpg', 1),
(2, 'HOG-005', 'Alfombra Antideslizante Absorbente para Baño', 14.99, 30, 'alfombra.jpg', 1),

-- Ropa
(3, 'ROP-001', 'Camiseta Casual de Algodón 100% para Uso Diario', 18.50, 30, 'camiseta.jpg', 1),
(3, 'ROP-002', 'Jeans Clásicos Azules de Corte Moderno', 39.99, 20, 'jeans.jpg', 1),
(3, 'ROP-003', 'Chaqueta Deportiva Impermeable Ligera', 49.99, 12, 'chaqueta.jpg', 1),
(3, 'ROP-004', 'Hoodie Oversize de Felpa con Capucha y Bolsillo', 32.00, 25, 'hoodie.jpg', 1),
(3, 'ROP-005', 'Vestido Casual Veraniego con Estampado Floral', 28.50, 18, 'vestido.jpg', 1);