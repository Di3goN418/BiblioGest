-- BiblioGest v2.0 — Base de datos normalizada
-- Cambios: libros normalizado (autores, géneros separados), ISBN, año publicación
--          usuarios simplificado (sin password/rol, con teléfono)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `bibliotecanew`;
USE `bibliotecanew`;

-- ─────────────────────────────────────────────
--  ADMINS  (sin cambios)
-- ─────────────────────────────────────────────
CREATE TABLE `admins` (
  `id`       int(11)      NOT NULL AUTO_INCREMENT,
  `usuario`  varchar(50)  NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `admins` VALUES (1, 'admin', '1234');

-- ─────────────────────────────────────────────
--  AUTORES  (nueva tabla)
-- ─────────────────────────────────────────────
CREATE TABLE `autores` (
  `id`     int(11)      NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `autores` VALUES
(1, 'Antoine de Saint-Exupéry'),
(2, 'George Orwell');

-- ─────────────────────────────────────────────
--  GÉNEROS  (nueva tabla)
-- ─────────────────────────────────────────────
CREATE TABLE `generos` (
  `id`     int(11)     NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `generos` VALUES
(1, 'Infantil'),
(2, 'Ciencia ficción'),
(3, 'Novela'),
(4, 'Historia'),
(5, 'Filosofía'),
(6, 'Ciencia'),
(7, 'Biografía'),
(8, 'Terror'),
(9, 'Romance'),
(10, 'Poesía');

-- ─────────────────────────────────────────────
--  LIBROS  (normalizado: sin autor/categoría directos)
-- ─────────────────────────────────────────────
CREATE TABLE `libros` (
  `id`               int(11)      NOT NULL AUTO_INCREMENT,
  `titulo`           varchar(150) NOT NULL,
  `isbn`             varchar(20)  DEFAULT NULL,
  `anio_publicacion` year(4)      DEFAULT NULL,
  `editorial`        varchar(100) DEFAULT NULL,
  `stock`            int(11)      DEFAULT 0,
  `imagen`           varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `libros` VALUES
(1, 'El principito', '978-84-261-3674-5', 1943, 'Planeta',    5, 'principito.jpg'),
(2, '1984',          '978-84-9759-577-2', 1949, 'Debolsillo', 3, '1984.jpg');

-- ─────────────────────────────────────────────
--  LIBRO_AUTORES  (tabla de unión)
-- ─────────────────────────────────────────────
CREATE TABLE `libro_autores` (
  `id_libro` int(11) NOT NULL,
  `id_autor` int(11) NOT NULL,
  PRIMARY KEY (`id_libro`, `id_autor`),
  FOREIGN KEY (`id_libro`) REFERENCES `libros`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_autor`) REFERENCES `autores`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `libro_autores` VALUES (1,1), (2,2);

-- ─────────────────────────────────────────────
--  LIBRO_GENEROS  (tabla de unión)
-- ─────────────────────────────────────────────
CREATE TABLE `libro_generos` (
  `id_libro`  int(11) NOT NULL,
  `id_genero` int(11) NOT NULL,
  PRIMARY KEY (`id_libro`, `id_genero`),
  FOREIGN KEY (`id_libro`)  REFERENCES `libros`(`id`)  ON DELETE CASCADE,
  FOREIGN KEY (`id_genero`) REFERENCES `generos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `libro_generos` VALUES (1,1), (2,2);

-- ─────────────────────────────────────────────
--  USUARIOS  (simplificado: sin password/rol, con teléfono)
-- ─────────────────────────────────────────────
CREATE TABLE `usuarios` (
  `id`       int(11)      NOT NULL AUTO_INCREMENT,
  `nombre`   varchar(100) NOT NULL,
  `correo`   varchar(100) DEFAULT NULL,
  `telefono` varchar(20)  DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `usuarios` VALUES
(1, 'Ana Torres',  NULL, NULL),
(2, 'Luis Gómez',  NULL, NULL);

-- ─────────────────────────────────────────────
--  PRÉSTAMOS  (sin cambios)
-- ─────────────────────────────────────────────
CREATE TABLE `prestamos` (
  `id`               int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario`       int(11) DEFAULT NULL,
  `id_libro`         int(11) DEFAULT NULL,
  `fecha_prestamo`   date    DEFAULT NULL,
  `fecha_devolucion` date    DEFAULT NULL,
  `estado`           varchar(20) DEFAULT 'Activo',
  `fecha_entrega`    date    DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`),
  FOREIGN KEY (`id_libro`)   REFERENCES `libros`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `prestamos` VALUES
(1, 1, 1, '2025-06-01', '2025-06-15', 'Activo', NULL),
(2, 2, 2, '2025-06-05', '2025-06-20', 'Activo', NULL);

-- ─────────────────────────────────────────────
--  MULTAS  (sin cambios, columna corregida a `dias`)
-- ─────────────────────────────────────────────
CREATE TABLE `multas` (
  `id`          int(11)        NOT NULL AUTO_INCREMENT,
  `id_prestamo` int(11)        DEFAULT NULL,
  `dias`        int(11)        DEFAULT NULL,
  `monto`       decimal(10,2)  DEFAULT NULL,
  `estado`      varchar(20)    DEFAULT 'Pendiente',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_prestamo`) REFERENCES `prestamos`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
