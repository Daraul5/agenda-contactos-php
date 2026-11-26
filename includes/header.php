<?php
/**
 * includes/header.php
 * Encabezado común para todas las vistas del sistema
 */
require_once __DIR__ . '/../config/db.php';
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | Agenda PHP CRUD' : 'Agenda de Contactos | Programación Web'; ?></title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- CSS Externo Obligatorio -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <!-- Menú común de navegación -->
    <?php include __DIR__ . '/menu.php'; ?>

    <!-- Contenedor Principal -->
    <main class="container my-4 flex-grow-1">

        <!-- Mensajes Flash de Notificación (Alertas del Servidor) -->
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']); ?> alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert">
                <i class="bi <?= $flash['type'] === 'success' ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger'; ?> fs-4 me-2"></i>
                <div class="flex-grow-1">
                    <?= e($flash['message']); ?>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>
