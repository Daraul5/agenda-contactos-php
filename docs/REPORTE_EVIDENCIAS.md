# REPORTE TÉCNICO DE EVIDENCIAS
## MINI PROYECTO 2: DESARROLLO WEB CON PHP Y BASE DE DATOS (CRUD)
### AGENDA DE CONTACTOS CON MYSQL Y PDO

---

### DATOS GENERALES
- **Asignatura:** Programación Web
- **Unidad:** Unidad 4 – Programación del lado del servidor con PHP
- **Modalidad:** En equipo
- **Integrantes:**
  1. David Raúl Vázquez Yáñez
  2. Rebeca Jiménez Díaz
  3. Elías Raziel Gómez Carrión
- **Enlace del Repositorio GitHub:** `https://github.com/Daraul5/agenda-contactos-php`
- **Enlace de la Aplicación en Servidor Real (Hosting):** `https://agenda-contactos-pw.infinityfreeapp.com`

---

## LISTA DE VERIFICACIÓN SECUENCIAL DE ESPECIFICACIONES

> **Regla de Evaluación:** Cada especificación se presenta en el orden estricto solicitado y consta de los tres componentes obligatorios: **Captura del Código Fuente**, **Captura de Ejecución** (navegador/consola con URL visible) y **Explicación Técnica** (máximo 2 párrafos).

---

### ESPECIFICACIÓN 1: CONEXIÓN A BASE DE DATOS UTILIZANDO PDO

#### 1. Captura del Código Fuente
*Archivo: `config/db.php` (Líneas 25 a 55)*

```php
function getDBConnection(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Error crítico en PDO: " . $e->getMessage());
            die("Error de Conexión a la Base de Datos...");
        }
    }

    return $pdo;
}
```

#### 2. Captura de Ejecución
- **Elemento a capturar:** Pantalla del navegador cargando `index.php` donde se observe la conexión exitosa mostrando el total de registros activos, sin alertas ni interrupciones del script.
- **URL Visible:** `http://localhost:8000/index.php` o `https://tudominio.infinityfreeapp.com/index.php`
- *[INSERTAR AQUÍ CAPTURA DE PANTALLA 1: NAVEGADOR CARGANDO CORRECTAMENTE LA APLICACIÓN CONECTADA A MYSQL]*

#### 3. Explicación Técnica
Se implementó la conexión centralizada a la base de datos MySQL haciendo uso exclusivo de la extensión **PDO (PHP Data Objects)** dentro de la función `getDBConnection()`. Para optimizar el rendimiento y el consumo de sockets en el servidor, se empleó el patrón estático (*Singleton* local), evitando la apertura innecesaria de múltiples conexiones durante un mismo ciclo de petición HTTP. La cadena de conexión DSN especifica explícitamente el juego de caracteres `utf8mb4` para admitir acentos y caracteres especiales sin corrupción de datos.

Como medida fundamental de seguridad y robustez, se configuró el arreglo asociativo `$options` activando `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`, lo cual permite interceptar cualquier anomalía mediante bloques `try-catch`. Asimismo, se configuró `PDO::ATTR_EMULATE_PREPARES => false` para delegar la preparación de consultas directamente en el motor nativo de MySQL, y se encapsuló cualquier posible fallo crítico registrándolo en los logs del servidor mediante `error_log()` sin exponer contraseñas ni detalles de la infraestructura al usuario.

---

### ESPECIFICACIÓN 2: USO DE SENTENCIAS PREPARADAS (PREPARE Y EXECUTE)

#### 1. Captura del Código Fuente
*Archivo: `guardar.php` (Líneas 95 a 115)*

```php
// Consulta SQL con marcadores de posición nombrados (Named Placeholders)
$sql = "INSERT INTO contactos (nombre, apellido, telefono, foto, email, direccion, notas) 
        VALUES (:nombre, :apellido, :telefono, :foto, :email, :direccion, :notas)";

$stmt = $pdo->prepare($sql);

// Ejecución segura de la sentencia preparada pasando arreglo asociativo
$stmt->execute([
    ':nombre'    => $nombre,
    ':apellido'  => $apellido,
    ':telefono'  => $telefono,
    ':foto'      => $nombreFotoGuardada,
    ':email'     => !empty($email) ? $email : null,
    ':direccion' => !empty($direccion) ? $direccion : null,
    ':notas'     => !empty($notas) ? $notas : null,
]);
```

#### 2. Captura de Ejecución
- **Elemento a capturar:** Salida del navegador tras procesar una sentencia preparada exitosamente, o pantalla de phpMyAdmin demostrando que los datos ingresados (incluso con comillas simples o caracteres como `' OR '1'='1`) se guardaron de forma literal e inofensiva.
- **URL Visible:** `http://localhost:8000/guardar.php` -> `index.php`
- *[INSERTAR AQUÍ CAPTURA DE PANTALLA 2: ENVÍO DE DATOS Y REGISTRO EN LA BASE DE DATOS SIN VULNERABILIDAD]*

#### 3. Explicación Técnica
Se implementó el mecanismo de **Sentencias Preparadas (Prepared Statements)** nativas de PDO mediante la separación de dos fases indispensables: compilación y ejecución. En la primera fase, se invoca `$pdo->prepare($sql)` enviando la estructura estática de la consulta con marcadores de posición nombrados (`:nombre`, `:apellido`, `:telefono`, etc.), permitiendo que el motor MySQL compile el plan de ejecución antes de recibir cualquier entrada de usuario.

En la segunda fase, se ejecuta `$stmt->execute($params)` enviando los valores validados en un arreglo asociativo fuertemente vinculado. Esta técnica garantiza inmunidad total contra ataques de **Inyección SQL (SQLi)**, puesto que el motor de base de datos interpreta las variables estrictamente como literales de datos y nunca como código ejecutable de la consulta.

---

### ESPECIFICACIÓN 3: INSERCIÓN DE DATOS (CREATE)

#### 1. Captura del Código Fuente
*Archivo: `guardar.php` (Líneas 90 a 125) y `crear.php` (Líneas 30 a 50)*

```php
// Procesamiento de subida física y almacenamiento en Base de Datos
$sql = "INSERT INTO contactos (nombre, apellido, telefono, foto, email, direccion, notas) 
        VALUES (:nombre, :apellido, :telefono, :foto, :email, :direccion, :notas)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':nombre'    => $nombre,
    ':apellido'  => $apellido,
    ':telefono'  => $telefono,
    ':foto'      => $nombreFotoGuardada,
    ':email'     => !empty($email) ? $email : null,
    ':direccion' => !empty($direccion) ? $direccion : null,
    ':notas'     => !empty($notas) ? $notas : null,
]);

setFlash('success', '¡Contacto registrado exitosamente en la agenda!');
header('Location: index.php');
exit;
```

#### 2. Captura de Ejecución
- **Elemento a capturar:** Formulario `crear.php` completado con la foto de perfil cargada y la previsualización activa, seguido del mensaje flash verde de éxito en `index.php` con el nuevo contacto visible en la lista.
- **URL Visible:** `http://localhost:8000/crear.php` y `http://localhost:8000/index.php`
- *[INSERTAR AQUÍ CAPTURA DE PANTALLA 3: FORMULARIO LLENO Y REDIRECCIÓN CON ALERTA DE CONTACTO CREADO]*

#### 3. Explicación Técnica
La operación de creación (**CREATE**) se diseñó bajo una estricta separación entre la capa de presentación (`crear.php`) y la capa de lógica de negocio (`guardar.php`). El formulario HTML5 utiliza el método HTTP `POST` junto con `enctype="multipart/form-data"` para soportar la transmisión simultánea de texto y el archivo binario correspondiente a la fotografía obligatoria del contacto.

En el controlador `guardar.php`, una vez validados todos los requisitos en el servidor y transferido el archivo al directorio `archivos/` con una nomenclatura única generada mediante `bin2hex(random_bytes(8))`, se dispara la consulta `INSERT INTO contactos` con PDO. En caso de suscitarse un error en la base de datos, el script implementa un mecanismo de reversión manual que elimina la fotografía física recién subida para evitar archivos huérfanos en el disco del hosting.

---

### ESPECIFICACIÓN 4: CONSULTA DE DATOS (READ – LISTADO DE CONTACTOS)

#### 1. Captura del Código Fuente
*Archivo: `index.php` (Líneas 15 a 45)*

```php
// Consulta general de todos los contactos ordenada alfabéticamente
$sql = "SELECT * FROM contactos ORDER BY nombre ASC, apellido ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$contactos = $stmt->fetchAll();
$totalRegistros = count($contactos);
```

#### 2. Captura de Ejecución
- **Elemento a capturar:** Página principal del sistema en el navegador con la tabla de contactos desplegada, mostrando la miniatura de la foto, nombre, teléfono, correo, dirección y botones de acción.
- **URL Visible:** `http://localhost:8000/index.php`
- *[INSERTAR AQUÍ CAPTURA DE PANTALLA 4: LISTADO GENERAL DE CONTACTOS CON SU CONTADOR Y FOTOGRAFÍAS]*

#### 3. Explicación Técnica
El listado general de registros (**READ**) se procesa en `index.php` mediante la ejecución de una consulta `SELECT` que recupera la totalidad de contactos almacenados en la tabla `contactos`, ordenados alfabéticamente por nombre y apellido (`ORDER BY nombre ASC, apellido ASC`). La extracción de datos se realiza con el método `$stmt->fetchAll()`, obteniendo una matriz asociativa optimizada gracias al modo `PDO::FETCH_ASSOC`.

En la vista, la información se recorre dinámicamente mediante un ciclo `foreach`, presentando cada contacto en una fila estilizada de tabla responsiva. El sistema verifica la existencia física de la imagen en la carpeta `archivos/` mediante `file_exists()`; si el archivo fue removido o el campo está vacío, el sistema sustituye automáticamente la imagen por el avatar genérico `archivos/default.png`, previniendo enlaces rotos en la interfaz.

---

### ESPECIFICACIÓN 5: CONSULTA INDIVIDUAL DE UN CONTACTO (DETALLE)

#### 1. Captura del Código Fuente
*Archivo: `ver.php` (Líneas 10 a 35)*

```php
// Validación estricta del ID numérico obtenido por GET
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    header('Location: index.php');
    exit;
}

// Consulta parametrizada para obtener un único registro
$sql = "SELECT * FROM contactos WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);
$contacto = $stmt->fetch();

if (!$contacto) {
    setFlash('warning', 'El contacto solicitado no fue encontrado.');
    header('Location: index.php');
    exit;
}
```

#### 2. Captura de Ejecución
- **Elemento a capturar:** Vista detallada de un contacto en `ver.php?id=1` con su fotografía en alta resolución, nombre, teléfono con botón de llamada, correo con enlace de envío, dirección y fecha de alta formateada.
- **URL Visible:** `http://localhost:8000/ver.php?id=1`
- *[INSERTAR AQUÍ CAPTURA DE PANTALLA 5: TARJETA DE PERFIL Y DETALLE INDIVIDUAL DEL CONTACTO]*

#### 3. Explicación Técnica
La lectura individual de contacto (**READ individual**) se implementó en `ver.php` para ofrecer una vista minuciosa del perfil del contacto. El módulo aplica una primera capa de validación mediante la función nativa `filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)`, garantizando que el parámetro recibido a través de la URL sea obligatoriamente un número entero positivo antes de entablar cualquier interacción con el motor de base de datos.

Posteriormente, se ejecuta una consulta con el marcador `:id` y la cláusula `LIMIT 1`. Mediante `$stmt->fetch()`, se recupera exclusivamente el registro correspondiente. Si el usuario intenta forzar un identificador inexistente o manipulado, el script detecta el retorno booleano `false`, crea una notificación flash de advertencia y redirige limpiamente al listado principal sin romper la ejecución del servidor.

---

### ESPECIFICACIÓN 6: ACTUALIZACIÓN DE DATOS (UPDATE)

#### 1. Captura del Código Fuente
*Archivo: `actualizar.php` (Líneas 80 a 115)*

```php
// Actualización del registro con sentencias preparadas PDO
$sql = "UPDATE contactos 
        SET nombre = :nombre,
            apellido = :apellido,
            telefono = :telefono,
            foto = :foto,
            email = :email,
            direccion = :direccion,
            notas = :notas
        WHERE id = :id";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':nombre'    => $nombre,
    ':apellido'  => $apellido,
    ':telefono'  => $telefono,
    ':foto'      => $fotoFinal,
    ':email'     => !empty($email) ? $email : null,
    ':direccion' => !empty($direccion) ? $direccion : null,
    ':notas'     => !empty($notas) ? $notas : null,
    ':id'        => $id
]);
```

#### 2. Captura de Ejecución
- **Elemento a capturar:** Formulario de edición `editar.php?id=1` con los datos cargados previamente y el campo para reemplazar foto, seguido del resultado final en `ver.php` mostrando la información actualizada y el mensaje de confirmación.
- **URL Visible:** `http://localhost:8000/editar.php?id=1` -> `ver.php?id=1`
- *[INSERTAR AQUÍ CAPTURA DE PANTALLA 6: FORMULARIO DE EDICIÓN Y DETALLE CON DATOS ACTUALIZADOS]*

#### 3. Explicación Técnica
La actualización de registros (**UPDATE**) se divide en dos fases: la presentación del formulario prellenado (`editar.php`), el cual obtiene los datos actuales del contacto por su ID, y el procesamiento de los cambios (`actualizar.php`). El formulario transmite los datos vía `POST` acompañados del campo oculto `foto_actual`, permitiendo conservar la imagen previa si el usuario decide no seleccionar un archivo nuevo durante la edición.

En el controlador `actualizar.php`, si el usuario adjunta una nueva imagen válida, se almacena en el servidor y se procede a eliminar la fotografía anterior mediante `unlink()` (protegiendo las fotos de muestra del sistema). La sentencia `UPDATE` se ejecuta de forma segura con marcadores de posición PDO asegurando que únicamente se modifique el registro que coincida con el identificador `:id`.

---

### ESPECIFICACIÓN 7: ELIMINACIÓN DE DATOS (DELETE)

#### 1. Captura del Código Fuente
*Archivo: `eliminar.php` (Líneas 35 a 65)*

```php
// Eliminación segura vía HTTP POST con token CSRF validado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Obtener el nombre del archivo de foto para liberar espacio
    $stmtFoto = $pdo->prepare("SELECT foto FROM contactos WHERE id = :id LIMIT 1");
    $stmtFoto->execute([':id' => $id]);
    $contacto = $stmtFoto->fetch();

    // 2. Sentencia preparada DELETE
    $sql = "DELETE FROM contactos WHERE id = :id";
    $stmtDelete = $pdo->prepare($sql);
    $stmtDelete->execute([':id' => $id]);

    // 3. Borrado físico del archivo en el directorio archivos/
    if (!empty($contacto['foto']) && file_exists(__DIR__ . '/archivos/' . $contacto['foto'])) {
        unlink(__DIR__ . '/archivos/' . $contacto['foto']);
    }

    setFlash('success', 'El contacto ha sido eliminado exitosamente.');
    header('Location: index.php');
    exit;
}
```

#### 2. Captura de Ejecución
- **Elemento a capturar:** Pantalla de confirmación previa con botón rojo de advertencia `eliminar.php?id=...`, seguida de la redirección a `index.php` donde se comprueba que el contacto ya no figura en la tabla y se despliega la alerta de eliminación.
- **URL Visible:** `http://localhost:8000/eliminar.php?id=1` y `http://localhost:8000/index.php`
- *[INSERTAR AQUÍ CAPTURA DE PANTALLA 7: PANTALLA DE ADVERTENCIA DE BORRADO Y TABLA SIN EL CONTACTO]*

#### 3. Explicación Técnica
Para cumplir con los estándares de seguridad web, la operación de borrado (**DELETE**) prohíbe terminantemente la eliminación directa a través de enlaces con método GET (lo cual previene borrados accidentales causados por indexadores o bots). Cuando se invoca `eliminar.php` vía GET, el sistema renderiza una pantalla de confirmación explícita con el resumen del contacto y un botón de cancelación.

Al pulsar el botón de eliminación, se emite una petición `POST` protegida con token CSRF. El controlador ejecuta una primera consulta para rescatar el nombre de la imagen asignada, aplica la instrucción `DELETE FROM contactos WHERE id = :id` mediante sentencias preparadas de PDO, y ejecuta la función `unlink()` para purgar el archivo del almacenamiento del servidor, asegurando la integridad tanto en la base de datos como en el sistema de archivos.

---

### ESPECIFICACIÓN 8: VALIDACIÓN DE FORMULARIOS (HTML5 Y SERVIDOR)

#### 1. Captura del Código Fuente
*Archivo: `guardar.php` (Líneas 25 a 70)*

```php
// Validación estricta en el servidor (Server-Side Validation)
if (empty($nombre)) {
    $errores[] = 'El campo Nombre es obligatorio.';
} elseif (mb_strlen($nombre) > 100) {
    $errores[] = 'El Nombre no debe exceder los 100 caracteres.';
}

if (empty($telefono)) {
    $errores[] = 'El campo Teléfono es obligatorio.';
} elseif (!preg_match('/^[0-9\+\-\s\(\)]{7,20}$/', $telefono)) {
    $errores[] = 'El formato del Teléfono no es válido (7 a 20 dígitos numéricos).';
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'La dirección de correo electrónico ingresada no es válida.';
}

// Validación de archivo binario (MIME Type real y tamaño)
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $_FILES['foto']['tmp_name']);
finfo_close($finfo);
if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
    $errores[] = 'El archivo subido no es una imagen permitida (JPG, PNG o WEBP).';
}
```

#### 2. Captura de Ejecución
- **Elemento a capturar:** Formulario en el navegador mostrando las alertas de validación del cliente (tooltip nativo de HTML5 con `required`) y las alertas rojas generadas por PHP al intentar enviar datos vacíos o un correo inválido como `prueba@@sinpunto`.
- **URL Visible:** `http://localhost:8000/crear.php`
- *[INSERTAR AQUÍ CAPTURA DE PANTALLA 8: VALIDACIÓN HTML5 Y ALERTAS ROJAS DEL SERVIDOR PHP]*

#### 3. Explicación Técnica
Se implementó una estrategia de **doble validación defensiva**: del lado del cliente y del lado del servidor. En el cliente (`crear.php` y `editar.php`), se aplicaron atributos semánticos de HTML5 como `required`, `type="tel"`, `type="email"` y `pattern="[0-9\+\-\s\(\)]{7,20}"`, lo cual brinda retroalimentación instantánea al usuario y evita peticiones innecesarias.

Sin embargo, dado que las restricciones de cliente pueden ser omitidas o manipuladas desde las herramientas de desarrollador, el controlador `guardar.php` efectúa una validación exhaustiva en PHP. Se utilizan funciones como `mb_strlen()`, expresiones regulares con `preg_match()`, y `filter_var(..., FILTER_VALIDATE_EMAIL)`. En el caso del archivo adjunto, se audita el código de error en `$_FILES`, el tamaño en bytes y se analiza la cabecera binaria del archivo mediante la librería nativa `finfo` para confirmar que se trate de una imagen real.

---

### ESPECIFICACIÓN 9: SANITIZACIÓN DE SALIDA (HTMLSPECIALCHARS)

#### 1. Captura del Código Fuente
*Archivo: `config/db.php` (Líneas 60 a 70) e `index.php` (Líneas 95 a 120)*

```php
// Función de sanitización centralizada
function e($data): string {
    return htmlspecialchars((string)($data ?? ''), ENT_QUOTES, 'UTF-8');
}

// Aplicación sistemática en la plantilla de salida HTML:
<a href="ver.php?id=<?= (int)$c['id']; ?>">
    <?= e($c['nombre'] . ' ' . $c['apellido']); ?>
</a>
<a href="tel:<?= e($c['telefono']); ?>">
    <?= e($c['telefono']); ?>
</a>
```

#### 2. Captura de Ejecución
- **Elemento a capturar:** Inserción de un contacto con código malicioso de prueba, por ejemplo en el nombre: `<script>alert('XSS')</script>Juan`. En el navegador se observa cómo el nombre se imprime como texto plano inofensivo en la tabla sin ejecutar ninguna alerta o script JavaScript.
- **URL Visible:** `http://localhost:8000/index.php`
- *[INSERTAR AQUÍ CAPTURA DE PANTALLA 9: INYECCIÓN XSS NEUTRALIZADA COMO TEXTO PLANO VISIBLE]*

#### 3. Explicación Técnica
La sanitización de salida es el pilar indispensable para mitigar vulnerabilidades de **Cross-Site Scripting (XSS)** reflejado y persistente. Para ello, se diseñó la función auxiliar `e($data)` en `config/db.php`, la cual encapsula la función nativa `htmlspecialchars()` configurada con las directivas de máxima seguridad: la bandera `ENT_QUOTES` (que convierte tanto comillas dobles como comillas simples en sus entidades HTML correspondientes) y la codificación `UTF-8`.

Esta función se invoca de forma obligatoria y sistemática en todas las vistas del proyecto (`index.php`, `ver.php`, `crear.php`, `editar.php`, `eliminar.php`) sobre cada variable proveniente de la base de datos o de los arreglos `$_POST` y `$_GET`. De esta manera, si un atacante intenta inyectar etiquetas como `<script>`, `<iframe>` o atributos maliciosos como `onload=...`, el motor los transforma en texto plano inofensivo (`&lt;script&gt;`), protegiendo la sesión del usuario.

---

### ESPECIFICACIÓN 10: IMPLEMENTACIÓN DE BÚSQUEDA DE CONTACTOS

#### 1. Captura del Código Fuente
*Archivo: `index.php` (Líneas 18 a 35)*

```php
// Captura del término de búsqueda y consulta dinámica con PDO
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if (!empty($q)) {
    // Búsqueda multicampo mediante sentencias preparadas y marcadores nombrados
    $sql = "SELECT * FROM contactos 
            WHERE nombre LIKE :filtro 
               OR apellido LIKE :filtro 
               OR telefono LIKE :filtro 
               OR email LIKE :filtro 
            ORDER BY nombre ASC, apellido ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':filtro' => '%' . $q . '%']);
} else {
    $sql = "SELECT * FROM contactos ORDER BY nombre ASC, apellido ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}
$contactos = $stmt->fetchAll();
```

#### 2. Captura de Ejecución
- **Elemento a capturar:** Barra de búsqueda en la parte superior con un término ingresado (ejemplo: `Carlos` o un número telefónico `961`), mostrando únicamente los registros que coinciden, el contador filtrado ("Resultados encontrados para: Carlos") y el botón para limpiar el filtro.
- **URL Visible:** `http://localhost:8000/index.php?q=Carlos`
- *[INSERTAR AQUÍ CAPTURA DE PANTALLA 10: RESULTADOS DEL FILTRO DE BÚSQUEDA EN EL NAVEGADOR]*

#### 3. Explicación Técnica
La búsqueda dinámica de contactos se implementó mediante el método HTTP `GET` en el controlador principal `index.php`, lo que permite compartir o guardar en marcadores la URL exacta con los resultados de la consulta. El valor del parámetro `$q` se captura y limpia mediante `trim()`; si contiene texto, el flujo bifurca hacia una consulta SQL condicional que examina múltiples campos simultáneamente utilizando el operador `LIKE`.

Para garantizar la inmunidad ante inyección SQL dentro de la cláusula `LIKE`, los comodines de porcentaje `%` se concatenan en el valor del parámetro dentro del arreglo de ejecución (`[':filtro' => '%' . $q . '%']`) y jamás directamente en la cadena SQL. La interfaz presenta en tiempo real una insignia con el total de coincidencias localizadas y un botón de restablecimiento que permite regresar al catálogo completo en un solo clic.

---

## 🏁 CONCLUSIÓN TÉCNICA DEL EQUIPO

El desarrollo de este **Mini Proyecto 2** permitió consolidar la transición desde la programación básica con PHP hacia la construcción de aplicaciones web empresariales orientadas a datos. La implementación integral de **PDO con sentencias preparadas**, validación robusta y sanitización exhaustiva demuestra que es posible desarrollar sistemas dinámicos seguros, mantenibles y de alto rendimiento utilizando las mejores prácticas de la industria en PHP 8.
