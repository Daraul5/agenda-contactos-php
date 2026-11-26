<?php
/**
 * includes/menu.php
 * Barra de navegación común con buscador y accesos directos
 */
$currentPage = basename($_SERVER['PHP_SELF']);
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm custom-navbar">
    <div class="container">
        <!-- Logo / Marca -->
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="index.php">
            <span class="brand-icon d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-2">
                <i class="bi bi-person-lines-fill"></i>
            </span>
            <span>Agenda<span class="text-primary-emphasis fw-light">PHP</span></span>
        </a>

        <!-- Botón Toggle para Móviles -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Navegación">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Contenido del Menú -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'index.php' ? 'active fw-semibold' : ''; ?>" href="index.php">
                        <i class="bi bi-grid-fill me-1"></i> Directorio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'crear.php' ? 'active fw-semibold' : ''; ?>" href="crear.php">
                        <i class="bi bi-person-plus-fill me-1"></i> Nuevo Contacto
                    </a>
                </li>
            </ul>

            <!-- Buscador en tiempo real / directo a index.php -->
            <form class="d-flex my-2 my-lg-0 me-lg-3" action="index.php" method="GET" role="search">
                <div class="input-group input-group-sm">
                    <input class="form-control bg-dark text-white border-secondary search-input" 
                           type="search" 
                           name="q" 
                           placeholder="Buscar por nombre, tel..." 
                           value="<?= e($searchQuery); ?>" 
                           aria-label="Buscar contacto">
                    <button class="btn btn-primary" type="submit" title="Buscar">
                        <i class="bi bi-search"></i>
                    </button>
                    <?php if (!empty($searchQuery)): ?>
                        <a href="index.php" class="btn btn-outline-secondary" title="Limpiar búsqueda">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Badge de la Asignatura -->
            <span class="badge bg-secondary-subtle text-secondary-emphasis d-none d-lg-inline-block border border-secondary">
                <i class="bi bi-mortarboard-fill me-1"></i> Programación Web · U4
            </span>
        </div>
    </div>
</nav>
