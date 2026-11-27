<?php
/**
 * ver.php
 * Vista detallada de un contacto individual - READ (Detalle)
 * Mini Proyecto 2 - Unidad 4: Programación Web
 */
require_once __DIR__ . '/config/db.php';

// 1. Validación del parámetro ID por GET (Debe ser un entero positivo)
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    setFlash('danger', 'Identificador de contacto no válido.');
    header('Location: index.php');
    exit;
}

// 2. Consulta con sentencia preparada PDO (Especificación 5)
try {
    $pdo = getDBConnection();
    $sql = "SELECT * FROM contactos WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $contacto = $stmt->fetch();

    if (!$contacto) {
        setFlash('warning', 'El contacto solicitado no fue encontrado en la base de datos.');
        header('Location: index.php');
        exit;
    }

} catch (PDOException $e) {
    error_log("Error al consultar detalle del contacto: " . $e->getMessage());
    setFlash('danger', 'Ocurrió un error al obtener la información del contacto.');
    header('Location: index.php');
    exit;
}

$pageTitle = 'Detalle: ' . $contacto['nombre'] . ' ' . $contacto['apellido'];

// Ruta de fotografía validada
$rutaFoto = 'archivos/' . $contacto['foto'];
if (empty($contacto['foto']) || !file_exists(__DIR__ . '/' . $rutaFoto)) {
    $rutaFoto = 'archivos/default.png';
}

include __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">

        <!-- Navegación de retorno -->
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <a href="index.php" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i> Volver al Directorio
            </a>
            <div class="d-flex gap-2">
                <a href="editar.php?id=<?= (int)$contacto['id']; ?>" class="btn btn-warning btn-sm fw-semibold">
                    <i class="bi bi-pencil-square me-1"></i> Editar
                </a>
                <a href="eliminar.php?id=<?= (int)$contacto['id']; ?>" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-trash3 me-1"></i> Eliminar
                </a>
            </div>
        </div>

        <!-- Tarjeta Principal de Información del Contacto -->
        <div class="card card-custom border-0 shadow-sm overflow-hidden">
            
            <!-- Banner Superior con Foto de Perfil -->
            <div class="text-center py-4 px-3 border-bottom" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.18) 0%, rgba(6, 182, 212, 0.18) 100%), var(--card-bg); border-color: var(--border-color) !important;">
                <div class="mb-3">
                    <img src="<?= e($rutaFoto); ?>" 
                         alt="<?= e($contacto['nombre'] . ' ' . $contacto['apellido']); ?>" 
                         class="contact-avatar-lg shadow">
                </div>
                <h3 class="fw-bold mb-1 text-light"><?= e($contacto['nombre'] . ' ' . $contacto['apellido']); ?></h3>
                <span class="badge badge-soft-primary px-3 py-1 rounded-pill fw-semibold">
                    ID de Registro: #<?= (int)$contacto['id']; ?>
                </span>
            </div>

            <!-- Cuerpo con Atributos Detallados -->
            <div class="card-body p-4">
                
                <h5 class="text-muted fw-bold text-uppercase fs-7 mb-3">Información de Contacto</h5>

                <!-- Teléfono (Obligatorio) -->
                <div class="detail-item">
                    <div class="detail-icon text-success">
                        <i class="bi bi-telephone-fill"></i>
                    </div>
                    <div class="detail-content flex-grow-1">
                        <h6>Teléfono Principal (Obligatorio)</h6>
                        <p>
                            <a href="tel:<?= e($contacto['telefono']); ?>" class="text-decoration-none text-light fw-bold">
                                <?= e($contacto['telefono']); ?>
                            </a>
                            <span class="badge badge-soft-success ms-2">Móvil</span>
                        </p>
                    </div>
                    <div>
                        <a href="tel:<?= e($contacto['telefono']); ?>" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-telephone-outbound me-1"></i> Llamar
                        </a>
                    </div>
                </div>

                <!-- Correo Electrónico (Opcional) -->
                <div class="detail-item">
                    <div class="detail-icon text-primary">
                        <i class="bi bi-envelope-fill"></i>
                    </div>
                    <div class="detail-content flex-grow-1">
                        <h6>Correo Electrónico</h6>
                        <p>
                            <?php if (!empty($contacto['email'])): ?>
                                <a href="mailto:<?= e($contacto['email']); ?>" class="text-decoration-none text-light">
                                    <?= e($contacto['email']); ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted fst-italic">No registrado</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php if (!empty($contacto['email'])): ?>
                        <div>
                            <a href="mailto:<?= e($contacto['email']); ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-send me-1"></i> Enviar Correo
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Dirección Física (Opcional) -->
                <div class="detail-item">
                    <div class="detail-icon text-danger">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div class="detail-content flex-grow-1">
                        <h6>Dirección Domiciliaria</h6>
                        <p>
                            <?php if (!empty($contacto['direccion'])): ?>
                                <?= e($contacto['direccion']); ?>
                            <?php else: ?>
                                <span class="text-muted fst-italic">No especificada</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <!-- Notas y Observaciones (Opcional) -->
                <div class="detail-item">
                    <div class="detail-icon text-warning">
                        <i class="bi bi-sticky-fill"></i>
                    </div>
                    <div class="detail-content flex-grow-1">
                        <h6>Notas / Observaciones</h6>
                        <p class="text-secondary" style="white-space: pre-line;">
                            <?= !empty($contacto['notas']) ? e($contacto['notas']) : '<span class="text-muted fst-italic">Sin observaciones adicionales.</span>'; ?>
                        </p>
                    </div>
                </div>

                <!-- Fecha de Registro -->
                <div class="detail-item">
                    <div class="detail-icon text-secondary">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div class="detail-content flex-grow-1">
                        <h6>Fecha de Alta en el Sistema</h6>
                        <p class="text-muted small mb-0">
                            <?= date('d/m/Y h:i A', strtotime($contacto['fecha_registro'])); ?>
                        </p>
                    </div>
                </div>

            </div>

            <!-- Footer de la Tarjeta con Botones de Acción -->
            <div class="card-footer p-3 d-flex justify-content-between align-items-center border-top" style="background-color: rgba(30, 41, 59, 0.45); border-color: var(--border-color) !important;">
                <span class="text-muted small">
                    <i class="bi bi-shield-check text-success me-1"></i> Registro verificado por PDO
                </span>
                <div class="d-flex gap-2">
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">Directorio</a>
                    <a href="editar.php?id=<?= (int)$contacto['id']; ?>" class="btn btn-warning btn-sm fw-semibold">
                        <i class="bi bi-pencil-square me-1"></i> Editar Contacto
                    </a>
                </div>
            </div>

        </div>

    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
