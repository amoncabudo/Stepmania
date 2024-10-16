document.addEventListener('DOMContentLoaded', function () {
    const pagePath = window.location.pathname;

    // Para la página de edición de canciones
    if (pagePath.includes('editarcanciones.html')) {
        const urlParams = new URLSearchParams(window.location.search);
        const songId = urlParams.get('id');

        if (songId) {
            fetch('src/PHP/playlist.json')
                .then(response => response.json())
                .then(data => {
                    const cancion = data.find(song => song.id === songId);
                    if (cancion) {
                        document.getElementById('songId').value = cancion.id;
                        document.getElementById('titulo').value = cancion.titulo;
                        document.getElementById('artista').value = cancion.artista;

                        // Mostrar la carátula actual
                        if (cancion.archivoCaratula) {
                            document.getElementById('caratulaPreview').src = `../PHP/${cancion.archivoCaratula}`;
                        }

                        // Configurar el audio actual
                        if (cancion.archivoMusica) {
                            document.getElementById('audioPreview').src = `../PHP/${cancion.archivoMusica}`;
                        }
                    }
                })
                .catch(error => console.error('Error al cargar la canción:', error));
        }

        // Previsualización de la carátula
        document.getElementById('caratula').addEventListener('change', function (e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('caratulaPreview').src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Previsualización del archivo de música
        document.getElementById('musica-btn').addEventListener('change', function (e) {
            if (this.files && this.files[0]) {
                const audioPreview = document.getElementById('audioPreview');
                const fileURL = URL.createObjectURL(this.files[0]);
                audioPreview.src = fileURL;
                audioPreview.load();
            }
        });

        // Enviar el formulario de editar canciones
        document.getElementById('editSongForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('src/PHP/editarcanciones.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Canción actualizada exitosamente');
                        window.location.href = 'listarcanciones.html';
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error al actualizar la canción:', error);
                    alert('Error al actualizar la canción');
                });
        });
    }

    // Para la página de validación de subida de canciones (song-form)
    if (pagePath.includes('agregar_canciones.html')) {
        document.getElementById('song-form').addEventListener('submit', function (event) {
            const fileInput = document.getElementById('fitxer-txt');
            const textareaInput = document.getElementById('textarea');
            const messageDiv = document.getElementById('message');

            let fileUploaded = fileInput.files.length > 0;
            let textareaFilled = textareaInput.value.trim() !== '';

            // Validar que no se use el archivo y el textarea al mismo tiempo
            if (fileUploaded && textareaFilled) {
                event.preventDefault();
                messageDiv.textContent = "Error: No puede subir un archivo TXT y rellenar el textarea al mismo tiempo.";
                messageDiv.style.color = 'red';
                return;
            }

            // Validación del archivo TXT
            if (fileUploaded) {
                const file = fileInput.files[0];
                if (!file.name.endsWith('.txt')) {
                    event.preventDefault();
                    messageDiv.textContent = "Error: Solo se permiten archivos TXT.";
                    messageDiv.style.color = 'red';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    const content = e.target.result;
                    if (!validarDatos(content)) {
                        event.preventDefault();
                        messageDiv.textContent = "Error: El archivo TXT contiene datos inválidos.";
                        messageDiv.style.color = 'red';
                    }
                };
                reader.readAsText(file);
            }

            // Validación del textarea
            if (textareaFilled) {
                if (!validarDatos(textareaInput.value)) {
                    event.preventDefault();
                    messageDiv.textContent = "Error: Los datos introducidos en el textarea son inválidos.";
                    messageDiv.style.color = 'red';
                    return;
                }
            }
        });
    }

    function validarDatos(content) {
        const lines = content.trim().split('\n');

        for (let line of lines) {
            const [instantInicial, instantFinal] = line.split(',').map(Number);

            if (isNaN(instantInicial) || isNaN(instantFinal) || instantInicial < 0 || instantFinal < 0 || instantInicial >= instantFinal) {
                return false;
            }
        }
        return true;
    }

    // Para la página de ranking
    if (pagePath.includes('clasificaciones.html')) {
        loadRanking();
    }

    // Cargar ranking desde puntuaciones.json
    async function loadRanking() {
        try {
            const response = await fetch('/src/PHP/puntuaciones.json');
            if (!response.ok) {
                throw new Error('Error al cargar el archivo JSON');
            }

            const ranking = await response.json();
            const rankingBody = document.getElementById('rankingBody');
            ranking.sort((a, b) => b.puntuacion - a.puntuacion);

            ranking.forEach((player, index) => {
                const row = document.createElement('tr');

                let rankingText;
                if (index === 0) {
                    rankingText = `<span style="color: gold;">1</span>`;
                } else {
                    rankingText = index + 1;
                }

                row.innerHTML = `
                    <td>${rankingText}</td>
                    <td>${player.nombre}</td>
                    <td>${player.puntuacion}</td>
                    <td>${player.fecha}</td>
                `;

                rankingBody.appendChild(row);
            });

            if (ranking.length === 0) {
                const noPlayersRow = document.createElement('tr');
                noPlayersRow.innerHTML = `<td colspan="3">No hay jugadores en el ranking</td>`;
                rankingBody.appendChild(noPlayersRow);
            }
        } catch (error) {
            console.error('Error al cargar el ranking:', error);
        }
    }

    // Para la página de playlist (listarcanciones.html)
    if (pagePath.includes('listarcanciones.html')) {
        fetch('src/PHP/playlist.json')
            .then(response => response.json())
            .then(data => {
                const songList = document.getElementById('songList');

                data.forEach((cancion) => {
                    if (!cancion.archivoMusica) {
                        console.error(`No se ha definido archivoMusica para la canción: ${cancion.titulo}`);
                        alert(`No se ha definido archivoMusica para la canción: ${cancion.titulo}`);
                        return;
                    }

                    const songItem = document.createElement('div');
                    songItem.classList.add('play-song-item');
                    const caratulaPath = 'src/PHP/' + cancion.archivoCaratula;

                    songItem.style.backgroundImage = `url('${caratulaPath}')`;

                    const caratula = document.createElement('img');
                    caratula.src = caratulaPath;
                    caratula.alt = `Carátula de ${cancion.titulo}`;
                    caratula.classList.add('song-img');

                    const songInfo = document.createElement('div');
                    songInfo.classList.add('play-song-info');
                    songInfo.innerHTML = `<h2>${cancion.titulo}</h2><p>by ${cancion.artista}</p>`;

                    const playButton = document.createElement('button');
                    playButton.innerHTML = '<i class="fa-solid fa-play"></i>';
                    playButton.classList.add('play-button');
                    playButton.addEventListener('click', () => {
                        playSong(cancion.archivoMusica, cancion.titulo, cancion.artista);
                    });

                    const editButton = document.createElement('button');
                    editButton.innerHTML = '<i class="fa-solid fa-pen"></i>';
                    editButton.classList.add('edit-button');
                    editButton.addEventListener('click', () => {
                        window.location.href = `editarcanciones.html?id=${cancion.id}`;
                    });

                    const deleteButton = document.createElement('button');
                    deleteButton.innerHTML = '<i class="fa-solid fa-trash"></i>';
                    deleteButton.classList.add('delete-button');
                    deleteButton.addEventListener('click', () => {
                        if (confirm(`¿Estás seguro de que quieres eliminar la canción "${cancion.titulo}"?`)) {
                            eliminarCancion(cancion.id);
                        }
                    });

                    const buttonContainer = document.createElement('div');
                    buttonContainer.classList.add('button-container');
                    buttonContainer.appendChild(playButton);
                    buttonContainer.appendChild(editButton);
                    buttonContainer.appendChild(deleteButton);

                    songItem.appendChild(caratula);
                    songItem.appendChild(songInfo);
                    songItem.appendChild(buttonContainer);
                    songList.appendChild(songItem);
                });
            })
            .catch(error => console.error('Error al cargar el JSON:', error));
    }

    // Función para eliminar una canción
    function eliminarCancion(id) {
        fetch(`src/PHP/eliminar_cancion.php?id=${id}`, {
            method: 'DELETE',
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Canción eliminada exitosamente');
                    location.reload(); // Recargar la página para reflejar los cambios
                } else {
                    alert('Error al eliminar la canción');
                }
            })
            .catch(error => console.error('Error:', error));
    }
});

function playSong(archivoMusica, titulo, artista) {
    const gameUrl = `play.html?song=${encodeURIComponent(archivoMusica)}&name=${encodeURIComponent(titulo)}&artist=${encodeURIComponent(artista)}`;
    window.location.href = gameUrl;
}
