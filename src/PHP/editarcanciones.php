<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];

    // Leer el archivo JSON existente
    $json_file = 'playlist.json';
    $canciones = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : array();

    $songId = isset($_POST['songId']) ? $_POST['songId'] : null;

    if ($songId !== null && isset($canciones[$songId])) {
        try {
            // Actualizar datos básicos
            $canciones[$songId]['titulo'] = $_POST['titulo'];
            $canciones[$songId]['artista'] = $_POST['artista'];
            $canciones[$songId]['descripcion'] = $_POST['descripcion'] ?? '';

            // Manejar archivo de música
            if (isset($_FILES['musica']) && $_FILES['musica']['error'] === UPLOAD_ERR_OK) {
                $musica_dir = 'uploads/audios/';
                if (!is_dir($musica_dir)) {
                    mkdir($musica_dir, 0777, true);
                }

                // Eliminar el archivo anterior si existe
                if (isset($canciones[$songId]['archivoMusica']) && file_exists($canciones[$songId]['archivoMusica'])) {
                    unlink($canciones[$songId]['archivoMusica']); // Borrar el archivo de música anterior
                }

                // Generar nombre único para el archivo
                $extension = pathinfo($_FILES['musica']['name'], PATHINFO_EXTENSION);
                $nuevo_nombre = uniqid('audio_') . '.' . $extension;
                $musica_file = $musica_dir . $nuevo_nombre;

                // Mover el nuevo archivo al directorio y actualizar JSON
                if (move_uploaded_file($_FILES['musica']['tmp_name'], $musica_file)) {
                    $canciones[$songId]['archivoMusica'] = $musica_file; // Actualizar con el nuevo archivo
                    $canciones[$songId]['audioTimestamp'] = time(); // Agregar timestamp para evitar caché
                } else {
                    throw new Exception("Error al mover el archivo de música.");
                }
            }

            // Manejar archivo de carátula (similar al audio)
            if (isset($_FILES['caratula']) && $_FILES['caratula']['error'] === UPLOAD_ERR_OK) {
                $caratula_dir = 'uploads/caratula/';
                if (!is_dir($caratula_dir)) {
                    mkdir($caratula_dir, 0777, true);
                }

                if (isset($canciones[$songId]['archivoCaratula']) && file_exists($canciones[$songId]['archivoCaratula'])) {
                    unlink($canciones[$songId]['archivoCaratula']); // Borrar la carátula anterior
                }

                $extension = pathinfo($_FILES['caratula']['name'], PATHINFO_EXTENSION);
                $nuevo_nombre = uniqid('caratula_') . '.' . $extension;
                $caratula_file = $caratula_dir . $nuevo_nombre;

                if (move_uploaded_file($_FILES['caratula']['tmp_name'], $caratula_file)) {
                    $canciones[$songId]['archivoCaratula'] = $caratula_file;
                    $canciones[$songId]['caratulaTimestamp'] = time();
                } else {
                    throw new Exception("Error al mover el archivo de carátula.");
                }
            }

            // Guardar cambios en el archivo JSON
            if (file_put_contents($json_file, json_encode($canciones, JSON_PRETTY_PRINT))) {
                $response['success'] = true;
                $response['message'] = 'Canción actualizada correctamente';
            } else {
                throw new Exception("Error al guardar los cambios en el archivo JSON.");
            }

        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
    } else {
        $response['message'] = "La canción no existe.";
    }

    echo json_encode($response);
    exit();
}
?>