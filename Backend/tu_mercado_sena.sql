-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-02-2026 a las 05:55:48
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `api_tms`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditorias`
--
-- Creación: 07-02-2026 a las 18:36:01
--

DROP TABLE IF EXISTS `auditorias`;
CREATE TABLE `auditorias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `administrador_id` bigint(20) UNSIGNED NOT NULL,
  `suceso_id` bigint(20) UNSIGNED NOT NULL,
  `descripcion` varchar(512) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `auditorias`:
--   `administrador_id`
--       `usuarios` -> `id`
--   `suceso_id`
--       `sucesos` -> `id`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bloqueados`
--
-- Creación: 07-02-2026 a las 18:36:01
--

DROP TABLE IF EXISTS `bloqueados`;
CREATE TABLE `bloqueados` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bloqueador_id` bigint(20) UNSIGNED NOT NULL,
  `bloqueado_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `bloqueados`:
--   `bloqueado_id`
--       `usuarios` -> `id`
--   `bloqueador_id`
--       `usuarios` -> `id`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--
-- Creación: 07-02-2026 a las 18:36:01
-- Última actualización: 07-02-2026 a las 19:06:14
--

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE `categorias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `categorias`:
--

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`) VALUES
(1, 'vestimenta'),
(2, 'alimento'),
(3, 'papelería'),
(4, 'herramienta'),
(5, 'cosmético'),
(6, 'deportivo'),
(7, 'dispositivo'),
(8, 'servicio'),
(9, 'social'),
(10, 'mobiliario'),
(11, 'vehículo'),
(12, 'mascota'),
(13, 'otro'),
(14, 'adorno');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chats`
--
-- Creación: 07-02-2026 a las 18:36:01
--

DROP TABLE IF EXISTS `chats`;
CREATE TABLE `chats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `comprador_id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` bigint(20) UNSIGNED NOT NULL,
  `estado_id` bigint(20) UNSIGNED NOT NULL,
  `visto_comprador` tinyint(1) NOT NULL DEFAULT 1,
  `visto_vendedor` tinyint(1) NOT NULL DEFAULT 0,
  `precio` double NOT NULL,
  `cantidad` smallint(5) UNSIGNED NOT NULL,
  `calificacion` tinyint(3) UNSIGNED DEFAULT NULL,
  `comentario` varchar(512) DEFAULT NULL,
  `fecha_venta` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `chats`:
--   `comprador_id`
--       `usuarios` -> `id`
--   `estado_id`
--       `estados` -> `id`
--   `producto_id`
--       `productos` -> `id`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentas`
--
-- Creación: 08-02-2026 a las 03:45:03
-- Última actualización: 08-02-2026 a las 03:49:49
--

DROP TABLE IF EXISTS `cuentas`;
CREATE TABLE `cuentas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(127) NOT NULL,
  `clave` varchar(32) NOT NULL,
  `notifica_correo` tinyint(1) NOT NULL DEFAULT 0,
  `notifica_push` tinyint(1) NOT NULL DEFAULT 1,
  `uso_datos` tinyint(1) NOT NULL DEFAULT 0,
  `pin` varchar(4) DEFAULT '',
  `fecha_clave` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `cuentas`:
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `denuncias`
--
-- Creación: 07-02-2026 a las 18:36:02
--

DROP TABLE IF EXISTS `denuncias`;
CREATE TABLE `denuncias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `denunciante_id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `chat_id` bigint(20) UNSIGNED NOT NULL,
  `motivo_id` bigint(20) UNSIGNED NOT NULL,
  `estado_id` bigint(20) UNSIGNED NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `denuncias`:
--   `chat_id`
--       `chats` -> `id`
--   `denunciante_id`
--       `usuarios` -> `id`
--   `estado_id`
--       `estados` -> `id`
--   `motivo_id`
--       `motivos` -> `id`
--   `producto_id`
--       `productos` -> `id`
--   `usuario_id`
--       `usuarios` -> `id`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados`
--
-- Creación: 07-02-2026 a las 18:36:01
-- Última actualización: 08-02-2026 a las 04:21:06
--

DROP TABLE IF EXISTS `estados`;
CREATE TABLE `estados` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(32) NOT NULL,
  `descripcion` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `estados`:
--

--
-- Volcado de datos para la tabla `estados`
--

INSERT INTO `estados` (`id`, `nombre`, `descripcion`) VALUES
(1, 'activo', 'usuario, producto o etc activos'),
(2, 'invisible', 'usuario, producto o etc que se ha ocultado por su propietario'),
(3, 'eliminado', 'usuario, producto o etc eliminado'),
(4, 'bloqueado', 'usuario baneado momentáneamente'),
(5, 'vendido', 'aplicado a un chat cuando se hizo la transacción'),
(6, 'esperando', 'la transacción del chat espera el visto bueno del comprador'),
(7, 'devolviendo', 'el historial abre una solicitud de devolución, a espera de respuesta del vendedor'),
(8, 'devuelto', 'el chat finalizó con una transacción que fué cancelada'),
(9, 'censurado', 'el estado del chat era vendido, pero la administración baneó la calificación y comentario'),
(10, 'denunciado', 'cuando un usuario o producto ha sido denunciado repetidas veces, mientras se revisa el caso, no será listado públicamente'),
(11, 'resuelto', 'para decir que una PQRS o denuncia ya fué tratada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `favoritos`
--
-- Creación: 07-02-2026 a las 18:36:01
--

DROP TABLE IF EXISTS `favoritos`;
CREATE TABLE `favoritos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `votante_id` bigint(20) UNSIGNED NOT NULL,
  `votado_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `favoritos`:
--   `votado_id`
--       `usuarios` -> `id`
--   `votante_id`
--       `usuarios` -> `id`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fotos`
--
-- Creación: 07-02-2026 a las 18:36:02
-- Última actualización: 08-02-2026 a las 03:50:59
--

DROP TABLE IF EXISTS `fotos`;
CREATE TABLE `fotos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` bigint(20) UNSIGNED NOT NULL,
  `imagen` varchar(80) NOT NULL,
  `actualiza` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `fotos`:
--   `producto_id`
--       `productos` -> `id`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `integridad`
--
-- Creación: 07-02-2026 a las 18:36:01
-- Última actualización: 07-02-2026 a las 19:06:14
--

DROP TABLE IF EXISTS `integridad`;
CREATE TABLE `integridad` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(32) NOT NULL,
  `descripcion` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `integridad`:
--

--
-- Volcado de datos para la tabla `integridad`
--

INSERT INTO `integridad` (`id`, `nombre`, `descripcion`) VALUES
(1, 'nuevo', 'alta calidad, recién hecho o sin desempacar, sin uso'),
(2, 'usado', 'el producto está en buena calidad pero ya ha sido usado o tiene algún tipo de desgaste'),
(3, 'reparado', 'el producto puede tener fallas pero aún funciona'),
(4, 'reciclable', 'el producto está inutilizable, pero puede ser reutilizado, reparado o desarmado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `login_ip`
--
-- Creación: 07-02-2026 a las 18:36:01
-- Última actualización: 08-02-2026 a las 03:37:34
--

DROP TABLE IF EXISTS `login_ip`;
CREATE TABLE `login_ip` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `ip_direccion` varchar(45) NOT NULL,
  `informacion` varchar(128) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `login_ip`:
--   `usuario_id`
--       `usuarios` -> `id`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--
-- Creación: 07-02-2026 a las 18:36:02
--

DROP TABLE IF EXISTS `mensajes`;
CREATE TABLE `mensajes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `es_comprador` tinyint(1) NOT NULL DEFAULT 1,
  `chat_id` bigint(20) UNSIGNED NOT NULL,
  `mensaje` varchar(512) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `mensajes`:
--   `chat_id`
--       `chats` -> `id`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `motivos`
--
-- Creación: 07-02-2026 a las 18:36:01
-- Última actualización: 07-02-2026 a las 19:06:14
--

DROP TABLE IF EXISTS `motivos`;
CREATE TABLE `motivos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(32) NOT NULL,
  `descripcion` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `motivos`:
--

--
-- Volcado de datos para la tabla `motivos`
--

INSERT INTO `motivos` (`id`, `nombre`, `descripcion`) VALUES
(1, 'pqrs_pregunta', 'mensaje de pregunta'),
(2, 'pqrs_queja', 'mensaje de queja'),
(3, 'pqrs_reclamo', 'mensaje de reclamo'),
(4, 'pqrs_sugerencia', 'mensaje de sugerencia'),
(5, 'pqrs_agradecimiento', 'mensaje de agradecimiento'),
(6, 'notifica_denuncia', 'se ha respondido algo ante una denuncia'),
(7, 'notifica_pqrs', 'se ha respondido algo a una PQRS'),
(8, 'notifica_comprador', 'un comprador potencial se ha puesto en contacto'),
(9, 'notifica_comunidad', 'ha llegado un mensaje enviado a todos los usuarios'),
(10, 'notifica_administrativa', 'un mensaje interno de la administración, por ejemplo, puedes haber sido baneado o eliminado'),
(11, 'notifica_bienvenida', 'mensaje de bienvenida al sistema'),
(12, 'notifica_oferta', 'un favorito ha publicado un nuevo producto'),
(13, 'notifica_venta', 'un vendedor ha enviado una solicitud de consolidar venta'),
(14, 'notifica_devolver', 'un comprador ha enviado una solicitud de cancelar una transacción'),
(15, 'notifica_exito', 'se ha llevado a cabo una compraventa exitosa'),
(16, 'notifica_cancela', 'se ha llevado a cabo una devolución exitosa, se cancelará la compraventa del historial'),
(17, 'notifica_califica', 'un comprador ha calificado o escrito un comentario, o lo ha modificado'),
(18, 'denuncia_acoso', 'comportamiento de acoso sexual en un chat o imágenes o descripciónes'),
(19, 'denuncia_bulling', 'comportamiento de burlas o insultos en un chat o imágenes o descripciónes'),
(20, 'denuncia_violencia', 'comportamiento que incita al odio o amenzada directamente'),
(21, 'denuncia_ilegal', 'comportamiento asociado a drogas, armas, prostitución y demás'),
(22, 'denuncia_troll', 'comportamiento enfocado en molestar y hacer perder el tiempo, por ejemplo, con negociaciónes por mamar gallo'),
(23, 'denuncia_fraude', 'se trata de vender algo malo o mediante trampas, tratan de tumbar al otro con fraudes'),
(24, 'denuncia_fake', 'un producto o perfil es meme o chisto o simplemente hace perder el tiempo al no ser una propuesta real'),
(25, 'denuncia_spam', 'un producto o perfil aparece muchas veces como si lo pusieran en demasia para llamar la atención'),
(26, 'denuncia_sexual', 'un perfil o producto exhibe temáticas sexuales o pornográficas que incomodan a la comunidad');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--
-- Creación: 07-02-2026 a las 18:36:01
--

DROP TABLE IF EXISTS `notificaciones`;
CREATE TABLE `notificaciones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `motivo_id` bigint(20) UNSIGNED NOT NULL,
  `mensaje` varchar(255) NOT NULL,
  `visto` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `notificaciones`:
--   `motivo_id`
--       `motivos` -> `id`
--   `usuario_id`
--       `usuarios` -> `id`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `papelera`
--
-- Creación: 07-02-2026 a las 18:36:01
--

DROP TABLE IF EXISTS `papelera`;
CREATE TABLE `papelera` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `mensaje` varchar(512) NOT NULL,
  `imagen` varchar(80) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `papelera`:
--   `usuario_id`
--       `usuarios` -> `id`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pqrs`
--
-- Creación: 07-02-2026 a las 18:36:01
--

DROP TABLE IF EXISTS `pqrs`;
CREATE TABLE `pqrs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `mensaje` varchar(512) NOT NULL,
  `motivo_id` bigint(20) UNSIGNED NOT NULL,
  `estado_id` bigint(20) UNSIGNED NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `pqrs`:
--   `estado_id`
--       `estados` -> `id`
--   `motivo_id`
--       `motivos` -> `id`
--   `usuario_id`
--       `usuarios` -> `id`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--
-- Creación: 07-02-2026 a las 18:36:01
-- Última actualización: 08-02-2026 a las 03:50:59
--

DROP TABLE IF EXISTS `productos`;
CREATE TABLE `productos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(64) NOT NULL,
  `subcategoria_id` bigint(20) UNSIGNED NOT NULL,
  `integridad_id` bigint(20) UNSIGNED NOT NULL,
  `vendedor_id` bigint(20) UNSIGNED NOT NULL,
  `estado_id` bigint(20) UNSIGNED NOT NULL,
  `descripcion` varchar(512) NOT NULL,
  `precio` double NOT NULL,
  `disponibles` smallint(5) UNSIGNED NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualiza` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `productos`:
--   `estado_id`
--       `estados` -> `id`
--   `integridad_id`
--       `integridad` -> `id`
--   `subcategoria_id`
--       `subcategorias` -> `id`
--   `vendedor_id`
--       `usuarios` -> `id`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--
-- Creación: 07-02-2026 a las 18:36:01
-- Última actualización: 07-02-2026 a las 18:36:01
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `roles`:
--

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`) VALUES
(2, 'administrador'),
(3, 'master'),
(1, 'prosumer');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subcategorias`
--
-- Creación: 07-02-2026 a las 18:36:01
-- Última actualización: 07-02-2026 a las 19:06:14
--

DROP TABLE IF EXISTS `subcategorias`;
CREATE TABLE `subcategorias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(32) NOT NULL,
  `categoria_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `subcategorias`:
--   `categoria_id`
--       `categorias` -> `id`
--

--
-- Volcado de datos para la tabla `subcategorias`
--

INSERT INTO `subcategorias` (`id`, `nombre`, `categoria_id`) VALUES
(1, 'otro', 2),
(2, 'postre o helado', 2),
(3, 'fruta o verdura fresca', 2),
(4, 'carne o huevos', 2),
(5, 'especias o aditivos', 2),
(6, 'almuerzo o desayuno', 2),
(7, 'chatarra preparada', 2),
(8, 'chatarra industrial', 2),
(9, 'pan o pastel', 2),
(10, 'bebidas', 2),
(21, 'otro', 5),
(22, 'cuidado de la piel', 5),
(23, 'cuidado del pelo', 5),
(24, 'labial', 5),
(25, 'sombra', 5),
(26, 'delineador', 5),
(27, 'piercing', 5),
(28, 'tatuaje', 5),
(29, 'maniquiur', 5),
(30, 'peluqueria', 5),
(31, 'otro', 6),
(32, 'balón', 6),
(33, 'pesas', 6),
(34, 'suplemento alimenticio', 6),
(35, 'patineta o patines', 6),
(36, 'implementos acuaticos', 6),
(37, 'implementos terrestres', 6),
(38, 'implementos extremos', 6),
(39, 'arte marcial o lucha', 6),
(40, 'aseo deportivo', 6),
(41, 'otro', 7),
(42, 'computador de escritorio', 7),
(43, 'computador portátil', 7),
(44, 'periféricos para computador', 7),
(45, 'celular', 7),
(46, 'cámara fotográfica', 7),
(47, 'calculadora o mediciónes', 7),
(48, 'tableta para arte', 7),
(49, 'audifónos, reloj o corporales', 7),
(50, 'sistema de seguridad', 7),
(51, 'otro', 4),
(52, 'taladro, pulidora o similar', 4),
(53, 'martillo, alicate o similar', 4),
(54, 'licuadora, microondas o similar', 4),
(55, 'seguridad para ajustar bicicleta', 4),
(56, 'escuadra o regla para dibujo', 4),
(57, 'metro o medidores', 4),
(58, 'tijera, visturí o similar', 4),
(59, 'ventilador o aire acondicionado', 4),
(60, 'kit de mecánico', 4),
(61, 'otro', 4),
(62, 'taladro, pulidora o similar', 4),
(63, 'martillo, alicate o similar', 4),
(64, 'licuadora, microondas o similar', 4),
(65, 'seguridad para ajustar bicicleta', 4),
(66, 'escuadra o regla para dibujo', 4),
(67, 'metro o medidores', 4),
(68, 'tijera, visturí o similar', 4),
(69, 'ventilador o aire acondicionado', 4),
(70, 'kit de mecánico', 4),
(71, 'otro', 12),
(72, 'perro', 12),
(73, 'gato', 12),
(74, 'pez', 12),
(75, 'alimento', 12),
(76, 'correa', 12),
(77, 'juguete', 12),
(78, 'roedor', 12),
(79, 'tapete', 12),
(80, 'ropa', 12),
(81, 'otro', 10),
(82, 'silla regulable', 10),
(83, 'silla estática', 10),
(84, 'sillón grande', 10),
(85, 'mesa', 10),
(86, 'cama o colchón', 10),
(87, 'matera', 10),
(88, 'armario o nochero', 10),
(89, 'escritorio', 10),
(90, 'tapete', 10),
(91, 'otro', 13),
(92, 'otro', 3),
(93, 'cartónes o cajas', 3),
(94, 'telas y costura', 3),
(95, 'pegamentos', 3),
(96, 'cuadernos, carpetas', 3),
(97, 'colores, pinturas, pinceles', 3),
(98, 'libros', 3),
(99, 'lápices, marcadores, lapiceros', 3),
(100, 'borradores, sacapuntas', 3),
(101, 'papel, fomi, cartulina', 3),
(102, 'otro', 8),
(103, 'entrenamiento deportivo', 8),
(104, 'eseñanza artística', 8),
(105, 'enseñanza tecnológica', 8),
(106, 'mantenimiento computadora', 8),
(107, 'reparación dispositivos', 8),
(108, 'preparación de comidas', 8),
(109, 'documentación', 8),
(110, 'creación de arte o manualidades', 8),
(111, 'cuidado y aseo', 8),
(112, 'otro', 9),
(113, 'juego deportivo', 9),
(114, 'juego videojuegos', 9),
(115, 'practicar idiomas', 9),
(116, 'fiesta y baile', 9),
(117, 'charlar', 9),
(118, 'emprendimiento', 9),
(119, 'paseos y viajes', 9),
(120, 'seguir en redes', 9),
(121, 'religión e ideologías', 9),
(122, 'otro', 11),
(123, 'bicicleta', 11),
(124, 'moto de gasolina', 11),
(125, 'casco de moto', 11),
(126, 'moto eléctrica', 11),
(127, 'bici o patín eléctricos', 11),
(128, 'carro', 11),
(129, 'chaleco, guantes o vestimenta', 11),
(130, 'repuesto', 11),
(131, 'parrillas o implementos', 11),
(132, 'otro', 1),
(133, 'calzado', 1),
(134, 'sombrero', 1),
(135, 'pantalón', 1),
(136, 'camisa', 1),
(137, 'vestido', 1),
(138, 'falda', 1),
(139, 'medias o guantes', 1),
(140, 'chaleco o buzo', 1),
(141, 'colgandijas', 1),
(142, 'colgantes', 14),
(143, 'figurillas', 14),
(144, 'materas o jardín', 14),
(145, 'de metal', 14),
(146, 'de plástico', 14),
(147, 'de madera', 14),
(148, 'de porcelana', 14),
(149, 'afiches o pinturas', 14),
(150, 'peluches', 14),
(151, 'otro', 14);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sucesos`
--
-- Creación: 07-02-2026 a las 18:36:01
-- Última actualización: 07-02-2026 a las 19:06:14
--

DROP TABLE IF EXISTS `sucesos`;
CREATE TABLE `sucesos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(32) NOT NULL,
  `descripcion` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `sucesos`:
--

--
-- Volcado de datos para la tabla `sucesos`
--

INSERT INTO `sucesos` (`id`, `nombre`, `descripcion`) VALUES
(1, 'estado_usuario', 'ha cambiado el estado de un usuario, por ejemplo a activo, eliminado, baneado'),
(2, 'rol_cambiado', 'se ha modificado que un usuario sea o deje de ser administrador'),
(3, 'ver_chat', 'buscando ilegalidades ha entrado a revisar una conversación'),
(4, 'enviar_mail', 'ha enviado un mail a un usuario, lo que también disparará una notificación'),
(5, 'constante_modificada', 'creó, destruyó o editó una constante de la DB por ejemplo, categorías'),
(6, 'cambio_password', 'obtuvo una clave de acceso para recuperar una contraseña o crear una cuenta sin correo institucional'),
(7, 'noticia_masiva', 'envió una notificación y email a todos los usuarios'),
(8, 'estado_producto', 'cambio un producto poniéndolo como eliminado o activo por ejemplo'),
(9, 'respuesta_pqrs', 'marcó una PQRS como resuelta ya que hizo alguna acción para atenderla'),
(10, 'respuesta_denuncia', 'marcó una denuncia como resuelta pues confirma que hizo algo para atenderla'),
(11, 'estado_chat', 'modificó la visibilidad de un historial de compraventa, posiblemente deshabilitando calificación y comentario');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tokens_de_sesion`
--
-- Creación: 07-02-2026 a las 23:33:20
-- Última actualización: 08-02-2026 a las 03:37:34
--

DROP TABLE IF EXISTS `tokens_de_sesion`;
CREATE TABLE `tokens_de_sesion` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cuenta_id` bigint(20) UNSIGNED NOT NULL,
  `dispositivo` enum('web','movil','desktop') NOT NULL,
  `jti` varchar(255) NOT NULL,
  `ultimo_uso` timestamp NULL DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualiza` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `tokens_de_sesion`:
--   `cuenta_id`
--       `cuentas` -> `id`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--
-- Creación: 07-02-2026 a las 18:36:01
-- Última actualización: 08-02-2026 a las 04:41:27
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cuenta_id` bigint(20) UNSIGNED NOT NULL,
  `nickname` varchar(32) NOT NULL,
  `imagen` varchar(80) NOT NULL,
  `descripcion` varchar(512) NOT NULL,
  `link` varchar(128) DEFAULT NULL,
  `rol_id` bigint(20) UNSIGNED NOT NULL,
  `estado_id` bigint(20) UNSIGNED NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualiza` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fecha_reciente` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `usuarios`:
--   `cuenta_id`
--       `cuentas` -> `id`
--   `estado_id`
--       `estados` -> `id`
--   `rol_id`
--       `roles` -> `id`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vistos`
--
-- Creación: 07-02-2026 a las 18:36:01
--

DROP TABLE IF EXISTS `vistos`;
CREATE TABLE `vistos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED NOT NULL,
  `producto_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `vistos`:
--   `producto_id`
--       `productos` -> `id`
--   `usuario_id`
--       `usuarios` -> `id`
--

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `auditorias`
--
ALTER TABLE `auditorias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auditorias_administrador_id_foreign` (`administrador_id`),
  ADD KEY `auditorias_suceso_id_foreign` (`suceso_id`);

--
-- Indices de la tabla `bloqueados`
--
ALTER TABLE `bloqueados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bloqueados_bloqueador_id_foreign` (`bloqueador_id`),
  ADD KEY `bloqueados_bloqueado_id_foreign` (`bloqueado_id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chats_comprador_id_foreign` (`comprador_id`),
  ADD KEY `chats_producto_id_foreign` (`producto_id`),
  ADD KEY `chats_estado_id_foreign` (`estado_id`);

--
-- Indices de la tabla `cuentas`
--
ALTER TABLE `cuentas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cuentas_email_unique` (`email`);

--
-- Indices de la tabla `denuncias`
--
ALTER TABLE `denuncias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `denuncias_denunciante_id_foreign` (`denunciante_id`),
  ADD KEY `denuncias_producto_id_foreign` (`producto_id`),
  ADD KEY `denuncias_usuario_id_foreign` (`usuario_id`),
  ADD KEY `denuncias_chat_id_foreign` (`chat_id`),
  ADD KEY `denuncias_motivo_id_foreign` (`motivo_id`),
  ADD KEY `denuncias_estado_id_foreign` (`estado_id`);

--
-- Indices de la tabla `estados`
--
ALTER TABLE `estados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `estados_nombre_unique` (`nombre`);

--
-- Indices de la tabla `favoritos`
--
ALTER TABLE `favoritos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `favoritos_votante_id_foreign` (`votante_id`),
  ADD KEY `favoritos_votado_id_foreign` (`votado_id`);

--
-- Indices de la tabla `fotos`
--
ALTER TABLE `fotos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fotos_producto_id_foreign` (`producto_id`);

--
-- Indices de la tabla `integridad`
--
ALTER TABLE `integridad`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `login_ip`
--
ALTER TABLE `login_ip`
  ADD PRIMARY KEY (`id`),
  ADD KEY `login_ip_usuario_id_foreign` (`usuario_id`);

--
-- Indices de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mensajes_chat_id_foreign` (`chat_id`);

--
-- Indices de la tabla `motivos`
--
ALTER TABLE `motivos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notificaciones_usuario_id_foreign` (`usuario_id`),
  ADD KEY `notificaciones_motivo_id_foreign` (`motivo_id`);

--
-- Indices de la tabla `papelera`
--
ALTER TABLE `papelera`
  ADD PRIMARY KEY (`id`),
  ADD KEY `papelera_usuario_id_foreign` (`usuario_id`);

--
-- Indices de la tabla `pqrs`
--
ALTER TABLE `pqrs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pqrs_usuario_id_foreign` (`usuario_id`),
  ADD KEY `pqrs_motivo_id_foreign` (`motivo_id`),
  ADD KEY `pqrs_estado_id_foreign` (`estado_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `productos_subcategoria_id_foreign` (`subcategoria_id`),
  ADD KEY `productos_integridad_id_foreign` (`integridad_id`),
  ADD KEY `productos_vendedor_id_foreign` (`vendedor_id`),
  ADD KEY `productos_estado_id_foreign` (`estado_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_nombre_unique` (`nombre`);

--
-- Indices de la tabla `subcategorias`
--
ALTER TABLE `subcategorias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subcategorias_categoria_id_foreign` (`categoria_id`);

--
-- Indices de la tabla `sucesos`
--
ALTER TABLE `sucesos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tokens_de_sesion`
--
ALTER TABLE `tokens_de_sesion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tokens_de_sesion_cuenta_id_dispositivo_unique` (`cuenta_id`,`dispositivo`),
  ADD UNIQUE KEY `tokens_de_sesion_jti_unique` (`jti`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuarios_cuenta_id_unique` (`cuenta_id`),
  ADD UNIQUE KEY `usuarios_nickname_unique` (`nickname`),
  ADD KEY `usuarios_rol_id_foreign` (`rol_id`),
  ADD KEY `usuarios_estado_id_foreign` (`estado_id`);

--
-- Indices de la tabla `vistos`
--
ALTER TABLE `vistos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vistos_usuario_id_foreign` (`usuario_id`),
  ADD KEY `vistos_producto_id_foreign` (`producto_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `auditorias`
--
ALTER TABLE `auditorias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bloqueados`
--
ALTER TABLE `bloqueados`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `chats`
--
ALTER TABLE `chats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cuentas`
--
ALTER TABLE `cuentas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `denuncias`
--
ALTER TABLE `denuncias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estados`
--
ALTER TABLE `estados`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `favoritos`
--
ALTER TABLE `favoritos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `fotos`
--
ALTER TABLE `fotos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `integridad`
--
ALTER TABLE `integridad`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `login_ip`
--
ALTER TABLE `login_ip`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `motivos`
--
ALTER TABLE `motivos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `papelera`
--
ALTER TABLE `papelera`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pqrs`
--
ALTER TABLE `pqrs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `subcategorias`
--
ALTER TABLE `subcategorias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=152;

--
-- AUTO_INCREMENT de la tabla `sucesos`
--
ALTER TABLE `sucesos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `tokens_de_sesion`
--
ALTER TABLE `tokens_de_sesion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vistos`
--
ALTER TABLE `vistos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `auditorias`
--
ALTER TABLE `auditorias`
  ADD CONSTRAINT `auditorias_administrador_id_foreign` FOREIGN KEY (`administrador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `auditorias_suceso_id_foreign` FOREIGN KEY (`suceso_id`) REFERENCES `sucesos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `bloqueados`
--
ALTER TABLE `bloqueados`
  ADD CONSTRAINT `bloqueados_bloqueado_id_foreign` FOREIGN KEY (`bloqueado_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bloqueados_bloqueador_id_foreign` FOREIGN KEY (`bloqueador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `chats`
--
ALTER TABLE `chats`
  ADD CONSTRAINT `chats_comprador_id_foreign` FOREIGN KEY (`comprador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chats_estado_id_foreign` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chats_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `denuncias`
--
ALTER TABLE `denuncias`
  ADD CONSTRAINT `denuncias_chat_id_foreign` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `denuncias_denunciante_id_foreign` FOREIGN KEY (`denunciante_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `denuncias_estado_id_foreign` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `denuncias_motivo_id_foreign` FOREIGN KEY (`motivo_id`) REFERENCES `motivos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `denuncias_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `denuncias_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `favoritos`
--
ALTER TABLE `favoritos`
  ADD CONSTRAINT `favoritos_votado_id_foreign` FOREIGN KEY (`votado_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favoritos_votante_id_foreign` FOREIGN KEY (`votante_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `fotos`
--
ALTER TABLE `fotos`
  ADD CONSTRAINT `fotos_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `login_ip`
--
ALTER TABLE `login_ip`
  ADD CONSTRAINT `login_ip_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD CONSTRAINT `mensajes_chat_id_foreign` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_motivo_id_foreign` FOREIGN KEY (`motivo_id`) REFERENCES `motivos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notificaciones_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `papelera`
--
ALTER TABLE `papelera`
  ADD CONSTRAINT `papelera_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pqrs`
--
ALTER TABLE `pqrs`
  ADD CONSTRAINT `pqrs_estado_id_foreign` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pqrs_motivo_id_foreign` FOREIGN KEY (`motivo_id`) REFERENCES `motivos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pqrs_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_estado_id_foreign` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `productos_integridad_id_foreign` FOREIGN KEY (`integridad_id`) REFERENCES `integridad` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `productos_subcategoria_id_foreign` FOREIGN KEY (`subcategoria_id`) REFERENCES `subcategorias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `productos_vendedor_id_foreign` FOREIGN KEY (`vendedor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `subcategorias`
--
ALTER TABLE `subcategorias`
  ADD CONSTRAINT `subcategorias_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tokens_de_sesion`
--
ALTER TABLE `tokens_de_sesion`
  ADD CONSTRAINT `tokens_de_sesion_cuenta_id_foreign` FOREIGN KEY (`cuenta_id`) REFERENCES `cuentas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_cuenta_id_foreign` FOREIGN KEY (`cuenta_id`) REFERENCES `cuentas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuarios_estado_id_foreign` FOREIGN KEY (`estado_id`) REFERENCES `estados` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuarios_rol_id_foreign` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `vistos`
--
ALTER TABLE `vistos`
  ADD CONSTRAINT `vistos_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vistos_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
