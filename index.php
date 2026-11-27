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

// Modo de visualización: 'cards' (tarjetas) o 'table' (tabla)
$view = isset($_GET['view']) ? trim($_GET['view']) : ($_SESSION['view_mode'] ?? 'cards');
if (!in_array($view, ['cards', 'table'], true)) {
    $view = 'cards';
}
$_SESSION['view_mode'] = $view;

try {
    $pdo = getDBConnection();

    // Consulta de datos con sentencias preparadas (Especificación 4 y 10)
    if (!empty($q)) {
        // Búsqueda multicampo mediante marcadores nombrados únicos (compatibilidad nativa PDO)
        $sql = "SELECT * FROM contactos 
                WHERE nombre LIKE :f1 
                   OR apellido LIKE :f2 
                   OR telefono LIKE :f3 
                   OR email LIKE :f4 
                   OR CONCAT(nombre, ' ', apellido) LIKE :f5
                ORDER BY nombre ASC, apellido ASC";
        $stmt = $pdo->prepare($sql);
        $param = '%' . $q . '%';
        $stmt->execute([
            ':f1' => $param,
            ':f2' => $param,
            ':f3' => $param,
            ':f4' => $param,
            ':f5' => $param,
        ]);
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
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <!-- Selector de Vista (Tarjetas / Tabla) -->
        <div class="btn-group shadow-sm" role="group" aria-label="Modo de visualización">
            <a href="index.php?view=cards<?= !empty($q) ? '&q=' . urlencode($q) : ''; ?>" 
               class="btn btn-sm <?= $view === 'cards' ? 'btn-primary' : 'btn-outline-secondary'; ?>" 
               title="Vista en Tarjetas">
                <i class="bi bi-grid-3x3-gap-fill me-1"></i> Tarjetas
            </a>
            <a href="index.php?view=table<?= !empty($q) ? '&q=' . urlencode($q) : ''; ?>" 
               class="btn btn-sm <?= $view === 'table' ? 'btn-primary' : 'btn-outline-secondary'; ?>" 
               title="Vista en Tabla">
                <i class="bi bi-table me-1"></i> Tabla
            </a>
        </div>

        <!-- Botón Exportar CSV -->
        <?php if ($totalRegistros > 0): ?>
            <a href="exportar.php<?= !empty($q) ? '?q=' . urlencode($q) : ''; ?>" 
               class="btn btn-outline-success btn-sm d-inline-flex align-items-center gap-1 shadow-sm" 
               title="Descargar listado en formato CSV">
                <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                <span class="d-none d-sm-inline">Exportar CSV</span>
            </a>
        <?php endif; ?>

        <a href="crear.php" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2 shadow-sm fw-semibold">
            <i class="bi bi-person-plus-fill"></i>
            <span>Nuevo Contacto</span>
        </a>
    </div>
</div>

<!-- Barra de Filtros y Búsqueda Activa -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="index.php" method="GET" class="row g-2 align-items-center">
            <input type="hidden" name="view" value="<?= e($view); ?>">
            <div class="col-md-9 col-sm-8">
                <div class="input-group">
                    <span class="input-group-text text-muted border-end-0">
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
                    <a href="index.php?view=<?= e($view); ?>" class="btn btn-outline-danger" title="Limpiar filtro">
                        <i class="bi bi-x-circle"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (!empty($q)): ?>
            <div class="mt-2 pt-2 border-top border-secondary-subtle small text-muted d-flex justify-content-between align-items-center">
                <span>Resultados encontrados para: <strong>"<?= e($q); ?>"</strong></span>
                <a href="index.php?view=<?= e($view); ?>" class="text-decoration-none">Mostrar todos</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Listado de Contactos (Vista Dual: Tarjetas o Tabla) -->
<?php if ($totalRegistros > 0): ?>

    <?php if ($view === 'cards'): ?>
        <!-- Vista de Cuadrícula / Tarjetas -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
            <?php foreach ($contactos as $c): ?>
                <?php
                    $rutaFoto = 'archivos/' . $c['foto'];
                    if (empty($c['foto']) || !file_exists(__DIR__ . '/' . $rutaFoto)) {
                        $rutaFoto = 'archivos/default.png';
                    }
                ?>
                <div class="col">
                    <div class="card card-custom card-contact h-100 p-3 text-center d-flex flex-column">
                        <div class="avatar-wrapper mb-3 mt-1">
                            <a href="ver.php?id=<?= (int)$c['id']; ?>">
                                <img src="<?= e($rutaFoto); ?>" 
                                     alt="<?= e($c['nombre'] . ' ' . $c['apellido']); ?>" 
                                     class="contact-avatar">
                            </a>
                        </div>
                        <h5 class="fw-bold mb-1 text-truncate">
                            <a href="ver.php?id=<?= (int)$c['id']; ?>" class="text-decoration-none text-light">
                                <?= e($c['nombre'] . ' ' . $c['apellido']); ?>
                            </a>
                        </h5>
                        <div class="mb-3">
                            <span class="badge badge-soft-primary">
                                ID: #<?= (int)$c['id']; ?>
                            </span>
                        </div>

                        <div class="text-start border-top border-secondary-subtle pt-3 mb-3 small flex-grow-1">
                            <div class="d-flex align-items-center mb-2 text-truncate">
                                <i class="bi bi-telephone-fill text-success me-2 flex-shrink-0"></i>
                                <a href="tel:<?= e($c['telefono']); ?>" class="text-decoration-none text-secondary text-truncate">
                                    <?= e($c['telefono']); ?>
                                </a>
                            </div>
                            <div class="d-flex align-items-center mb-2 text-truncate">
                                <i class="bi bi-envelope-fill text-info me-2 flex-shrink-0"></i>
                                <?php if (!empty($c['email'])): ?>
                                    <a href="mailto:<?= e($c['email']); ?>" class="text-decoration-none text-secondary text-truncate" title="<?= e($c['email']); ?>">
                                        <?= e($c['email']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Sin correo</span>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex align-items-center text-truncate">
                                <i class="bi bi-geo-alt-fill text-danger me-2 flex-shrink-0"></i>
                                <?php if (!empty($c['direccion'])): ?>
                                    <span class="text-muted text-truncate" title="<?= e($c['direccion']); ?>">
                                        <?= e($c['direccion']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Sin dirección</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="btn-group w-100 btn-action-group" role="group">
                            <a href="ver.php?id=<?= (int)$c['id']; ?>" class="btn btn-outline-info btn-sm" title="Ver detalles">
                                <i class="bi bi-eye"></i> Ver
                            </a>
                            <a href="editar.php?id=<?= (int)$c['id']; ?>" class="btn btn-outline-warning btn-sm" title="Editar contacto">
                                <i class="bi bi-pencil-square"></i> Editar
                            </a>
                            <a href="eliminar.php?id=<?= (int)$c['id']; ?>" class="btn btn-outline-danger btn-sm" title="Eliminar contacto">
                                <i class="bi bi-trash3"></i> Borrar
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <!-- Vista de Tabla Responsiva -->
        <div class="card card-custom border-0 shadow-sm overflow-hidden mb-4">
            <div class="table-responsive">
                <table class="table table-hover table-contacts align-middle mb-0">
                    <thead>
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
                                    <a href="ver.php?id=<?= (int)$c['id']; ?>" class="text-decoration-none fw-bold text-light d-block">
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
    <?php endif; ?>

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
