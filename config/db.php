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

/**
 * Obtiene una variable de entorno de forma segura consultando $_ENV, $_SERVER y getenv()
 */
function getDbEnv(string $key, string $default = ''): string {
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') return (string)$_ENV[$key];
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return (string)$_SERVER[$key];
    $val = getenv($key);
    if ($val !== false && $val !== '') return (string)$val;
    return $default;
}

// Parámetros de conexión configurables con fallback directo a TiDB Cloud
define('DB_HOST', getDbEnv('DB_HOST', '127.0.0.1'));
define('DB_PORT', getDbEnv('DB_PORT', '3306'));
define('DB_NAME', getDbEnv('DB_NAME', 'agenda_db'));
define('DB_USER', getDbEnv('DB_USER', 'root'));
define('DB_PASS', getDbEnv('DB_PASS', ''));
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

        // Soporte para conexiones SSL en bases de datos Cloud (TiDB, Aiven, etc.)
        $requiereSSL = (strpos(DB_HOST, 'tidbcloud.com') !== false || DB_PORT == '4000' || getDbEnv('DB_SSL') === 'true' || getDbEnv('DB_SSL') === '1');
        if ($requiereSSL) {
            $caPaths = [
                getDbEnv('DB_SSL_CA'),
                '/etc/ssl/certs/ca-certificates.crt', // Debian/Ubuntu/Arch
                '/etc/pki/tls/certs/ca-bundle.crt',   // RHEL/CentOS
                '/etc/ssl/cert.pem',                  // Alpine/macOS
            ];
            foreach ($caPaths as $path) {
                if (!empty($path) && file_exists($path)) {
                    $options[PDO::MYSQL_ATTR_SSL_CA] = $path;
                    break;
                }
            }
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

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
