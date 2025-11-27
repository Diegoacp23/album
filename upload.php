<?php
// upload.php
if (!file_exists('fotos')) {
    mkdir('fotos', 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['imagen'])) {
    $target_dir = "fotos/";
    $target_file = $target_dir . basename($_FILES["imagen"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validaciones simples
    $check = getimagesize($_FILES["imagen"]["tmp_name"]);
    if($check !== false) {
        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
            echo "La imagen ". htmlspecialchars( basename( $_FILES["imagen"]["name"])). " ha sido subida.";
        } else {
            echo "Hubo un error al subir tu archivo.";
        }
    } else {
        echo "El archivo no es una imagen.";
    }
    // Redireccionar al álbum
    header("Location: index.php");
    exit();
}
?>