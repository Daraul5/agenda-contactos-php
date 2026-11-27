# 📒 Mini Proyecto 2 (Unidad 4) — Agenda de Contactos con PHP y MySQL (CRUD)

## 📌 Información General

- **Asignatura:** Programación Web  
- **Unidad:** Unidad 4 – Programación del lado del servidor con PHP  
- **Tipo de actividad:** Mini Proyecto 2 (Desarrollo Web con PHP y Base de Datos)  
- **Modalidad:** En equipo  

---

## 👥 Integrantes del Equipo

- **David Raúl Vázquez Yáñez**
- **Rebeca Jiménez Díaz**
- **Elías Raziel Gómez Carrión**

---

## 🔗 Enlaces de Entrega del Proyecto

- **Repositorio Público en GitHub:** [https://github.com/Daraul5/agenda-contactos-php](https://github.com/Daraul5/agenda-contactos-php)
- **Aplicación Web Publicada en Servidor Real (Hosting):** [https://agenda-contactos-pw.infinityfreeapp.com](https://agenda-contactos-pw.infinityfreeapp.com)

---

## 🎯 Descripción del Proyecto

Aplicación web dinámica desarrollada en **PHP 8** y **MySQL / MariaDB** para la administración integral de una **Agenda de Contactos**. El sistema implementa un ciclo de vida **CRUD (Create, Read, Update, Delete)** completo, haciendo uso riguroso de buenas prácticas de ingeniería de software, arquitectura limpia, seguridad informática contra vulnerabilidades comunes (SQL Injection, XSS y CSRF) y separación clara de vistas y controladores.

---

## 🛠️ Tecnologías y Herramientas Utilizadas

- **Lenguaje del Servidor:** PHP 8.x (Programación procedimental modular y estructurada).
- **Manejador de Base de Datos:** MySQL / MariaDB (Motor relacional InnoDB con soporte UTF-8mb4).
- **Capa de Abstracción de Datos:** **PDO (PHP Data Objects)** con sentencias preparadas nativas (`prepare` y `execute`).
- **Diseño Visual e Interfaz de Usuario:**
  - HTML5 semántico con validación nativa de formularios.
  - CSS3 modular externo (`css/styles.css`) con soporte para tema oscuro moderno.
  - Bootstrap 5.3 (Framework responsive y componentes interactivos).
  - Bootstrap Icons (Iconografía vectorial moderna).
- **Seguridad Web:**
  - Sentencias preparadas parametrizadas para mitigación total de **SQL Injection**.
  - Sanitización estricta de salida en vistas mediante `htmlspecialchars()`.
  - Protección de formularios mediante **Tokens Anti-CSRF**.
  - Confirmación de borrado restrictiva mediante peticiones **POST**.
  - Validación profunda de archivos binarios subidos (extensiones, tipos MIME reales con `finfo` y cuota máxima en bytes).
  - Ocultación de errores SQL internos al usuario final con registro en `error_log`.
- **Contenerización y Despliegue:**
  - Docker con Apache y extensiones PHP (PDO MySQL, GD para manejo de imágenes).
  - Compatibilidad con bases de datos en la nube (TiDB Cloud, Aiven) mediante soporte SSL.

---

## 🚀 Funcionalidades Implementadas (CRUD Completo)

| Operación | Módulo | Descripción Técnica |
| :--- | :--- | :--- |
| **CREATE (Vista)** | `crear.php` | Formulario HTML5 con validaciones de cliente (`required`, `type`, `pattern`) y previsualización de imagen en tiempo real. |
| **CREATE (Lógica)** | `guardar.php` | Valida token CSRF, audita entradas en servidor, valida y sube la foto a `archivos/` con nombre criptográfico único, y ejecuta `INSERT INTO contactos` con PDO. |
| **READ (General)** | `index.php` | Lista todos los contactos ordenados alfabéticamente en **vista dual interactiva**: cuadrícula de tarjetas de perfil y tabla responsiva. |
| **READ (Búsqueda)**| `index.php?q=...` | Buscador multicampo con filtro dinámico y marcadores nombrados únicos (`:f1` a `:f5`) sobre nombre, apellido, teléfono, email y nombre completo concatenado. |
| **READ (Detalle)** | `ver.php?id=...` | Muestra el perfil completo del contacto individual validando el ID numérico con `FILTER_VALIDATE_INT`, accesos directos de llamada y correo. |
| **UPDATE (Vista)** | `editar.php?id=...` | Carga los datos existentes en el formulario, permitiendo actualizar datos y opcionalmente reemplazar la fotografía. |
| **UPDATE (Lógica)**| `actualizar.php` | Valida datos en servidor, gestiona la sustitución de imagen, borra la imagen previa del disco y ejecuta `UPDATE contactos SET ... WHERE id = :id`. |
| **DELETE** | `eliminar.php` | Pantalla de confirmación previa vía GET y ejecución definitiva mediante formulario POST seguro que elimina el registro en BD y el archivo físico. |
| **EXPORTACIÓN** | `exportar.php` | Generación y descarga directa del directorio en formato **CSV** con codificación UTF-8 (BOM incluido para compatibilidad universal con Excel). |

---

## 📋 Estructura del Proyecto

```text
agenda-contactos/
│
├── index.php             # Listado principal, visualización dual (tarjetas/tabla) y buscador dinámico (READ)
├── crear.php             # Formulario de alta de nuevo contacto (CREATE vista)
├── guardar.php           # Procesamiento de inserción y subida de fotografía (CREATE lógica)
├── editar.php            # Formulario de modificación con datos existentes (UPDATE vista)
├── actualizar.php        # Procesamiento de actualización y reemplazo de foto (UPDATE lógica)
├── ver.php               # Detalle completo de contacto individual (READ individual)
├── eliminar.php          # Confirmación visual y procesamiento seguro de borrado (DELETE)
├── exportar.php          # Exportación del directorio a formato CSV (UTF-8 BOM)
│
├── config/
│   └── db.php            # Conexión PDO, soporte para variables de entorno / SSL y funciones de seguridad (e, CSRF, Flash)
│
├── includes/
│   ├── header.php        # Cabecera HTML5, hojas de estilos, metadatos y alertas flash
│   ├── menu.php          # Barra de navegación común y formulario de búsqueda global
│   └── footer.php        # Pie de página institucional, créditos del equipo y scripts JS
│
├── css/
│   └── styles.css        # Hoja de estilos personalizada externa con paleta moderna y tema oscuro
│
├── archivos/             # Carpeta de almacenamiento para fotografías de contactos
│   ├── default.png       # Avatar por defecto de respaldo
│   ├── contacto1.png     # Foto de prueba sembrada
│   ├── contacto2.png     # Foto de prueba sembrada
│   └── contacto3.png     # Foto de prueba sembrada
│
├── sql/
│   └── schema.sql        # Script DDL/DML para crear la base de datos, tabla y registros semilla
│
├── Dockerfile            # Configuración para despliegue contenerizado en Docker / Render
├── .dockerignore         # Exclusiones de archivos para el contexto de Docker
├── .gitignore            # Exclusión de archivos temporales del repositorio
└── README.md             # Documentación técnica completa del proyecto
```

---

## 💻 Instrucciones para Ejecución Local

### 1. Prerrequisitos
- PHP 8.0 o superior con extensión `pdo_mysql` habilitada (o Docker instalado).
- Servidor MySQL o MariaDB (vía XAMPP, Laragon, WAMP o nativo).

### 2. Base de Datos
1. Inicie su servidor MySQL (por ejemplo en el panel de control de XAMPP).
2. Abra **phpMyAdmin** (`http://localhost/phpmyadmin`) o su gestor favorito (DBeaver, MySQL Workbench).
3. Importe o ejecute el archivo [`sql/schema.sql`](sql/schema.sql). Esto creará la base de datos `agenda_db` y la tabla `contactos` con datos de prueba iniciales.

### 3. Configuración de Conexión y Variables de Entorno
La aplicación detecta automáticamente variables de entorno o usa los valores por defecto en [`config/db.php`](config/db.php):
```php
define('DB_HOST', getDbEnv('DB_HOST', '127.0.0.1'));
define('DB_PORT', getDbEnv('DB_PORT', '3306'));
define('DB_NAME', getDbEnv('DB_NAME', 'agenda_db'));
define('DB_USER', getDbEnv('DB_USER', 'root'));
define('DB_PASS', getDbEnv('DB_PASS', ''));
```

| Variable de Entorno | Descripción | Valor por Defecto |
| :--- | :--- | :--- |
| `DB_HOST` | Host del servidor de MySQL/MariaDB | `127.0.0.1` |
| `DB_PORT` | Puerto de escucha de MySQL | `3306` |
| `DB_NAME` | Nombre de la base de datos | `agenda_db` |
| `DB_USER` | Usuario con permisos en la base de datos | `root` |
| `DB_PASS` | Contraseña de acceso a la base de datos | *(vacío)* |
| `DB_SSL` | Activar soporte SSL (`true` o `1`) para cloud DBs | *(inactivo)* |

### 4. Servidor de Desarrollo Local con PHP
Desde la raíz del proyecto, ejecute en terminal:
```bash
php -S localhost:8000
```
Luego ingrese en su navegador web a: **`http://localhost:8000`**

### 5. Ejecución con Docker (Opcional)
Si prefiere ejecutar la aplicación en un contenedor Docker con Apache y PHP preconfigurados:
```bash
# Construir la imagen
docker build -t agenda-contactos .

# Ejecutar el contenedor
docker run -d -p 8000:80 \
  -e DB_HOST=host.docker.internal \
  -e DB_PORT=3306 \
  -e DB_NAME=agenda_db \
  -e DB_USER=root \
  -e DB_PASS= \
  --name agenda-app agenda-contactos
```
Luego abra en su navegador: **`http://localhost:8000`**

---

## 🌐 Guía de Despliegue en Hosting Gratuito (InfinityFree / AeonFree / ByetHost)

Para cumplir con el requisito de publicación en internet en un servidor real con PHP y MySQL:

### Paso 1: Crear Cuenta en el Hosting
1. Regístrese de manera gratuita en [InfinityFree](https://www.infinityfree.com/) o [AeonFree](https://aeonfree.com/).
2. Cree una nueva cuenta de hosting y elija un subdominio gratuito (ejemplo: `miagenda.infinityfreeapp.com`).

### Paso 2: Crear la Base de Datos en el cPanel
1. Ingrese al panel de control (cPanel / VistaPanel) de su cuenta.
2. Vaya a **MySQL Databases**.
3. Cree una nueva base de datos (se le asignará un prefijo, por ejemplo: `epiz_34567890_agenda`).
4. Anote los siguientes datos que le muestra el panel:
   - **MySQL Hostname:** (ejemplo: `sql123.infinityfree.com` o similar, **NO es localhost**)
   - **MySQL Database Name:** (ejemplo: `epiz_34567890_agenda`)
   - **MySQL Username:** (ejemplo: `epiz_34567890`)
   - **MySQL Password:** (la contraseña de su cuenta de hosting)

### Paso 3: Importar el Script SQL
1. Desde el cPanel, abra la herramienta **phpMyAdmin**.
2. Seleccione la base de datos que acaba de crear.
3. Haga clic en la pestaña **Importar** (Import).
4. Suba el archivo `sql/schema.sql` y haga clic en **Continuar** para crear la tabla `contactos` y los registros de muestra.

### Paso 4: Configurar `config/db.php`
En el archivo `config/db.php`, actualice las constantes con las credenciales de su hosting:
```php
define('DB_HOST', 'sql123.infinityfree.com'); // Su hostname de hosting
define('DB_NAME', 'epiz_34567890_agenda');     // Su nombre de BD
define('DB_USER', 'epiz_34567890');            // Su usuario de BD
define('DB_PASS', 'SuContraseñaSegura');       // Su contraseña
```

### Paso 5: Subir los Archivos mediante Administrador de Archivos o FTP
1. En el cPanel, abra el **Online File Manager** (o conecte un cliente FTP como FileZilla).
2. Navegue dentro de la carpeta pública **`htdocs/`**.
3. Suba todos los archivos y carpetas del proyecto (`index.php`, `crear.php`, `config/`, `includes/`, `css/`, `archivos/`, etc.) directamente dentro de `htdocs/`.
4. Asegúrese de que la carpeta `archivos/` tenga permisos de escritura (`755` o `777`) para que PHP pueda almacenar las fotos subidas.

### Paso 6: Verificación en Vivo
Ingrese a la URL pública de su sitio en el navegador. Podrá dar de alta contactos, subir fotos, realizar búsquedas, editarlos y eliminarlos en tiempo real.
