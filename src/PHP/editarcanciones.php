<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    if (!isset($_POST['id'])) {
        throw new Exception('No se proporcionó ID');
    }

    $baseDir = __DIR__;
    $uploadDir = 'uploads/';
    $caratulasDir = $uploadDir . 'caratulas/';
    $audiosDir = $uploadDir . 'musica/';
    $jsonFile = 'playlist.json';

    // Leer el archivo JSON existente
    $jsonContent = file_get_contents($jsonFile);
    $canciones = json_decode($jsonContent, true);

    $songId = $_POST['id'];
    $cancionIndex = array_search($songId, array_column($canciones, 'id'));

    if ($cancionIndex === false) {
        throw new Exception('Canción no encontrada');
    }

    // Actualizar los datos básicos
    $canciones[$cancionIndex]['titulo'] = $_POST['titulo'];
    $canciones[$cancionIndex]['artista'] = $_POST['artista'];

    // Manejar el archivo de música
    if (isset($_FILES['musica']) && $_FILES['musica']['error'] === UPLOAD_ERR_OK) {
        $musica_dir = $audiosDir;
        if (!is_dir($musica_dir)) {
            mkdir($musica_dir, 0777, true);
        }

        // Eliminar el archivo anterior si existe
        if (isset($canciones[$cancionIndex]['archivoMusica']) && file_exists($canciones[$cancionIndex]['archivoMusica'])) {
            unlink($canciones[$cancionIndex]['archivoMusica']);
        }

        // Guardar el nuevo archivo de música
        $musica_file = $musica_dir . basename($_FILES['musica']['name']);
        if (move_uploaded_file($_FILES['musica']['tmp_name'], $musica_file)) {
            $canciones[$cancionIndex]['archivoMusica'] = $musica_file;
        } else {
            throw new Exception("Error al mover el archivo de música.");
        }
    }

    // Manejar el archivo de carátula
    if (isset($_FILES['caratula']) && $_FILES['caratula']['error'] === UPLOAD_ERR_OK) {
        $caratula_dir = $caratulasDir;
        if (!is_dir($caratula_dir)) {
            mkdir($caratula_dir, 0777, true);
        }

        // Eliminar la carátula anterior si existe
        if (isset($canciones[$cancionIndex]['archivoCaratula']) && file_exists($canciones[$cancionIndex]['archivoCaratula'])) {
            unlink($canciones[$cancionIndex]['archivoCaratula']);
        }

        // Guardar la nueva carátula
        $caratula_file = $caratula_dir . basename($_FILES['caratula']['name']);
        if (move_uploaded_file($_FILES['caratula']['tmp_name'], $caratula_file)) {
            $canciones[$cancionIndex]['archivoCaratula'] = $caratula_file;
        } else {
            throw new Exception("Error al mover la carátula.");
        }
    }

    // Guardar los cambios en el archivo JSON
    if (file_put_contents($jsonFile, json_encode($canciones, JSON_PRETTY_PRINT)) === false) {
        throw new Exception('Error al guardar los cambios en el JSON');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Canción actualizada correctamente',
        'cancion' => $canciones[$cancionIndex]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
