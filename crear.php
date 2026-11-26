<?php
/**
 * crear.php
 * Formulario de creación de contacto - CREATE (Vista)
 * Mini Proyecto 2 - Unidad 4: Programación Web
 */
$pageTitle = 'Nuevo Contacto';
require_once __DIR__ . '/config/db.php';

// Recuperar datos previos en caso de error de validación
$old = $_SESSION['old_data'] ?? [];
unset($_SESSION['old_data']);

include __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        
        <!-- Tarjeta del Formulario -->
        <div class="card card-custom border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary p-2 rounded-3">
                        <i class="bi bi-person-plus-fill fs-5"></i>
                    </span>
                    <div>
                        <h4 class="card-title fw-bold mb-0">Agregar Nuevo Contacto</h4>
                        <small class="text-muted">Complete los campos obligatorios para registrar en la agenda</small>
                    </div>
                </div>
                <a href="index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Regresar
                </a>
            </div>

            <div class="card-body p-4">
                
                <!-- Formulario POST con enctype multipart para subida de archivos -->
                <form action="guardar.php" method="POST" enctype="multipart/form-data" novalidate class="needs-validation">
                    
                    <!-- Token CSRF para seguridad de formularios -->
                    <input type="hidden" name="csrf_token" value="<?= getCsrfToken(); ?>">

                    <!-- Sección de Fotografía (Obligatoria) -->
                    <div class="text-center mb-4">
                        <div class="photo-preview-container">
                            <img id="preview-image" src="archivos/default.png" alt="Previsualización de foto">
                        </div>
                        <label for="foto" class="btn btn-sm btn-outline-primary cursor-pointer">
                            <i class="bi bi-camera-fill me-1"></i> Seleccionar Fotografía (Obligatorio)
                        </label>
                        <input type="file" 
                               name="foto" 
                               id="foto" 
                               class="d-none" 
                               accept="image/png, image/jpeg, image/webp" 
                               required>
                        <div class="form-text small text-muted">Formatos permitidos: JPG, PNG o WEBP. Máximo 3MB.</div>
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
                                       value="<?= e($old['nombre'] ?? ''); ?>" 
                                       required 
                                       maxlength="100" 
                                       placeholder="Ej. Roberto">
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
                                       value="<?= e($old['apellido'] ?? ''); ?>" 
                                       required 
                                       maxlength="100" 
                                       placeholder="Ej. Gómez Bolaños">
                            </div>
                        </div>
                    </div>

                    <!-- Fila 2: Teléfono y Correo Electrónico -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="telefono" class="form-label fw-semibold">Teléfono <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-telephone"></i></span>
                                <input type="tel" 
                                       class="form-control" 
                                       id="telefono" 
                                       name="telefono" 
                                       value="<?= e($old['telefono'] ?? ''); ?>" 
                                       required 
                                       maxlength="20" 
                                       pattern="[0-9\+\-\s\(\)]{7,20}" 
                                       placeholder="Ej. 961 123 4567">
                            </div>
                            <div class="form-text small">Mínimo 7 dígitos numéricos.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold">Correo Electrónico <span class="text-muted fw-normal">(Opcional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?= e($old['email'] ?? ''); ?>" 
                                       maxlength="150" 
                                       placeholder="nombre@ejemplo.com">
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
                                   value="<?= e($old['direccion'] ?? ''); ?>" 
                                   maxlength="255" 
                                   placeholder="Calle, número, colonia, ciudad">
                        </div>
                    </div>

                    <!-- Fila 4: Notas Adicionales -->
                    <div class="mb-4">
                        <label for="notas" class="form-label fw-semibold">Notas / Observaciones <span class="text-muted fw-normal">(Opcional)</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-journal-text"></i></span>
                            <textarea class="form-control" 
                                      id="notas" 
                                      name="notas" 
                                      rows="3" 
                                      placeholder="Anotaciones relevantes sobre el contacto..."><?= e($old['notas'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="index.php" class="btn btn-light border px-4">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Guardar Contacto
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
