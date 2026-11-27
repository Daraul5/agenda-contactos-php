<?php
/**
 * exportar.php
 * Exportación del directorio de contactos en formato CSV con soporte UTF-8
 * Mini Proyecto 2 - Unidad 4: Programación Web
 */
require_once __DIR__ . '/config/db.php';

// Captura de filtro opcional de búsqueda
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

try {
    $pdo = getDBConnection();

    if (!empty($q)) {
        $sql = "SELECT id, nombre, apellido, telefono, email, direccion, notas, fecha_registro 
                FROM contactos 
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
        $sql = "SELECT id, nombre, apellido, telefono, email, direccion, notas, fecha_registro 
                FROM contactos 
                ORDER BY nombre ASC, apellido ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }

    $contactos = $stmt->fetchAll();

    // Configuración de encabezados HTTP para forzar descarga CSV
    $nombreArchivo = 'agenda_contactos_' . date('Y-m-d_His') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Apertura del flujo de salida
    $output = fopen('php://output', 'w');

    // Escribir marca BOM UTF-8 para compatibilidad total con Excel
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Encabezados de columnas
    fputcsv($output, [
        'ID',
        'Nombre',
        'Apellido',
        'Teléfono',
        'Correo Electrónico',
        'Dirección',
        'Notas',
        'Fecha de Registro'
    ]);

    // Filas de datos
    foreach ($contactos as $c) {
        fputcsv($output, [
            $c['id'],
            $c['nombre'],
            $c['apellido'],
            $c['telefono'],
            $c['email'] ?? '',
            $c['direccion'] ?? '',
            $c['notas'] ?? '',
            $c['fecha_registro'] ?? ''
        ]);
    }

    fclose($output);
    exit;

} catch (PDOException $e) {
    error_log("Error al exportar contactos: " . $e->getMessage());
    setFlash('danger', 'Error al generar la exportación de contactos.');
    header('Location: index.php');
    exit;
}
