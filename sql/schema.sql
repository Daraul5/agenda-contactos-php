-- ====================================================================
-- MINI PROYECTO 2 - UNIDAD 4: PROGRAMACIÓN WEB
-- Agenda de Contactos con PHP y MySQL (PDO)
-- ====================================================================

-- 1. Creación de la base de datos si no existe
CREATE DATABASE IF NOT EXISTS `agenda_db` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `agenda_db`;

-- 2. Creación de la tabla de contactos
DROP TABLE IF EXISTS `contactos`;
CREATE TABLE IF NOT EXISTS `contactos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL,
    `apellido` VARCHAR(100) NOT NULL,
    `telefono` VARCHAR(20) NOT NULL,
    `foto` VARCHAR(255) NOT NULL,
    `email` VARCHAR(150) NULL,
    `direccion` VARCHAR(255) NULL,
    `notas` TEXT NULL,
    `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Inserción de datos iniciales de prueba (Seed Data)
INSERT INTO `contactos` (`nombre`, `apellido`, `telefono`, `foto`, `email`, `direccion`, `notas`) VALUES
('Carlos', 'Ramírez Mendoza', '9611234567', 'contacto1.png', 'carlos.ramirez@example.com', 'Av. Central Poniente #123, Tuxtla Gutiérrez', 'Colega del departamento de desarrollo de software.'),
('Ana Paula', 'López Domínguez', '9619876543', 'contacto2.png', 'ana.lopez@example.com', 'Blvd. Belisario Domínguez #450, Tuxtla Gutiérrez', 'Coordinadora de proyectos y analista de calidad.'),
('Mario', 'Lara Velázquez', '9615551234', 'contacto3.png', 'mario.lara@example.com', 'Calle 5a Norte Oriente #89, Tuxtla Gutiérrez', 'Contacto para servicios y mantenimiento de infraestructura.');
