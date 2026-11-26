<?php
/**
 * editar.php
 * Formulario de edición de contacto existente - UPDATE (Vista)
 * Mini Proyecto 2 - Unidad 4: Programación Web
 */
$pageTitle = 'Editar Contacto';
require_once __DIR__ . '/config/db.php';

// 1. Validación de ID recibido por GET
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    setFlash('danger', 'Identificador de contacto inválido.');
    header('Location: index.php');
    exit;
}

// 2. Consulta de los datos actuales del contacto mediante PDO (READ individual previo a UPDATE)
try {
    $pdo = getDBConnection();
    $sql = "SELECT * FROM contactos WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $contacto = $stmt->fetch();

    if (!$contacto) {
        setFlash('warning', 'El contacto a editar no existe en el sistema.');
        header('Location: index.php');
        exit;
    }

} catch (PDOException $e) {
    error_log("Error al cargar contacto para edición: " . $e->getMessage());
    setFlash('danger', 'Error de base de datos al cargar el contacto.');
    header('Location: index.php');
    exit;
}

// Ruta de la imagen actual
$rutaFoto = 'archivos/' . $contacto['foto'];
if (empty($contacto['foto']) || !file_exists(__DIR__ . '/' . $rutaFoto)) {
    $rutaFoto = 'archivos/default.png';
}

include __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">

        <!-- Tarjeta del Formulario de Edición -->
        <div class="card card-custom border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning-subtle text-warning-emphasis p-2 rounded-3">
                        <i class="bi bi-pencil-square fs-5"></i>
                    </span>
                    <div>
                        <h4 class="card-title fw-bold mb-0">Modificar Contacto #<?= (int)$contacto['id']; ?></h4>
                        <small class="text-muted">Actualice la información y guarde los cambios</small>
                    </div>
                </div>
                <a href="ver.php?id=<?= (int)$contacto['id']; ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Cancelar
                </a>
            </div>

            <div class="card-body p-4">

                <!-- Formulario POST que envía hacia actualizar.php -->
                <form action="actualizar.php" method="POST" enctype="multipart/form-data" novalidate class="needs-validation">

                    <!-- Parámetros Ocultos de Seguridad y Control -->
                    <input type="hidden" name="csrf_token" value="<?= getCsrfToken(); ?>">
                    <input type="hidden" name="id" value="<?= (int)$contacto['id']; ?>">
                    <input type="hidden" name="foto_actual" value="<?= e($contacto['foto']); ?>">

                    <!-- Fotografía Actual y Reemplazo -->
                    <div class="text-center mb-4">
                        <div class="photo-preview-container">
                            <img id="preview-image" src="<?= e($rutaFoto); ?>" alt="Fotografía actual">
                        </div>
                        <label for="foto" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-camera me-1"></i> Cambiar Fotografía (Opcional)
                        </label>
                        <input type="file" 
                               name="foto" 
                               id="foto" 
                               class="d-none" 
                               accept="image/png, image/jpeg, image/webp">
                        <div class="form-text small text-muted">
                            Si no selecciona un archivo nuevo, se mantendrá la fotografía actual: <code><?= e($contacto['foto']); ?></code>
                        </div>
                    </div>

                    <!-- Fila 1: Nombre y Apellido -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="nombre" class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                                <input type="text" 
                                       class="form-control" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="<?= e($contacto['nombre']); ?>" 
                                       required 
                                       maxlength="100">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="apellido" class="form-label fw-semibold">Apellido <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-person-badge"></i></span>
                                <input type="text" 
                                       class="form-control" 
                                       id="apellido" 
                                       name="apellido" 
                                       value="<?= e($contacto['apellido']); ?>" 
                                       required 
                                       maxlength="100">
                            </div>
                        </div>
                    </div>

                    <!-- Fila 2: Teléfono y Correo -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="telefono" class="form-label fw-semibold">Teléfono <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-telephone"></i></span>
                                <input type="tel" 
                                       class="form-control" 
                                       id="telefono" 
                                       name="telefono" 
                                       value="<?= e($contacto['telefono']); ?>" 
                                       required 
                                       maxlength="20" 
                                       pattern="[0-9\+\-\s\(\)]{7,20}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold">Correo Electrónico <span class="text-muted fw-normal">(Opcional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?= e($contacto['email']); ?>" 
                                       maxlength="150">
                            </div>
                        </div>
                    </div>

                    <!-- Fila 3: Dirección Física -->
                    <div class="mb-3">
                        <label for="direccion" class="form-label fw-semibold">Dirección <span class="text-muted fw-normal">(Opcional)</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-geo-alt"></i></span>
                            <input type="text" 
                                   class="form-control" 
                                   id="direccion" 
                                   name="direccion" 
                                   value="<?= e($contacto['direccion']); ?>" 
                                   maxlength="255">
                        </div>
                    </div>

                    <!-- Fila 4: Notas -->
                    <div class="mb-4">
                        <label for="notas" class="form-label fw-semibold">Notas / Observaciones <span class="text-muted fw-normal">(Opcional)</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-journal-text"></i></span>
                            <textarea class="form-control" 
                                      id="notas" 
                                      name="notas" 
                                      rows="3"><?= e($contacto['notas']); ?></textarea>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <a href="eliminar.php?id=<?= (int)$contacto['id']; ?>" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-trash3 me-1"></i> Eliminar Registro
                        </a>
                        <div class="d-flex gap-2">
                            <a href="ver.php?id=<?= (int)$contacto['id']; ?>" class="btn btn-light border px-4">Cancelar</a>
                            <button type="submit" class="btn btn-warning px-4 fw-semibold shadow-sm">
                                <i class="bi bi-arrow-repeat me-1"></i> Actualizar Contacto
                            </button>
                        </div>
                    </div>

                </form>

            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
