<?php
// Ruta al archivo JSON donde se almacenan las puntuaciones
$filename = 'puntuaciones.json';

// Obtener los datos enviados por el cliente (nombre y puntuación)
$data = json_decode(file_get_contents('php://input'), true);
$nombre = $data['nombre'];
$puntuacion = $data['puntuacion'];

// Convertir el nombre del usuario a un formato seguro usando bin2hex
$nombreSeguro = bin2hex($nombre);

// Leer el archivo JSON existente
$puntuaciones = [];
if (file_exists($filename)) {
    $puntuaciones = json_decode(file_get_contents($filename), true);
}

// Añadir la nueva puntuación
$puntuaciones[] = [
    'nombre' => $nombre,
    'puntuacion' => $puntuacion,
    'fecha' => date('Y-m-d H:i:s')  // Añadir la fecha
];

// Guardar las puntuaciones actualizadas en el archivo JSON
file_put_contents($filename, json_encode($puntuaciones, JSON_PRETTY_PRINT));

// Crear cookies para el nombre y la puntuación, utilizando bin2hex para el nombre
setcookie("nombre", $nombreSeguro, time() + (86400 * 7), "/"); // Expira en 7 días
setcookie("puntuacion", $puntuacion, time() + (86400 * 7), "/"); // Expira en 7 días

// Devolver una respuesta al cliente
echo json_encode(['success' => true]);
?>
