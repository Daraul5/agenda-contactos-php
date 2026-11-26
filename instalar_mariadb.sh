#!/usr/bin/env bash
# ====================================================================
# Script de Instalación y Configuración Automatizada de MariaDB & PHP PDO
# Sistema Operativo: CachyOS / Arch Linux
# Mini Proyecto 2 - Unidad 4: Programación Web
# ====================================================================

set -e

# Colores para la terminal
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}====================================================${NC}"
echo -e "${BLUE}   Configurador de MariaDB & PHP PDO para la Agenda   ${NC}"
echo -e "${BLUE}====================================================${NC}"

# 1. Verificar permisos de superusuario (root)
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}[ERROR] Este script requiere permisos de superusuario.${NC}"
    echo -e "Por favor, ejecútalo con sudo:"
    echo -e "  ${YELLOW}sudo bash $0${NC}"
    exit 1
fi

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# 2. Instalar MariaDB si no está instalado
echo -e "\n${BLUE}[1/5] Verificando e instalando el paquete MariaDB...${NC}"
if ! pacman -Q mariadb &>/dev/null; then
    echo "Instalando mariadb mediante pacman..."
    pacman -S --needed --noconfirm mariadb
    echo -e "${GREEN}✓ MariaDB instalado exitosamente.${NC}"
else
    echo -e "${GREEN}✓ MariaDB ya está instalado en el sistema.${NC}"
fi

# 3. Inicializar el directorio de datos de MariaDB si es necesario
echo -e "\n${BLUE}[2/5] Inicializando el almacén de datos (/var/lib/mysql)...${NC}"
if [ ! -d "/var/lib/mysql/mysql" ]; then
    echo "Ejecutando mariadb-install-db..."
    mariadb-install-db --user=mysql --basedir=/usr --datadir=/var/lib/mysql
    echo -e "${GREEN}✓ Directorio de datos inicializado.${NC}"
else
    echo -e "${GREEN}✓ Directorio de datos ya inicializado.${NC}"
fi

# 4. Iniciar y habilitar el servicio de MariaDB
echo -e "\n${BLUE}[3/5] Iniciando y habilitando el servicio mariadb.service...${NC}"
systemctl enable --now mariadb
if systemctl is-active --quiet mariadb; then
    echo -e "${GREEN}✓ Servicio MariaDB activo y en ejecución.${NC}"
else
    echo -e "${RED}[ERROR] El servicio MariaDB no pudo iniciarse.${NC}"
    exit 1
fi

# 5. Habilitar la extensión pdo_mysql en PHP (/etc/php/php.ini)
echo -e "\n${BLUE}[4/5] Habilitando extensión pdo_mysql en PHP (/etc/php/php.ini)...${NC}"
if grep -q "^;extension=pdo_mysql" /etc/php/php.ini 2>/dev/null; then
    sed -i 's/^;extension=pdo_mysql/extension=pdo_mysql/' /etc/php/php.ini
    echo -e "${GREEN}✓ Extensión pdo_mysql habilitada en /etc/php/php.ini.${NC}"
elif grep -q "^extension=pdo_mysql" /etc/php/php.ini 2>/dev/null; then
    echo -e "${GREEN}✓ Extensión pdo_mysql ya estaba activa en /etc/php/php.ini.${NC}"
else
    echo "extension=pdo_mysql" >> /etc/php/php.ini
    echo -e "${GREEN}✓ Línea extension=pdo_mysql añadida a /etc/php/php.ini.${NC}"
fi

# 6. Crear base de datos y configurar usuario root para conexión local TCP/IP
echo -e "\n${BLUE}[5/5] Configurando base de datos 'agenda_db' e importando schema.sql...${NC}"

# Configurar permisos para root en localhost y 127.0.0.1
mariadb -e "
CREATE DATABASE IF NOT EXISTS agenda_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'root'@'127.0.0.1' IDENTIFIED BY '';
GRANT ALL PRIVILEGES ON agenda_db.* TO 'root'@'127.0.0.1';
GRANT ALL PRIVILEGES ON agenda_db.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
"

# Importar el esquema SQL con los contactos de muestra
if [ -f "$PROJECT_DIR/sql/schema.sql" ]; then
    mariadb agenda_db < "$PROJECT_DIR/sql/schema.sql"
    echo -e "${GREEN}✓ Esquema sql/schema.sql importado con éxito (tabla contactos y datos de prueba).${NC}"
fi

# 7. Prueba rápida con PHP PDO
echo -e "\n${BLUE}====================================================${NC}"
echo -e "${BLUE}        Verificación de Conexión PHP -> MariaDB     ${NC}"
echo -e "${BLUE}====================================================${NC}"

php -r "
try {
    \$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=agenda_db;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    \$count = \$pdo->query('SELECT COUNT(*) AS total FROM contactos')->fetch()['total'];
    echo \"\033[0;32m✓ ¡Conexión PDO exitosa!\033[0m Total de contactos en la BD: \" . \$count . PHP_EOL;
} catch (Exception \$e) {
    echo \"\033[0;31m✗ Error al conectar mediante PDO: \033[0m\" . \$e->getMessage() . PHP_EOL;
    exit(1);
}
"

echo -e "\n${GREEN}🎉 ¡Todo listo y configurado!${NC}"
echo -e "Para iniciar el servidor web, ejecuta como tu usuario normal:"
echo -e "  ${YELLOW}cd $PROJECT_DIR && php -S localhost:8000${NC}\n"
