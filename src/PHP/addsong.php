<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Verificar que los campos del formulario estén presentes
    $titulo = isset($_POST['titulo']) ? $_POST['titulo'] : null;
    $artista = isset($_POST['artista']) ? $_POST['artista'] : null;
    $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : null;

    // Asegurarse de que se hayan enviado los archivos correctamente
    if (isset($_FILES['musica']) && isset($_FILES['caratula'])) {
        
        // Directorios de subida
        $musica_dir = 'uploads/musica/';
        $caratula_dir = 'uploads/caratula/';
        $text_dir = 'uploads/text/'; // Directorio para archivos de texto

        // Verificar y crear los directorios si no existen
        if (!is_dir($musica_dir)) {
            mkdir($musica_dir, 0777, true);
        }
        if (!is_dir($caratula_dir)) {
            mkdir($caratula_dir, 0777, true);
        }
        if (!is_dir($text_dir)) {
            mkdir($text_dir, 0777, true);
        }

        $musica_file = $musica_dir . basename($_FILES['musica']['name']);
        if (!move_uploaded_file($_FILES['musica']['tmp_name'], $musica_file)) {
            echo "Error al subir el archivo de música.";
            exit();
        }

        // Verificar errores en el archivo de carátula
        if ($_FILES['caratula']['error'] === UPLOAD_ERR_OK) {
            $caratula_file = $caratula_dir . basename($_FILES['caratula']['name']);
            if (!move_uploaded_file($_FILES['caratula']['tmp_name'], $caratula_file)) {
                echo "Error al mover el archivo de carátula.";
                exit();
            }
        } else {
            echo "Error al subir el archivo de carátula. Código de error: " . $_FILES['caratula']['error'];
            exit();
        }

        // Subir el archivo de texto si fue enviado, o crear un archivo de texto con la descripción
        $text_file = null;
        if (isset($_FILES['textFile']) && $_FILES['textFile']['error'] === UPLOAD_ERR_OK) {
            // Si se subió un archivo de texto, moverlo a la carpeta correspondiente
            $text_file = $text_dir . basename($_FILES['textFile']['name']);
            if (!move_uploaded_file($_FILES['textFile']['tmp_name'], $text_file)) {
                echo "Error al subir el archivo de texto.";
                exit();
            }
        } elseif (!empty($descripcion)) {
            // Si no se subió un archivo de texto, guardar la descripción como un archivo .txt
            $text_file_name = 'descripcion_' . uniqid() . '.txt';
            $text_file = $text_dir . $text_file_name;
            if (file_put_contents($text_file, $descripcion) === false) {
                echo "Error al guardar la descripción como archivo de texto.";
                exit();
            }
        }

        // Leer el archivo JSON existente
        $json_file = 'playlist.json';
        $canciones = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : array();

        // Generar un ID único para la canción
        $id = uniqid('cancion_', true);

        // Crear la nueva entrada de canción con ID único
        $nueva_cancion = array(
            'id' => $id,  // Añadir el ID único
            'titulo' => $titulo,
            'artista' => $artista,
            'descripcion' => $descripcion, // Esto también se guarda en el JSON por si acaso
            'archivoMusica' => $musica_file,
            'archivoCaratula' => $caratula_file,
            'archivoTexto' => $text_file // Guardar el archivo de texto (subido o creado)
        );

        // Añadir la nueva canción al array de canciones
        array_push($canciones, $nueva_cancion);

        // Guardar el archivo JSON actualizado
        file_put_contents($json_file, json_encode($canciones, JSON_PRETTY_PRINT));

        // Redirigir a la página de éxito o de listado
        header('Location: ../../listarcanciones.html');
        exit();
    } else {
        echo "No se han enviado los archivos correctamente.";
    }
}
?>
