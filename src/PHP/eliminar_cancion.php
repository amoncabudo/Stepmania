<?php
// Ruta al archivo JSON
$jsonFilePath = 'playlist.json';

// Leer el archivo JSON
$songData = [];
if (file_exists($jsonFilePath)) {
    $jsonData = file_get_contents($jsonFilePath);
    $songData = json_decode($jsonData, true);
}

// Obtener el ID de la canción a eliminar
$id = $_GET['id'];

// Filtrar la lista de canciones, eliminando la canción con el ID dado
$songData = array_filter($songData, function($cancion) use ($id) {
    return $cancion['id'] != $id;
});

// Guardar el JSON actualizado
file_put_contents($jsonFilePath, json_encode(array_values($songData), JSON_PRETTY_PRINT));

// Responder con un mensaje de éxito
echo json_encode(['success' => true]);
?>
