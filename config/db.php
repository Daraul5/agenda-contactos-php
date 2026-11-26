<?php
/**
 * config/db.php
 * Conexión a Base de Datos con PDO y funciones auxiliares de seguridad
 * Mini Proyecto 2 - Unidad 4: Programación Web
 */

// Iniciar sesión para soporte de mensajes flash y tokens CSRF
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ====================================================================
// PARÁMETROS DE CONFIGURACIÓN DE BASE DE DATOS
// ====================================================================
// Para hosting gratuito (InfinityFree / AeonFree / ByetHost):
// Reemplaza estos valores con los proporcionados en el cPanel / MySQL Details.
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'agenda_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Establece y retorna la conexión a la base de datos usando PDO
 * Aplica buenas prácticas de seguridad:
 * - Modo de errores: Excepciones (PDO::ERRMODE_EXCEPTION)
 * - Fetch asociativo predeterminado
 * - Emulación de sentencias preparadas desactivada (True Prepared Statements)
 * 
 * @return PDO
 */
function getDBConnection(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // Sentencias preparadas reales en el motor MySQL
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Seguridad: Registramos el detalle técnico en el log del servidor y ocultamos el error SQL al usuario
            error_log("Error crítico en PDO: " . $e->getMessage());
            die("
                <div style='font-family: system-ui, -apple-system, sans-serif; max-width: 650px; margin: 60px auto; padding: 25px; border-left: 5px solid #dc2626; background: #fef2f2; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);'>
                    <h3 style='color: #991b1b; margin-top: 0;'>⚠️ Error de Conexión a la Base de Datos</h3>
                    <p style='color: #374151; font-size: 15px; line-height: 1.5;'>
                        No fue posible conectarse a MySQL mediante PDO. Por favor verifique que las credenciales en <code>config/db.php</code> sean correctas y que el servidor de base de datos esté encendido.
                    </p>
                    <p style='color: #6b7280; font-size: 13px; margin-bottom: 0;'>
                        <em>Nota de seguridad: Los detalles internos de la excepción SQL no se muestran al cliente para prevenir fuga de información sensible.</em>
                    </p>
                </div>
            ");
        }
    }

    return $pdo;
}

/**
 * Sanitización de salida contra ataques XSS (Cross-Site Scripting)
 * Requisito obligatorio: htmlspecialchars
 * 
 * @param mixed $data
 * @return string
 */
function e($data): string {
    return htmlspecialchars((string)($data ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Almacena un mensaje flash en la sesión para mostrar tras redirección
 * 
 * @param string $type ('success' | 'danger' | 'warning' | 'info')
 * @param string $message
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Obtiene y limpia el mensaje flash de la sesión
 * 
 * @return array|null
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Genera o retorna el token CSRF para validación de formularios POST
 * 
 * @return string
 */
function getCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida que el token CSRF recibido en el formulario coincida con la sesión
 * 
 * @param string|null $token
 * @return bool
 */
function validateCsrfToken(?string $token): bool {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
