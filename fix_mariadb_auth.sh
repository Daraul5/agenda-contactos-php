#!/usr/bin/env bash
# ====================================================================
# Script para solucionar el error 1698 (Access denied / unix_socket)
# Configura autenticación nativa sin contraseña para usuario root en MariaDB
# ====================================================================

set -e

if [ "$EUID" -ne 0 ]; then
    echo -e "\033[0;31m[ERROR] Por favor ejecuta este script con sudo:\033[0m"
    echo -e "  sudo bash $0"
    exit 1
fi

echo -e "\033[0;34mConfigurando autenticación de MariaDB...\033[0m"

mariadb << 'EOF'
-- Desactivar restricción exclusiva de unix_socket y permitir acceso a root local
ALTER USER 'root'@'localhost' IDENTIFIED BY '';
CREATE USER IF NOT EXISTS 'root'@'127.0.0.1' IDENTIFIED BY '';
ALTER USER 'root'@'127.0.0.1' IDENTIFIED BY '';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION;
GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION;

-- Crear también usuario específico de la aplicación por si se prefiere
CREATE USER IF NOT EXISTS 'agenda_user'@'localhost' IDENTIFIED BY '';
CREATE USER IF NOT EXISTS 'agenda_user'@'127.0.0.1' IDENTIFIED BY '';
GRANT ALL PRIVILEGES ON agenda_db.* TO 'agenda_user'@'localhost';
GRANT ALL PRIVILEGES ON agenda_db.* TO 'agenda_user'@'127.0.0.1';

FLUSH PRIVILEGES;
EOF

echo -e "\033[0;32m✓ Permisos y métodos de autenticación actualizados en MariaDB.\033[0m"

echo -e "\n\033[0;34mProbando conexión PHP PDO con usuario root...\033[0m"
php -r "
try {
    \$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=agenda_db;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    \$count = \$pdo->query('SELECT COUNT(*) AS total FROM contactos')->fetch()['total'];
    echo \"\033[0;32m✓ ¡Conexión PDO EXITOSA!\033[0m Total de contactos en la BD: \" . \$count . PHP_EOL;
} catch (Exception \$e) {
    echo \"\033[0;31m✗ Error PDO: \033[0m\" . \$e->getMessage() . PHP_EOL;
    exit(1);
}
"

echo -e "\n\033[0;32m🎉 ¡Base de datos lista para usar en el navegador!\033[0m"
