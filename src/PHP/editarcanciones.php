<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    if (!isset($_POST['id'])) {
        throw new Exception('No se proporcionó ID');
    }

    $baseDir = __DIR__;
    // Rutas relativas para el almacenamiento
    $uploadDir = 'uploads/';
    $caratulasDir = $uploadDir . 'uploads/caratulas/';
    $audiosDir = $uploadDir . 'audios/';
    $jsonFile = 'playlist.json';

    // Crear directorios si no existen
    if (!file_exists($caratulasDir)) {
        mkdir($caratulasDir, 0777, true);
    }
    if (!file_exists($audiosDir)) {
        mkdir($audiosDir, 0777, true);
    }

    // Leer el archivo JSON actual
    $jsonContent = file_get_contents($jsonFile);
    if ($jsonContent === false) {
        throw new Exception('No se pudo leer el archivo JSON');
    }

    $canciones = json_decode($jsonContent, true);
    if ($canciones === null) {
        throw new Exception('Error al decodificar el JSON');
    }

    // Encontrar la canción a actualizar
    $id = $_POST['id'];
    $cancionIndex = -1;
    foreach ($canciones as $index => $cancion) {
        if ($cancion['id'] === $id) {
            $cancionIndex = $index;
            break;
        }
    }

    if ($cancionIndex === -1) {
        throw new Exception('Canción no encontrada');
    }

    // Actualizar los datos básicos
    $canciones[$cancionIndex]['titulo'] = $_POST['titulo'];
    $canciones[$cancionIndex]['artista'] = $_POST['artista'];

    if (isset($_FILES['caratula']) && $_FILES['caratula']['error'] === UPLOAD_ERR_OK) {
        $fileInfo = pathinfo($_FILES['caratula']['name']);
        $extension = strtolower($fileInfo['extension']);
        
        // Verificar extensión permitida
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception('Tipo de archivo de carátula no permitido');
        }
    
        // Generar nombre único para la carátula y construir la ruta
        $caratulaFilename = uniqid() . '.' . $extension;
        $caratulaPath = 'uploads/caratulas/' . $caratulaFilename;
    
        // Mover la carátula a la carpeta correspondiente
        if (!move_uploaded_file($_FILES['caratula']['tmp_name'], $caratulaPath)) {
            throw new Exception('Error al guardar la carátula');
        }
    
        // Almacenar la ruta relativa en el JSON
        $canciones[$cancionIndex]['archivoCaratula'] = $caratulaPath; // Guardar la ruta correcta
    }
    

    // Manejar el audio
if (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
    $fileInfo = pathinfo($_FILES['audio']['name']);
    $extension = strtolower($fileInfo['extension']);
    
    // Verificar las extensiones permitidas para los archivos de audio
    $allowedExtensions = ['mp3', 'wav', 'ogg'];
    if (!in_array($extension, $allowedExtensions)) {
        throw new Exception('Tipo de archivo de audio no permitido');
    }

    // Generar un nombre único para el archivo de audio
    $audioFilename = uniqid() . '.' . $extension;
    $audioPath = 'uploads/audios/' . $audioFilename; // Ruta correcta para guardar el audio

    // Mover el archivo a la carpeta correspondiente
    if (!move_uploaded_file($_FILES['audio']['tmp_name'], $audioPath)) {
        throw new Exception('Error al guardar el audio');
    }

    // Almacenar la ruta relativa en el JSON
    $canciones[$cancionIndex]['audio'] = $audioPath; // Guardar la ruta correcta
}


    // Guardar los cambios en el archivo JSON
    if (file_put_contents($jsonFile, json_encode($canciones, JSON_PRETTY_PRINT)) === false) {
        throw new Exception('Error al guardar los cambios en el JSON');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Canción actualizada correctamente',
        'cancion' => $canciones[$cancionIndex] // Devolver la canción actualizada
    ]);

} catch (Exception $e) {
    error_log("Error en actualizar_cancion.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>