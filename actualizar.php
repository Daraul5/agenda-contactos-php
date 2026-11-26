<?php
/**
 * actualizar.php
 * Procesa la actualización de los datos de un contacto - UPDATE (Lógica)
 * Mini Proyecto 2 - Unidad 4: Programación Web
 */
require_once __DIR__ . '/config/db.php';

// 1. Exigir método HTTP POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// 2. Validación de Token CSRF
if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlash('danger', 'Error de seguridad: La solicitud ha caducado o el token CSRF es inválido.');
    header('Location: index.php');
    exit;
}

// 3. Captura y sanitización de datos
$id         = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$fotoActual = trim($_POST['foto_actual'] ?? '');
$nombre     = trim($_POST['nombre'] ?? '');
$apellido   = trim($_POST['apellido'] ?? '');
$telefono   = trim($_POST['telefono'] ?? '');
$email      = trim($_POST['email'] ?? '');
$direccion  = trim($_POST['direccion'] ?? '');
$notas      = trim($_POST['notas'] ?? '');

if (!$id || $id <= 0) {
    setFlash('danger', 'Identificador de contacto no válido.');
    header('Location: index.php');
    exit;
}

$errores = [];

// 4. Validaciones obligatorias de servidor
if (empty($nombre)) {
    $errores[] = 'El campo Nombre es obligatorio.';
} elseif (mb_strlen($nombre) > 100) {
    $errores[] = 'El Nombre no debe exceder 100 caracteres.';
}

if (empty($apellido)) {
    $errores[] = 'El campo Apellido es obligatorio.';
} elseif (mb_strlen($apellido) > 100) {
    $errores[] = 'El Apellido no debe exceder 100 caracteres.';
}

if (empty($telefono)) {
    $errores[] = 'El campo Teléfono es obligatorio.';
} elseif (!preg_match('/^[0-9\+\-\s\(\)]{7,20}$/', $telefono)) {
    $errores[] = 'El Teléfono debe contener entre 7 y 20 dígitos numéricos.';
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'La dirección de correo electrónico no es válida.';
}

// 5. Manejo del reemplazo de fotografía (Opcional en UPDATE)
$fotoFinal = $fotoActual;
$nuevoArchivoSubido = false;
$rutaNuevaFoto = null;

if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $maxBytes = 3 * 1024 * 1024; // 3 MB
    if ($_FILES['foto']['size'] > $maxBytes) {
        $errores[] = 'La nueva fotografía no debe exceder 3 MB de tamaño.';
    }

    $extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($extension, $extensionesPermitidas, true)) {
        $errores[] = 'Formato no permitido. Solo se aceptan extensiones JPG, PNG o WEBP.';
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $_FILES['foto']['tmp_name']);
    finfo_close($finfo);

    $mimesPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mimeType, $mimesPermitidos, true)) {
        $errores[] = 'El archivo subido no contiene un formato de imagen verificado.';
    }

    if (empty($errores)) {
        $directorioDestino = __DIR__ . '/archivos/';
        $nombreFotoNueva = 'contacto_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $extension;
        $rutaNuevaFoto = $directorioDestino . $nombreFotoNueva;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaNuevaFoto)) {
            $fotoFinal = $nombreFotoNueva;
            $nuevoArchivoSubido = true;
        } else {
            $errores[] = 'No se pudo almacenar la nueva fotografía en el servidor.';
        }
    }
}

// 6. Retornar con errores si falló alguna validación
if (!empty($errores)) {
    setFlash('danger', implode('<br>', $errores));
    header('Location: editar.php?id=' . $id);
    exit;
}

// 7. Ejecución de la sentencia UPDATE con PDO (Especificación 6)
try {
    $pdo = getDBConnection();

    $sql = "UPDATE contactos 
            SET nombre = :nombre,
                apellido = :apellido,
                telefono = :telefono,
                foto = :foto,
                email = :email,
                direccion = :direccion,
                notas = :notas
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre'    => $nombre,
        ':apellido'  => $apellido,
        ':telefono'  => $telefono,
        ':foto'      => $fotoFinal,
        ':email'     => !empty($email) ? $email : null,
        ':direccion' => !empty($direccion) ? $direccion : null,
        ':notas'     => !empty($notas) ? $notas : null,
        ':id'        => $id
    ]);

    // Si se subió con éxito una nueva foto, eliminar la anterior si no es un archivo de plantilla/default
    if ($nuevoArchivoSubido && !empty($fotoActual)) {
        $fotosProtegidas = ['default.png', 'contacto1.png', 'contacto2.png', 'contacto3.png'];
        if (!in_array($fotoActual, $fotosProtegidas, true)) {
            $rutaFotoVieja = __DIR__ . '/archivos/' . $fotoActual;
            if (file_exists($rutaFotoVieja)) {
                unlink($rutaFotoVieja);
            }
        }
    }

    setFlash('success', '¡El contacto <strong>' . e($nombre . ' ' . $apellido) . '</strong> fue actualizado correctamente!');
    header('Location: ver.php?id=' . $id);
    exit;

} catch (PDOException $e) {
    // Si la actualización en base de datos falla, retirar la foto recién subida
    if ($nuevoArchivoSubido && file_exists($rutaNuevaFoto)) {
        unlink($rutaNuevaFoto);
    }
    error_log("Error SQL al actualizar contacto: " . $e->getMessage());
    setFlash('danger', 'Ocurrió un error al actualizar el contacto en la base de datos.');
    header('Location: editar.php?id=' . $id);
    exit;
}
