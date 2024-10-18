class Game {
    /*Declaracion de variables*/
    constructor() {
        this.playerName = ''; 
        this.elements = [];
        this.activeElements = new Set();
        this.score = 0;
        this.totalNotes = 0;
        this.correctHits = 0;
        this.wrongHits = 0;
        this.startTime = null;
        this.elementsContainer = document.getElementById('elementsContainer');
        this.gameStatus = document.getElementById('gameStatus');
        this.songInfo = document.getElementById('songInfo');
        this.songName = document.getElementById('songName');
        this.artistName = document.getElementById('artistName');
        this.songDuration = document.getElementById('songDuration');
        this.progressBar = document.getElementById('progressBar');
        this.isGameStarted = false;
        this.spawnRate = 800;
        this.lastSpawnTime = 0;
        this.approachRate = 2000;
        this.audio = document.getElementById('gameSong');

        // Escuchar teclas en cualquier parte del juego
        document.addEventListener('keydown', (e) => this.handleKeyPress(e.keyCode));
    }
    /*Cargar juego, darle al enter para iniciar y preguntar nombre */
    init() {
        this.loadSongFromParams();
        this.updateSongInfo();

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !this.isGameStarted) {
                this.askPlayerName(); // Preguntar nombre antes de empezar
            }
        });

        this.audio.addEventListener('timeupdate', () => {
            this.updateProgressBar();
        });
    }
    /*Cargar cancion en el juego */
    loadSongFromParams() {
        const params = new URLSearchParams(window.location.search);
        const songFile = decodeURIComponent(params.get('song'));
        const songName = decodeURIComponent(params.get('name'));
        const artist = decodeURIComponent(params.get('artist'));

        this.audio.src = `src/c/${songFile}`;
        this.songName.textContent = songName;
        this.artistName.textContent = artist;

        this.audio.addEventListener('loadedmetadata', () => {
            const duration = this.formatTime(this.audio.duration);
            this.songDuration.textContent = duration;
        });
    }

    updateSongInfo() {}

    /*Mostrar formato del tiempo */
    formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60).toString().padStart(2, '0');
        return `${minutes}:${secs}`;
    }
    /*Actualizar barra de progreso de la cancion */
    updateProgressBar() {
        const progress = (this.audio.currentTime / this.audio.duration) * 100;
        this.progressBar.style.width = `${progress}%`;
    }

    /*Preguntar el nombre del usuario antes de empezar el juego, una vez introducido empieza el juego */
    askPlayerName() {
        this.playerName = prompt('Introduce tu nombre para comenzar el juego:');
        if (this.playerName) {
            this.startGame(); 
        } else {
            alert('Por favor, introduce un nombre válido.');
        }
    }

    /*Inicia el juego con los siguientes valores */
    startGame() {
        this.isGameStarted = true;
        this.score = 0;
        this.correctHits = 0;
        this.wrongHits = 0;
        this.totalNotes = 0;
        this.startTime = Date.now();
        this.lastSpawnTime = Date.now();

        this.audio.play(); // Iniciar la canción

        this.audio.addEventListener('ended', () => {
            this.endGame();
        });

        this.gameLoop();
        this.gameStatus.textContent = `Joc iniciat per ${this.playerName}! Prem les tecles quan els cercles arribin a baix!`;
    }
    /*Bucle para empezar o terminar el juego */
    gameLoop() {
        if (!this.isGameStarted) return;

        const currentTime = Date.now();

        // Crear nuevo elemento basado en el spawnRate
        if (currentTime - this.lastSpawnTime >= this.spawnRate) {
            this.createVisualElement();
            this.lastSpawnTime = currentTime;
        }

        const elements = document.getElementsByClassName('element');
        for (let element of elements) {
            const creationTime = parseInt(element.dataset.creationTime);
            const elapsedTime = currentTime - creationTime;
            const progress = elapsedTime / this.approachRate;

            if (progress >= 1) {
                this.missNote(element);
                continue;
            }

            const startY = 0;
            const endY = document.getElementById('gameRectangle').offsetHeight - 100;
            const currentY = startY + (endY - startY) * progress;
            element.style.top = `${currentY}px`;

            const outerCircle = element.querySelector('.outer-circle');
            const scale = 1 + (1 - progress);
            outerCircle.style.transform = `scale(${scale})`;
        }

        requestAnimationFrame(() => this.gameLoop());
    }
    /* Crear un nuevo elemento visual en el juego */
    createVisualElement() {
        const div = document.createElement('div');
        div.className = 'element';
        div.dataset.creationTime = Date.now().toString();

        const outerCircle = document.createElement('div');
        outerCircle.className = 'outer-circle';

        const innerCircle = document.createElement('div');
        innerCircle.className = 'inner-circle';

        const gameRectangle = document.getElementById('gameRectangle');

        const xPos = Math.random() * (gameRectangle.offsetWidth - 100);
        div.style.left = `${xPos}px`;
        div.style.top = `0px`;

        const keyCode = [37, 38, 39, 40][Math.floor(Math.random() * 4)];
        const arrows = { 37: '←', 38: '↑', 39: '→', 40: '↓' };

        innerCircle.textContent = arrows[keyCode];
        this.activeElements.add(keyCode);
        div.dataset.keyCode = keyCode;

        div.appendChild(outerCircle);
        div.appendChild(innerCircle);
        this.elementsContainer.appendChild(div);

        this.totalNotes++;
    }

    handleKeyPress(keyCode) {
        if (![37, 38, 39, 40].includes(keyCode)) return;

        const elements = document.getElementsByClassName('element');
        let foundElement = null;

        for (let element of elements) {
            if (parseInt(element.dataset.keyCode) === keyCode) {
                foundElement = element;
                break;
            }
        }
        // Buscar el elemento que coincide con la tecla presionada

        if (foundElement) {
            this.correctHits++;
            this.score += 100;
            this.showHitFeedback(foundElement, 1);
            foundElement.remove();
            this.activeElements.delete(keyCode);
        } else {
            this.missNote();
        }

        this.gameStatus.textContent = `Puntuació: ${this.score}`;
    }
    /* Manejar una nota fallada */
    missNote(element) {
        if (element) {
            element.remove();
        }
        this.wrongHits++;
        this.score -= 50;
        this.gameStatus.textContent = `Puntuació: ${this.score}`;
    }

    // Enviar los datos al archivo PHP
    saveScore() {
        const data = {
            nombre: this.playerName,
            puntuacion: this.score
        };

        fetch('src/php/guardar_puntuacion.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                console.log('Puntuación guardada exitosamente.');
                this.createCookie('nombre', this.playerName, 7);
                this.createCookie('puntuacion', this.score, 7);
            }
        })
        .catch(error => {
            console.error('Error al guardar la puntuación:', error);
        });
    }

    // Crear una cookie
    createCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }
    /*Funcion para terminar el juego (Guarda la puntuacion, la muestra, y te reenvia al html listarcanciones con 1 segundo de retraso) */
    endGame() {
        this.isGameStarted = false;

        this.saveScore(); 

        this.gameStatus.textContent = `Joc acabat! Puntuació final: ${this.score}. Redirigint a la playlist...`;
        setTimeout(() => {
            alert(`Joc acabat! Has aconseguit ${this.score} punts!`);
            window.location.href = 'listarcanciones.html'; 
        }, 1000); 
    }
    /*Reinicio del juego */
    resetGame() {
        this.score = 0;
        this.correctHits = 0;
        this.wrongHits = 0;
        this.totalNotes = 0;
        this.activeElements.clear();
    }
}

// Inicializar el juego al cargar la página
window.onload = () => {
    const game = new Game();
    game.init();
};
