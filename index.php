<?php
/**
 * index.php
 * Listado principal y búsqueda dinámica de contactos - READ (General)
 * Mini Proyecto 2 - Unidad 4: Programación Web
 */
$pageTitle = 'Directorio de Contactos';
require_once __DIR__ . '/config/db.php';

// Captura del parámetro de búsqueda sanitizado
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

try {
    $pdo = getDBConnection();

    // Consulta de datos con sentencias preparadas (Especificación 4 y 10)
    if (!empty($q)) {
        // Búsqueda multicampo mediante marcadores PDO
        $sql = "SELECT * FROM contactos 
                WHERE nombre LIKE :filtro 
                   OR apellido LIKE :filtro 
                   OR telefono LIKE :filtro 
                   OR email LIKE :filtro 
                ORDER BY nombre ASC, apellido ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':filtro' => '%' . $q . '%']);
    } else {
        // Listado completo de la tabla contactos
        $sql = "SELECT * FROM contactos ORDER BY nombre ASC, apellido ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }

    $contactos = $stmt->fetchAll();
    $totalRegistros = count($contactos);

} catch (PDOException $e) {
    error_log("Error al consultar contactos: " . $e->getMessage());
    $contactos = [];
    $totalRegistros = 0;
    setFlash('danger', 'Error al consultar la lista de contactos en la base de datos.');
}

include __DIR__ . '/includes/header.php';
?>

<!-- Cabecera de Página con Métricas y Botón de Creación -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1 d-flex align-items-center gap-2">
            <span>Directorio de Contactos</span>
            <span class="badge bg-primary rounded-pill fs-6"><?= $totalRegistros; ?></span>
        </h2>
        <p class="text-muted mb-0">Gestión de contactos personales y profesionales mediante PHP & MySQL PDO</p>
    </div>
    <div class="d-flex gap-2">
        <a href="crear.php" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm fw-semibold">
            <i class="bi bi-person-plus-fill"></i>
            <span>Nuevo Contacto</span>
        </a>
    </div>
</div>

<!-- Barra de Filtros y Búsqueda Activa -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="index.php" method="GET" class="row g-2 align-items-center">
            <div class="col-md-9 col-sm-8">
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" 
                           name="q" 
                           class="form-control border-start-0 ps-0" 
                           placeholder="Buscar por nombre, apellido, teléfono o email..." 
                           value="<?= e($q); ?>">
                </div>
            </div>
            <div class="col-md-3 col-sm-4 d-flex gap-2">
                <button type="submit" class="btn btn-secondary w-100 fw-semibold">
                    Filtrar
                </button>
                <?php if (!empty($q)): ?>
                    <a href="index.php" class="btn btn-outline-danger" title="Limpiar filtro">
                        <i class="bi bi-x-circle"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (!empty($q)): ?>
            <div class="mt-2 pt-2 border-top small text-muted d-flex justify-content-between align-items-center">
                <span>Resultados encontrados para: <strong>"<?= e($q); ?>"</strong></span>
                <a href="index.php" class="text-decoration-none">Mostrar todos</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Listado de Contactos (Vista de Tabla y Tarjetas Adaptables) -->
<?php if ($totalRegistros > 0): ?>
    <div class="card card-custom border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover table-contacts align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 70px;">Foto</th>
                        <th>Contacto</th>
                        <th>Teléfono</th>
                        <th>Correo Electrónico</th>
                        <th class="d-none d-lg-table-cell">Dirección</th>
                        <th class="text-end pe-3" style="width: 170px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contactos as $c): ?>
                        <?php
                            // Verificación de existencia física de la imagen
                            $rutaFoto = 'archivos/' . $c['foto'];
                            if (empty($c['foto']) || !file_exists(__DIR__ . '/' . $rutaFoto)) {
                                $rutaFoto = 'archivos/default.png';
                            }
                        ?>
                        <tr>
                            <!-- Fotografía del Contacto (Obligatoria) -->
                            <td class="ps-3">
                                <a href="ver.php?id=<?= (int)$c['id']; ?>">
                                    <img src="<?= e($rutaFoto); ?>" 
                                         alt="<?= e($c['nombre'] . ' ' . $c['apellido']); ?>" 
                                         class="contact-avatar-sm shadow-sm">
                                </a>
                            </td>

                            <!-- Nombre y Apellido -->
                            <td>
                                <a href="ver.php?id=<?= (int)$c['id']; ?>" class="text-decoration-none fw-bold text-dark d-block">
                                    <?= e($c['nombre'] . ' ' . $c['apellido']); ?>
                                </a>
                                <small class="text-muted">ID: #<?= (int)$c['id']; ?></small>
                            </td>

                            <!-- Teléfono (Obligatorio) -->
                            <td>
                                <a href="tel:<?= e($c['telefono']); ?>" class="text-decoration-none text-primary fw-semibold">
                                    <i class="bi bi-telephone-fill me-1 small"></i><?= e($c['telefono']); ?>
                                </a>
                            </td>

                            <!-- Email (Opcional) -->
                            <td>
                                <?php if (!empty($c['email'])): ?>
                                    <a href="mailto:<?= e($c['email']); ?>" class="text-decoration-none text-secondary">
                                        <i class="bi bi-envelope me-1 small"></i><?= e($c['email']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted fst-italic small">Sin correo</span>
                                <?php endif; ?>
                            </td>

                            <!-- Dirección (Opcional) -->
                            <td class="d-none d-lg-table-cell text-truncate" style="max-width: 200px;">
                                <?php if (!empty($c['direccion'])): ?>
                                    <span title="<?= e($c['direccion']); ?>" class="text-muted">
                                        <i class="bi bi-geo-alt me-1 text-danger small"></i><?= e($c['direccion']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted fst-italic small">Sin dirección</span>
                                <?php endif; ?>
                            </td>

                            <!-- Acciones (Ver, Editar, Eliminar) -->
                            <td class="text-end pe-3">
                                <div class="btn-group btn-action-group" role="group">
                                    <a href="ver.php?id=<?= (int)$c['id']; ?>" 
                                       class="btn btn-outline-info" 
                                       title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="editar.php?id=<?= (int)$c['id']; ?>" 
                                       class="btn btn-outline-warning" 
                                       title="Editar contacto">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="eliminar.php?id=<?= (int)$c['id']; ?>" 
                                       class="btn btn-outline-danger" 
                                       title="Eliminar contacto">
                                        <i class="bi bi-trash3"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <!-- Estado Vacío cuando no hay contactos -->
    <div class="card card-custom border-0 shadow-sm text-center py-5">
        <div class="card-body">
            <div class="text-muted mb-3">
                <i class="bi bi-person-x fs-1 opacity-50"></i>
            </div>
            <h4 class="fw-bold">No se encontraron contactos</h4>
            <?php if (!empty($q)): ?>
                <p class="text-muted mb-4">No existen registros que coincidan con el término "<strong><?= e($q); ?></strong>".</p>
                <a href="index.php" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left me-1"></i> Ver todos los contactos
                </a>
            <?php else: ?>
                <p class="text-muted mb-4">La agenda está vacía. Comience agregando su primer contacto.</p>
            <?php endif; ?>
            <a href="crear.php" class="btn btn-primary fw-semibold">
                <i class="bi bi-person-plus-fill me-1"></i> Registrar Contacto
            </a>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
