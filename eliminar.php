<?php
/**
 * eliminar.php
 * Confirmación visual y procesamiento seguro de borrado - DELETE
 * Mini Proyecto 2 - Unidad 4: Programación Web
 */
require_once __DIR__ . '/config/db.php';

// Captura de ID desde GET o POST
$id = filter_input(
    $_SERVER['REQUEST_METHOD'] === 'POST' ? INPUT_POST : INPUT_GET, 
    'id', 
    FILTER_VALIDATE_INT
);

if (!$id || $id <= 0) {
    setFlash('danger', 'Identificador de contacto no válido.');
    header('Location: index.php');
    exit;
}

// --------------------------------------------------------------------
// CASO 1: PROCESAMIENTO VÍA HTTP POST (ELIMINACIÓN DEFINITIVA)
// --------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validación de Token CSRF
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Error de seguridad: Solicitud de eliminación no autorizada o token CSRF inválido.');
        header('Location: index.php');
        exit;
    }

    try {
        $pdo = getDBConnection();

        // 1. Obtener la foto asociada antes de eliminar el registro
        $stmtFoto = $pdo->prepare("SELECT nombre, apellido, foto FROM contactos WHERE id = :id LIMIT 1");
        $stmtFoto->execute([':id' => $id]);
        $contacto = $stmtFoto->fetch();

        if (!$contacto) {
            setFlash('warning', 'El contacto que intentas eliminar ya no existe.');
            header('Location: index.php');
            exit;
        }

        // 2. Sentencia preparada DELETE (Especificación 7)
        $sql = "DELETE FROM contactos WHERE id = :id";
        $stmtDelete = $pdo->prepare($sql);
        $stmtDelete->execute([':id' => $id]);

        // 3. Limpieza física del archivo de imagen en el servidor
        $foto = $contacto['foto'];
        $fotosProtegidas = ['default.png', 'contacto1.png', 'contacto2.png', 'contacto3.png'];
        if (!empty($foto) && !in_array($foto, $fotosProtegidas, true)) {
            $rutaArchivo = __DIR__ . '/archivos/' . $foto;
            if (file_exists($rutaArchivo)) {
                unlink($rutaArchivo);
            }
        }

        setFlash('success', 'El contacto <strong>' . e($contacto['nombre'] . ' ' . $contacto['apellido']) . '</strong> ha sido eliminado exitosamente.');
        header('Location: index.php');
        exit;

    } catch (PDOException $e) {
        error_log("Error SQL al eliminar contacto: " . $e->getMessage());
        setFlash('danger', 'Error al intentar eliminar el registro de la base de datos.');
        header('Location: index.php');
        exit;
    }
}

// --------------------------------------------------------------------
// CASO 2: VISTA DE CONFIRMACIÓN PREVIA VÍA HTTP GET
// --------------------------------------------------------------------
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM contactos WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $contacto = $stmt->fetch();

    if (!$contacto) {
        setFlash('warning', 'El contacto solicitado no fue encontrado.');
        header('Location: index.php');
        exit;
    }

} catch (PDOException $e) {
    error_log("Error al consultar contacto para confirmación: " . $e->getMessage());
    setFlash('danger', 'Error de conexión al procesar la confirmación.');
    header('Location: index.php');
    exit;
}

$pageTitle = 'Confirmar Eliminación: ' . $contacto['nombre'] . ' ' . $contacto['apellido'];

$rutaFoto = 'archivos/' . $contacto['foto'];
if (empty($contacto['foto']) || !file_exists(__DIR__ . '/' . $rutaFoto)) {
    $rutaFoto = 'archivos/default.png';
}

include __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">

        <div class="card card-custom border-danger shadow">
            
            <div class="card-header bg-danger text-white py-3 d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-diamond-fill fs-4"></i>
                <h4 class="card-title mb-0 fw-bold">Confirmación de Eliminación</h4>
            </div>

            <div class="card-body p-4 text-center">
                
                <div class="mb-3">
                    <img src="<?= e($rutaFoto); ?>" 
                         alt="<?= e($contacto['nombre'] . ' ' . $contacto['apellido']); ?>" 
                         class="contact-avatar-lg border-danger shadow-sm">
                </div>

                <h4 class="fw-bold text-dark mb-1">
                    ¿Está seguro de eliminar a <?= e($contacto['nombre'] . ' ' . $contacto['apellido']); ?>?
                </h4>
                
                <p class="text-muted mb-3">
                    Esta acción es irreversible y eliminará todos los datos asociados, incluyendo el teléfono 
                    <strong><?= e($contacto['telefono']); ?></strong> y su fotografía del servidor.
                </p>

                <div class="alert alert-warning py-2 small d-inline-block text-start border-0">
                    <i class="bi bi-info-circle-fill me-1"></i>
                    <strong>Registro ID:</strong> #<?= (int)$contacto['id']; ?> |
                    <strong>Teléfono:</strong> <?= e($contacto['telefono']); ?>
                </div>

                <!-- Formulario de Envío Seguro por POST con Token CSRF -->
                <form action="eliminar.php" method="POST" class="mt-4 pt-3 border-top d-flex justify-content-center gap-3">
                    
                    <input type="hidden" name="csrf_token" value="<?= getCsrfToken(); ?>">
                    <input type="hidden" name="id" value="<?= (int)$contacto['id']; ?>">

                    <a href="index.php" class="btn btn-secondary px-4 fw-semibold">
                        <i class="bi bi-x-circle me-1"></i> Cancelar
                    </a>

                    <button type="submit" class="btn btn-danger px-4 fw-semibold shadow-sm">
                        <i class="bi bi-trash3-fill me-1"></i> Sí, Eliminar Definitivamente
                    </button>

                </form>

            </div>

        </div>

    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
