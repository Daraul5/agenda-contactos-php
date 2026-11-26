<?php
/**
 * guardar.php
 * Procesa la inserción de un nuevo contacto en la Base de Datos - CREATE (Lógica)
 * Mini Proyecto 2 - Unidad 4: Programación Web
 */
require_once __DIR__ . '/config/db.php';

// 1. Restringir el acceso exclusivo al método HTTP POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// 2. Validación de Seguridad: Token CSRF
if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlash('danger', 'Error de seguridad: La solicitud ha caducado o el token CSRF es inválido.');
    header('Location: crear.php');
    exit;
}

// 3. Captura y limpieza de entradas (Server-Side Sanitization)
$nombre    = trim($_POST['nombre'] ?? '');
$apellido  = trim($_POST['apellido'] ?? '');
$telefono  = trim($_POST['telefono'] ?? '');
$email     = trim($_POST['email'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$notas     = trim($_POST['notas'] ?? '');

$errores = [];

// 4. Validaciones obligatorias en el servidor (Server-Side Validation)
if (empty($nombre)) {
    $errores[] = 'El campo Nombre es obligatorio.';
} elseif (mb_strlen($nombre) > 100) {
    $errores[] = 'El Nombre no debe exceder los 100 caracteres.';
}

if (empty($apellido)) {
    $errores[] = 'El campo Apellido es obligatorio.';
} elseif (mb_strlen($apellido) > 100) {
    $errores[] = 'El Apellido no debe exceder los 100 caracteres.';
}

if (empty($telefono)) {
    $errores[] = 'El campo Teléfono es obligatorio.';
} elseif (!preg_match('/^[0-9\+\-\s\(\)]{7,20}$/', $telefono)) {
    $errores[] = 'El formato del Teléfono no es válido (debe contener entre 7 y 20 caracteres numéricos).';
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'La dirección de correo electrónico ingresada no es válida.';
}

// 5. Validación y Procesamiento de la Fotografía (Requisito Obligatorio)
$nombreFotoGuardada = null;

if (!isset($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) {
    $errores[] = 'Debe seleccionar una fotografía obligatoria para el contacto.';
} elseif ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    $errores[] = 'Ocurrió un error al cargar la fotografía en el servidor (Código: ' . $_FILES['foto']['error'] . ').';
} else {
    // Validar tamaño máximo (3 MB = 3 * 1024 * 1024 bytes)
    $maxBytes = 3 * 1024 * 1024;
    if ($_FILES['foto']['size'] > $maxBytes) {
        $errores[] = 'La fotografía no debe superar el tamaño máximo permitido de 3 MB.';
    }

    // Validar extensión permitida
    $extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($extension, $extensionesPermitidas, true)) {
        $errores[] = 'Formato de imagen no permitido. Solo se aceptan extensiones: JPG, JPEG, PNG y WEBP.';
    }

    // Validar tipo MIME real del archivo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $_FILES['foto']['tmp_name']);
    finfo_close($finfo);

    $mimesPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mimeType, $mimesPermitidos, true)) {
        $errores[] = 'El archivo subido no es una imagen válida o está dañado.';
    }
}

// 6. Si existen errores de validación, regresar a la vista con alertas y datos previos
if (!empty($errores)) {
    $_SESSION['old_data'] = $_POST;
    setFlash('danger', implode('<br>', $errores));
    header('Location: crear.php');
    exit;
}

// 7. Mover la fotografía cargada con un nombre único y seguro
$directorioDestino = __DIR__ . '/archivos/';
if (!is_dir($directorioDestino)) {
    mkdir($directorioDestino, 0755, true);
}

// Generación de nombre único para prevenir sobreescrituras y path traversal
$nombreFotoGuardada = 'contacto_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $extension;
$rutaCompleta = $directorioDestino . $nombreFotoGuardada;

if (!move_uploaded_file($_FILES['foto']['tmp_name'], $rutaCompleta)) {
    setFlash('danger', 'Error al almacenar el archivo de la fotografía en el servidor.');
    $_SESSION['old_data'] = $_POST;
    header('Location: crear.php');
    exit;
}

// 8. Inserción Segura en la Base de Datos utilizando PDO y Sentencias Preparadas (CREATE)
try {
    $pdo = getDBConnection();
    
    // Consulta SQL con marcadores de posición nombrados (Named Placeholders)
    $sql = "INSERT INTO contactos (nombre, apellido, telefono, foto, email, direccion, notas) 
            VALUES (:nombre, :apellido, :telefono, :foto, :email, :direccion, :notas)";
    
    $stmt = $pdo->prepare($sql);
    
    // Ejecución segura de la sentencia preparada
    $stmt->execute([
        ':nombre'    => $nombre,
        ':apellido'  => $apellido,
        ':telefono'  => $telefono,
        ':foto'      => $nombreFotoGuardada,
        ':email'     => !empty($email) ? $email : null,
        ':direccion' => !empty($direccion) ? $direccion : null,
        ':notas'     => !empty($notas) ? $notas : null,
    ]);

    setFlash('success', '¡Contacto <strong>' . e($nombre . ' ' . $apellido) . '</strong> registrado exitosamente en la agenda!');
    header('Location: index.php');
    exit;

} catch (PDOException $e) {
    // Si falla la inserción en base de datos, eliminamos el archivo subido para no dejar huérfanos
    if (file_exists($rutaCompleta)) {
        unlink($rutaCompleta);
    }
    // Buenas prácticas: Registrar el error técnico en logs y notificar amigablemente al usuario
    error_log("Error SQL al insertar contacto: " . $e->getMessage());
    setFlash('danger', 'Ocurrió un error al procesar el guardado en la base de datos. Intente nuevamente.');
    $_SESSION['old_data'] = $_POST;
    header('Location: crear.php');
    exit;
}
