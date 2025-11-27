<?php
// --- BACKEND ---
$dir = 'fotos';
if (!file_exists($dir)) { @mkdir($dir, 0777, true); }

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['imagenes'])) {
    $total_files = count($_FILES['imagenes']['name']);
    for ($i = 0; $i < $total_files; $i++) {
        $tmpFilePath = $_FILES['imagenes']['tmp_name'][$i];
        if ($tmpFilePath != "") {
            // Limpiamos nombre y aseguramos extensión única
            $nombreLimpio = str_replace(' ', '_', $_FILES['imagenes']['name'][$i]);
            $newFilePath = $dir . "/" . time() . "_" . $i . "_" . $nombreLimpio;
            move_uploaded_file($tmpFilePath, $newFilePath);
        }
    }
    header("Location: index.php");
    exit();
}

$fotos = [];
if (is_dir($dir)) {
    $archivos = scandir($dir);
    foreach ($archivos as $archivo) {
        if ($archivo !== '.' && $archivo !== '..') {
            $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $fotos[] = $dir . '/' . $archivo;
            }
        }
    }
}
// Ordenar fotos (opcional, por defecto alfabético/fecha sistema)
// natsort($fotos); 

$grupos_fotos = array_chunk($fotos, 2);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Mi Álbum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Patrick+Hand&family=Cinzel:wght@700&display=swap" rel="stylesheet">
    
    <style>
        body {
            background-color: #222;
            /* Textura de mesa de madera oscura */
            background-image: repeating-linear-gradient(45deg, #2b1d0e 25%, transparent 25%, transparent 75%, #2b1d0e 75%, #2b1d0e), repeating-linear-gradient(45deg, #2b1d0e 25%, #222 25%, #222 75%, #2b1d0e 75%, #2b1d0e);
            background-position: 0 0, 10px 10px;
            background-size: 20px 20px;
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow-x: hidden;
        }

        .book-stage {
            width: 100%;
            height: 85vh;
            display: flex;
            justify-content: center;
            align-items: center;
            perspective: 2000px;
        }

        #book {
            opacity: 1 !important;
            transition: opacity 0.5s;
        }

        .page {
            background-color: #fdfaf7;
            border: 1px solid #c2b5a3;
            /* Textura de papel sutil */
            background-image: linear-gradient(#fdfaf7 2px, transparent 2px), linear-gradient(90deg, #fdfaf7 2px, transparent 2px), linear-gradient(rgba(0,0,0,.05) 1px, transparent 1px), linear-gradient(90deg, rgba(0,0,0,.05) 1px, transparent 1px);
            background-size: 100px 100px, 100px 100px, 20px 20px, 20px 20px;
            background-position: -2px -2px, -2px -2px, -1px -1px, -1px -1px;
            
            padding: 15px;
            box-shadow: inset -5px 0 20px rgba(0,0,0,0.1); 
            overflow: hidden;
        }

        /* --- NUEVA PORTADA --- */
        .page.--cover {
            /* Efecto Cuero */
            background: radial-gradient(#702e2e, #3e1212);
            color: #d4af37; /* Color Oro */
            border: 4px double #d4af37; /* Borde dorado */
            box-shadow: inset 0 0 50px rgba(0,0,0,0.8);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
        }

        /* Adorno en la portada */
        .page.--cover::before {
            content: '';
            position: absolute;
            top: 15px; left: 15px; right: 15px; bottom: 15px;
            border: 2px dashed #d4af37;
            opacity: 0.5;
            pointer-events: none;
        }

        .page.--cover h1 {
            font-family: 'Cinzel', serif; /* Fuente elegante */
            font-size: 2.5rem;
            text-shadow: 2px 2px 4px black;
            margin-bottom: 20px;
        }

        .page-content {
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            align-items: center;
            height: 100%;
        }

        .polaroid {
            background: white;
            padding: 8px 8px 40px 8px;
            box-shadow: 3px 3px 10px rgba(0,0,0,0.3);
            text-align: center;
            width: 85%;
            transition: transform 0.3s;
            position: relative;
        }
        
        /* Cinta adhesiva falsa */
        .polaroid::before {
            content: '';
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 25px;
            background-color: rgba(255, 255, 255, 0.4);
            border: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            transform: translateX(-50%) rotate(-2deg);
        }

        .polaroid:nth-child(even) { transform: rotate(2deg); }
        .polaroid:nth-child(odd) { transform: rotate(-2deg); }
        .polaroid:hover { transform: scale(1.03) rotate(0deg); z-index: 10; }

        .polaroid img {
            width: 100%; 
            height: 160px; /* Ajuste para móviles */
            object-fit: cover;
            border: 1px solid #eee;
            display: block;
        }
        
        .caption {
            font-family: 'Patrick Hand', cursive;
            color: #333;
            font-size: 1.2rem;
            margin-top: 8px;
            line-height: 1;
        }

        .controls {
            position: fixed;
            bottom: 20px;
            z-index: 1000;
            background: rgba(255,255,255,0.9);
            padding: 10px 20px;
            border-radius: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            backdrop-filter: blur(5px);
        }
    </style>
</head>
<body>

    <div class="book-stage">
        <div id="book">
            <div class="page --cover">
                <h1>Mis Recuerdos</h1>
                <p style="font-family: 'Patrick Hand'; font-size: 1.5rem; color: #eecda3;">Colección 2025</p>
                <div style="font-size: 2rem;">❦</div>
            </div>

            <?php if (!empty($grupos_fotos)): ?>
                <?php foreach($grupos_fotos as $index => $grupo): ?>
                    <div class="page">
                        <div class="page-content">
                            <div class="polaroid">
                                <img src="<?php echo $grupo[0]; ?>" alt="Recuerdo">
                                <div class="caption">Recuerdo <?php echo ($index * 2) + 1; ?></div>
                            </div>
                            
                            <?php if(isset($grupo[1])): ?>
                                <div class="polaroid">
                                    <img src="<?php echo $grupo[1]; ?>" alt="Recuerdo">
                                    <div class="caption">Recuerdo <?php echo ($index * 2) + 2; ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="page">
                    <div class="d-flex align-items-center justify-content-center h-100 flex-column text-muted">
                        <h3>Álbum Vacío</h3>
                        <p>Agrega tus primeras fotos abajo</p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="page --cover">
                <br><br><br>
                <h3>Fin</h3>
                <small style="color: #eecda3;">Hecho con ❤️</small>
            </div>
        </div>
    </div>

    <div class="controls">
        <form action="" method="post" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
            <input type="file" name="imagenes[]" class="form-control form-control-sm" multiple accept="image/*" required>
            <button type="submit" class="btn btn-dark btn-sm rounded-pill">Subir Fotos</button>
        </form>
    </div>

    <script src="https://unpkg.com/page-flip@2.0.7/dist/js/page-flip.browser.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let LibraryObj = null;
            if (typeof St !== 'undefined') LibraryObj = St;
            else if (typeof PageFlip !== 'undefined') LibraryObj = PageFlip;
            else if (typeof StPageFlip !== 'undefined') LibraryObj = StPageFlip;

            if (!LibraryObj) return;

            const bookElement = document.getElementById('book');
            
            // --- LÓGICA RESPONSIVE ---
            // Detectamos el ancho de la pantalla
            const screenWidth = window.innerWidth;
            let bookWidth, bookHeight;

            if (screenWidth < 768) {
                // MÓVIL: Hacemos el libro más pequeño para que quepa la hoja
                // Restamos un poco para márgenes
                bookWidth = screenWidth * 0.90; 
                bookHeight = window.innerHeight * 0.7; // 70% del alto de pantalla
            } else {
                // PC: Tamaño estándar
                bookWidth = 450;
                bookHeight = 600;
            }

            const pageFlip = new LibraryObj.PageFlip(bookElement, {
                width: bookWidth, 
                height: bookHeight,
                size: 'fixed', 
                // Estos límites ayudan a que no se rompa al redimensionar
                minWidth: 200,
                maxWidth: 1000,
                minHeight: 300,
                maxHeight: 1200,
                maxShadowOpacity: 0.5,
                showCover: true,
                // Activamos scroll en móviles para que sea más natural si el dedo toca fuera
                mobileScrollSupport: false 
            });

            pageFlip.loadFromHTML(document.querySelectorAll('.page'));
        });
    </script>
</body>
</html>
