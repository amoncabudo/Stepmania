<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $id = $_POST['id'];
    $titulo = $_POST['titulo'];
    $artista = $_POST['artista'];
    $archivoCaratula = $_POST['archivoCaratula']; // Obtener la ruta de la carátula
    
    // Cargar el archivo JSON
    $jsonFile = 'src/PHP/playlist.json';
    $data = json_decode(file_get_contents($jsonFile), true);

    // Buscar la canción por ID y actualizarla
    foreach ($data as &$cancion) {
        if ($cancion['id'] == $id) {
            $cancion['titulo'] = $titulo;
            $cancion['artista'] = $artista;
            $cancion['archivoCaratula'] = $archivoCaratula; // Actualizar la carátula
            break;
        }
    }

    // Guardar los datos actualizados de vuelta en el JSON
    file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
    
    // Redirigir a la lista de canciones o mostrar un mensaje
    header('Location: ../listarcanciones.html'); // Redirigir a la lista de canciones
    exit();
}
?>
