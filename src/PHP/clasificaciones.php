<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

// Guardar en la base de datos o en un archivo JSON
$stats = [
    'nombre' => $data['nombre'],
    'puntuacion' => $data['puntuacion'],
    'rank' => $data['rank'],
    'aciertos' => $data['aciertos'],
    'fallos' => $data['fallos'],
    'songId' => $data['songId'],
    'fecha' => $data['fecha']
];

// Aquí va tu código para guardar las estadísticas

echo json_encode(['success' => true]);
?>