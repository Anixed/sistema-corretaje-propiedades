-- phpMyAdmin SQL Dump
-- version 3.5.0
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 10-09-2013 a las 02:16:31
-- Versión del servidor: 5.0.51b-community-nt-log
-- Versión de PHP: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `sistema_corretaje`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administracion`
--

CREATE TABLE IF NOT EXISTS `administracion` (
  `id` int(1) NOT NULL auto_increment,
  `UF` decimal(7,2) NOT NULL,
  `ultima_modificacion` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Volcado de datos para la tabla `administracion`
--

INSERT INTO `administracion` (`id`, `UF`, `ultima_modificacion`) VALUES
(1, '22559.48', '2012-08-27 02:05:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `agenda_visitas`
--

CREATE TABLE IF NOT EXISTS `agenda_visitas` (
  `id_visita` int(11) unsigned NOT NULL auto_increment,
  `id_cliente` int(11) unsigned NOT NULL,
  `id_propiedad` int(11) unsigned NOT NULL,
  `id_vendedor` int(11) unsigned NOT NULL,
  `hora_in` time NOT NULL default '00:00:00',
  `hora_out` time NOT NULL default '00:00:00',
  `fecha_visita` date NOT NULL default '0000-00-00',
  `observaciones` text NOT NULL,
  `estado` int(1) NOT NULL default '1',
  `fecha_ingreso` datetime NOT NULL default '0000-00-00 00:00:00',
  `fecha_finalizada` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id_visita`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_vendedor` (`id_vendedor`),
  KEY `id_propiedades` (`id_propiedad`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;

--
-- Volcado de datos para la tabla `agenda_visitas`
--

INSERT INTO `agenda_visitas` (`id_visita`, `id_cliente`, `id_propiedad`, `id_vendedor`, `hora_in`, `hora_out`, `fecha_visita`, `observaciones`, `estado`, `fecha_ingreso`, `fecha_finalizada`) VALUES
(14, 4, 19, 2, '11:30:00', '12:00:00', '2012-09-07', '', 1, '2012-09-03 03:26:21', '0000-00-00 00:00:00'),
(15, 5, 19, 2, '12:30:00', '13:00:00', '2012-09-07', '', 1, '2012-09-03 03:28:08', '0000-00-00 00:00:00'),
(16, 4, 19, 2, '15:00:00', '15:30:00', '2012-09-07', '', 1, '2012-09-03 03:30:15', '0000-00-00 00:00:00'),
(17, 4, 18, 2, '09:30:00', '10:00:00', '2012-09-07', 'Al cliente no le intereso la propiedad.', 2, '2012-09-03 03:32:57', '2012-09-03 03:38:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calefaccion`
--

CREATE TABLE IF NOT EXISTS `calefaccion` (
  `id` int(4) unsigned NOT NULL auto_increment,
  `calefaccion_nombre` varchar(25) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Volcado de datos para la tabla `calefaccion`
--

INSERT INTO `calefaccion` (`id`, `calefaccion_nombre`) VALUES
(1, 'Central'),
(2, 'Comb. Lenta'),
(3, 'Gas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caracteristicas_bodega`
--

CREATE TABLE IF NOT EXISTS `caracteristicas_bodega` (
  `id_caracteristicas` int(11) unsigned NOT NULL auto_increment,
  `id_propiedad` int(11) unsigned NOT NULL,
  `banos` int(2) NOT NULL default '0',
  `num_privados` int(2) NOT NULL default '0',
  `num_estacionamientos` int(2) NOT NULL default '0',
  `tipo_construccion` varchar(10) NOT NULL,
  `superficie_total` decimal(10,2) NOT NULL default '0.00',
  `superficie_construida` decimal(10,2) NOT NULL default '0.00',
  `mtrs_frente` decimal(10,2) NOT NULL default '0.00',
  `mtrs_fondo` decimal(10,2) NOT NULL default '0.00',
  `otras_caracteristicas` text NOT NULL,
  PRIMARY KEY  (`id_caracteristicas`),
  UNIQUE KEY `id_propiedad` (`id_propiedad`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caracteristicas_campo`
--

CREATE TABLE IF NOT EXISTS `caracteristicas_campo` (
  `id_caracteristicas` int(11) unsigned NOT NULL auto_increment,
  `id_propiedad` int(11) unsigned NOT NULL,
  `hectas_superficie` decimal(10,2) NOT NULL default '0.00',
  `hectas_empastadas` decimal(10,2) NOT NULL default '0.00',
  `hectas_riego` decimal(10,2) NOT NULL default '0.00',
  `num_potreros` int(3) NOT NULL default '0',
  `num_casas_patronales` int(2) NOT NULL default '0',
  `num_casas_inquilinos` int(2) NOT NULL default '0',
  `num_bodegas` int(2) NOT NULL default '0',
  `clasificacion_suelos` varchar(20) NOT NULL,
  `aptitudes` varchar(250) NOT NULL,
  `otras_caracteristicas` text NOT NULL,
  PRIMARY KEY  (`id_caracteristicas`),
  UNIQUE KEY `id_propiedad` (`id_propiedad`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Volcado de datos para la tabla `caracteristicas_campo`
--

INSERT INTO `caracteristicas_campo` (`id_caracteristicas`, `id_propiedad`, `hectas_superficie`, `hectas_empastadas`, `hectas_riego`, `num_potreros`, `num_casas_patronales`, `num_casas_inquilinos`, `num_bodegas`, `clasificacion_suelos`, `aptitudes`, `otras_caracteristicas`) VALUES
(1, 16, '0.00', '0.00', '0.00', 0, 0, 0, 0, '', 'a:2:{i:1;s:8:"Forestal";i:3;s:10:"Turística";}', 'a:0:{}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caracteristicas_casa`
--

CREATE TABLE IF NOT EXISTS `caracteristicas_casa` (
  `id_caracteristicas` int(11) unsigned NOT NULL auto_increment,
  `id_propiedad` int(11) unsigned NOT NULL,
  `dormitorios` int(2) NOT NULL default '0',
  `banos` int(2) NOT NULL default '0',
  `num_pisos` int(2) NOT NULL default '0',
  `tipo_construccion` varchar(10) NOT NULL,
  `calefaccion` varchar(15) NOT NULL,
  `superficie_total` decimal(10,2) NOT NULL default '0.00',
  `superficie_construida` decimal(10,2) NOT NULL default '0.00',
  `otras_caracteristicas` text NOT NULL,
  PRIMARY KEY  (`id_caracteristicas`),
  UNIQUE KEY `id_propiedad` (`id_propiedad`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

--
-- Volcado de datos para la tabla `caracteristicas_casa`
--

INSERT INTO `caracteristicas_casa` (`id_caracteristicas`, `id_propiedad`, `dormitorios`, `banos`, `num_pisos`, `tipo_construccion`, `calefaccion`, `superficie_total`, `superficie_construida`, `otras_caracteristicas`) VALUES
(5, 5, 4, 3, 0, '', '', '0.00', '0.00', 'a:0:{}'),
(6, 6, 3, 1, 0, '', '', '0.00', '0.00', 'a:0:{}'),
(7, 7, 3, 2, 0, '', '', '0.00', '0.00', 'a:1:{s:15:"cocina_amoblada";s:2:"Si";}'),
(10, 18, 4, 3, 2, 'Sólida', 'Central', '690.00', '145.00', 'a:5:{s:5:"suite";s:2:"Si";s:5:"cable";s:2:"Si";s:8:"telefono";s:2:"Si";s:15:"cocina_amoblada";s:2:"Si";s:14:"living_comedor";s:8:"Separado";}'),
(12, 19, 4, 2, 0, '', '', '0.00', '0.00', 'a:0:{}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caracteristicas_departamento`
--

CREATE TABLE IF NOT EXISTS `caracteristicas_departamento` (
  `id_caracteristicas` int(11) unsigned NOT NULL auto_increment,
  `id_propiedad` int(11) unsigned NOT NULL,
  `dormitorios` int(2) NOT NULL default '0',
  `banos` int(2) NOT NULL default '0',
  `piso_numero` int(2) NOT NULL default '0',
  `num_estacionamientos` int(2) NOT NULL default '0',
  `orientacion` varchar(20) NOT NULL,
  `tipo_construccion` varchar(10) NOT NULL,
  `calefaccion` varchar(15) NOT NULL,
  `superficie_construida` decimal(10,2) NOT NULL default '0.00',
  `gastos_comunes` decimal(12,0) NOT NULL default '0',
  `otras_caracteristicas` text NOT NULL,
  PRIMARY KEY  (`id_caracteristicas`),
  UNIQUE KEY `id_propiedad` (`id_propiedad`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Volcado de datos para la tabla `caracteristicas_departamento`
--

INSERT INTO `caracteristicas_departamento` (`id_caracteristicas`, `id_propiedad`, `dormitorios`, `banos`, `piso_numero`, `num_estacionamientos`, `orientacion`, `tipo_construccion`, `calefaccion`, `superficie_construida`, `gastos_comunes`, `otras_caracteristicas`) VALUES
(1, 4, 2, 3, 0, 0, 'Sur-Oriente', '', '', '0.00', '0', 'a:2:{s:15:"cocina_amoblada";s:2:"Si";s:7:"terraza";s:2:"Si";}'),
(2, 8, 3, 2, 0, 1, 'Oriente-Poniente', '', '', '0.00', '0', 'a:2:{s:15:"cocina_amoblada";s:2:"Si";s:6:"bodega";s:2:"Si";}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caracteristicas_local`
--

CREATE TABLE IF NOT EXISTS `caracteristicas_local` (
  `id_caracteristicas` int(11) unsigned NOT NULL auto_increment,
  `id_propiedad` int(11) unsigned NOT NULL,
  `banos` int(2) NOT NULL default '0',
  `num_pisos` int(2) NOT NULL default '0',
  `num_privados` int(2) NOT NULL default '0',
  `num_estacionamientos` int(2) NOT NULL default '0',
  `tipo_construccion` varchar(10) NOT NULL,
  `calefaccion` varchar(15) NOT NULL,
  `superficie_construida` decimal(10,2) NOT NULL default '0.00',
  `otras_caracteristicas` text NOT NULL,
  PRIMARY KEY  (`id_caracteristicas`),
  UNIQUE KEY `id_propiedad` (`id_propiedad`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caracteristicas_oficina`
--

CREATE TABLE IF NOT EXISTS `caracteristicas_oficina` (
  `id_caracteristicas` int(11) unsigned NOT NULL auto_increment,
  `id_propiedad` int(11) unsigned NOT NULL,
  `planta_libre` varchar(2) NOT NULL,
  `banos` int(2) NOT NULL default '0',
  `num_privados` int(2) NOT NULL default '0',
  `num_estacionamientos` int(2) NOT NULL default '0',
  `tipo_construccion` varchar(10) NOT NULL,
  `calefaccion` varchar(15) NOT NULL,
  `superficie_construida` decimal(10,2) NOT NULL default '0.00',
  `gastos_comunes` decimal(12,0) NOT NULL default '0',
  `otras_caracteristicas` text NOT NULL,
  PRIMARY KEY  (`id_caracteristicas`),
  UNIQUE KEY `id_propiedad` (`id_propiedad`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Volcado de datos para la tabla `caracteristicas_oficina`
--

INSERT INTO `caracteristicas_oficina` (`id_caracteristicas`, `id_propiedad`, `planta_libre`, `banos`, `num_privados`, `num_estacionamientos`, `tipo_construccion`, `calefaccion`, `superficie_construida`, `gastos_comunes`, `otras_caracteristicas`) VALUES
(2, 13, '', 0, 0, 0, '', '', '1000.00', '0', 'a:0:{}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caracteristicas_parcela`
--

CREATE TABLE IF NOT EXISTS `caracteristicas_parcela` (
  `id_caracteristicas` int(11) unsigned NOT NULL auto_increment,
  `id_propiedad` int(11) unsigned NOT NULL,
  `hectas_superficie` decimal(10,2) NOT NULL default '0.00',
  `hectas_empastadas` decimal(10,2) NOT NULL default '0.00',
  `hectas_riego` decimal(10,2) NOT NULL default '0.00',
  `num_potreros` int(3) NOT NULL default '0',
  `num_casas_patronales` int(2) NOT NULL default '0',
  `num_casas_inquilinos` int(2) NOT NULL default '0',
  `num_bodegas` int(2) NOT NULL default '0',
  `clasificacion_suelos` varchar(20) NOT NULL,
  `aptitudes` varchar(250) NOT NULL,
  `otras_caracteristicas` text NOT NULL,
  PRIMARY KEY  (`id_caracteristicas`),
  UNIQUE KEY `id_propiedad` (`id_propiedad`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Volcado de datos para la tabla `caracteristicas_parcela`
--

INSERT INTO `caracteristicas_parcela` (`id_caracteristicas`, `id_propiedad`, `hectas_superficie`, `hectas_empastadas`, `hectas_riego`, `num_potreros`, `num_casas_patronales`, `num_casas_inquilinos`, `num_bodegas`, `clasificacion_suelos`, `aptitudes`, `otras_caracteristicas`) VALUES
(1, 17, '8114.00', '0.00', '0.00', 0, 90, 0, 0, 'Turistico', 'a:1:{i:0;s:10:"Turística";}', 'a:0:{}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caracteristicas_sitio`
--

CREATE TABLE IF NOT EXISTS `caracteristicas_sitio` (
  `id_caracteristicas` int(11) unsigned NOT NULL auto_increment,
  `id_propiedad` int(11) unsigned NOT NULL,
  `superficie_total` decimal(10,2) NOT NULL default '0.00',
  `mtrs_frente` decimal(10,2) NOT NULL default '0.00',
  `mtrs_fondo` decimal(10,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id_caracteristicas`),
  UNIQUE KEY `id_propiedad` (`id_propiedad`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE IF NOT EXISTS `clientes` (
  `id_cliente` int(11) unsigned NOT NULL auto_increment,
  `rut_cliente` varchar(12) NOT NULL,
  `nombre_cliente` varchar(30) NOT NULL,
  `apellidos_cliente` varchar(30) NOT NULL,
  `sexo_cliente` varchar(9) NOT NULL,
  `direccion` varchar(100) NOT NULL,
  `num_direccion` varchar(10) NOT NULL,
  `num_depa` varchar(10) NOT NULL,
  `comuna` varchar(20) NOT NULL,
  `ciudad` varchar(25) NOT NULL,
  `telefono` varchar(30) NOT NULL,
  `celular` varchar(30) NOT NULL,
  `oficina` varchar(30) NOT NULL,
  `fax` varchar(10) NOT NULL,
  `email` varchar(80) NOT NULL,
  `observaciones` text NOT NULL,
  `fecha_ingreso` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id_cliente`),
  UNIQUE KEY `rut_cliente` (`rut_cliente`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `rut_cliente`, `nombre_cliente`, `apellidos_cliente`, `sexo_cliente`, `direccion`, `num_direccion`, `num_depa`, `comuna`, `ciudad`, `telefono`, `celular`, `oficina`, `fax`, `email`, `observaciones`, `fecha_ingreso`) VALUES
(1, '22222222-2', 'Elton', 'John', 'Masculino', 'Colón', '996', '', 'Los Ángeles', 'Los Angeles', '', '', '', '', 'admin@admin.com', '', '2012-06-25 21:12:35'),
(4, '33333333-3', 'James', 'Howlett', 'Masculino', 'Facel', '1625', '', 'Vilcún', 'Vilcun', '', '', '', '', 'admin@admin.com', '', '2012-07-18 12:14:35'),
(5, '44444444-4', ' Ororo', 'Iqadi', 'Femenino', 'Forguu', '5489', '', 'Temuco', 'Temuco', '', '', '', '', 'admin@admin.com', 'Interesado buscar propiedad en arriendo antes de septiembre', '2012-07-18 12:23:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes_buscan`
--

CREATE TABLE IF NOT EXISTS `clientes_buscan` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `id_cliente` int(11) unsigned NOT NULL,
  `tipo_propiedad` varchar(150) default NULL,
  `operacion` varchar(10) default NULL,
  `valor_desde` decimal(12,0) default NULL,
  `valor_hasta` decimal(12,0) default NULL,
  `tipo_valor` varchar(5) default NULL,
  `sector` varchar(50) default NULL,
  `comuna` varchar(255) default NULL,
  `ciudad` varchar(25) default NULL,
  `superficie_total` decimal(10,2) default NULL,
  `superficie_construida` decimal(10,2) default NULL,
  `observaciones` text,
  `fecha_ingreso` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `id_cliente` (`id_cliente`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Volcado de datos para la tabla `clientes_buscan`
--

INSERT INTO `clientes_buscan` (`id`, `id_cliente`, `tipo_propiedad`, `operacion`, `valor_desde`, `valor_hasta`, `tipo_valor`, `sector`, `comuna`, `ciudad`, `superficie_total`, `superficie_construida`, `observaciones`, `fecha_ingreso`) VALUES
(5, 1, 'a:1:{i:0;s:4:"Casa";}', 'Venta', '30000000', '50000000', '$', 'Barrio Ingles', 'a:1:{i:0;s:12:"Los Ángeles";}', 'los angeles', '1500.32', '1560.67', NULL, '2012-09-02 04:38:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comuna`
--

CREATE TABLE IF NOT EXISTS `comuna` (
  `COMUNA_ID` int(5) NOT NULL default '0',
  `COMUNA_NOMBRE` varchar(20) default NULL,
  `COMUNA_PROVINCIA_ID` int(3) default NULL,
  PRIMARY KEY  (`COMUNA_ID`),
  KEY `COMUNA_PROVINCIA_ID` (`COMUNA_PROVINCIA_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `comuna`
--

INSERT INTO `comuna` (`COMUNA_ID`, `COMUNA_NOMBRE`, `COMUNA_PROVINCIA_ID`) VALUES
(1101, 'Iquique', 11),
(1107, 'Alto Hospicio', 11),
(1401, 'Pozo Almonte', 14),
(1402, 'Camiña', 14),
(1403, 'Colchane', 14),
(1404, 'Huara', 14),
(1405, 'Pica', 14),
(2101, 'Antofagasta', 21),
(2102, 'Mejillones', 21),
(2103, 'Sierra Gorda', 21),
(2104, 'Taltal', 21),
(2201, 'Calama', 22),
(2202, 'Ollagüe', 22),
(2203, 'San Pedro de Atacama', 22),
(2301, 'Tocopilla', 23),
(2302, 'María Elena', 23),
(3101, 'Copiapó', 31),
(3102, 'Caldera', 31),
(3103, 'Tierra Amarilla', 31),
(3201, 'Chañaral', 32),
(3202, 'Diego de Almagro', 32),
(3301, 'Vallenar', 33),
(3302, 'Alto del Carmen', 33),
(3303, 'Freirina', 33),
(3304, 'Huasco', 33),
(4101, 'La Serena', 41),
(4102, 'Coquimbo', 41),
(4103, 'Andacollo', 41),
(4104, 'La Higuera', 41),
(4105, 'Paihuano', 41),
(4106, 'Vicuña', 41),
(4201, 'Illapel', 42),
(4202, 'Canela', 42),
(4203, 'Los Vilos', 42),
(4204, 'Salamanca', 42),
(4301, 'Ovalle', 43),
(4302, 'Combarbalá', 43),
(4303, 'Monte Patria', 43),
(4304, 'Punitaqui', 43),
(4305, 'Río Hurtado', 43),
(5101, 'Valparaíso', 51),
(5102, 'Casablanca', 51),
(5103, 'Concón', 51),
(5104, 'Juan Fernández', 51),
(5105, 'Puchuncaví', 51),
(5107, 'Quintero', 51),
(5109, 'Viña del Mar', 51),
(5201, 'Isla de Pascua', 52),
(5301, 'Los Andes', 53),
(5302, 'Calle Larga', 53),
(5303, 'Rinconada', 53),
(5304, 'San Esteban', 53),
(5401, 'La Ligua', 54),
(5402, 'Cabildo', 54),
(5403, 'Papudo', 54),
(5404, 'Petorca', 54),
(5405, 'Zapallar', 54),
(5501, 'Quillota', 55),
(5502, 'La Calera', 55),
(5503, 'Hijuelas', 55),
(5504, 'La Cruz', 55),
(5506, 'Nogales', 55),
(5601, 'San Antonio', 56),
(5602, 'Algarrobo', 56),
(5603, 'Cartagena', 56),
(5604, 'El Quisco', 56),
(5605, 'El Tabo', 56),
(5606, 'Santo Domingo', 56),
(5701, 'San Felipe', 57),
(5702, 'Catemu', 57),
(5703, 'Llay Llay', 57),
(5704, 'Panquehue', 57),
(5705, 'Putaendo', 57),
(5706, 'Santa María', 57),
(5801, 'Quilpué', 58),
(5802, 'Limache', 58),
(5803, 'Olmué', 58),
(5804, 'Villa Alemana', 58),
(6101, 'Rancagua', 61),
(6102, 'Codegua', 61),
(6103, 'Coinco', 61),
(6104, 'Coltauco', 61),
(6105, 'Doñihue', 61),
(6106, 'Graneros', 61),
(6107, 'Las Cabras', 61),
(6108, 'Machalí', 61),
(6109, 'Malloa', 61),
(6110, 'Mostazal', 61),
(6111, 'Olivar', 61),
(6112, 'Peumo', 61),
(6113, 'Pichidegua', 61),
(6114, 'Quinta de Tilcoco', 61),
(6115, 'Rengo', 61),
(6116, 'Requínoa', 61),
(6117, 'San Vicente', 61),
(6201, 'Pichilemu', 62),
(6202, 'La Estrella', 62),
(6203, 'Litueche', 62),
(6204, 'Marchihue', 62),
(6205, 'Navidad', 62),
(6206, 'Paredones', 62),
(6301, 'San Fernando', 63),
(6302, 'Chépica', 63),
(6303, 'Chimbarongo', 63),
(6304, 'Lolol', 63),
(6305, 'Nancagua', 63),
(6306, 'Palmilla', 63),
(6307, 'Peralillo', 63),
(6308, 'Placilla', 63),
(6309, 'Pumanque', 63),
(6310, 'Santa Cruz', 63),
(7101, 'Talca', 71),
(7102, 'Constitución', 71),
(7103, 'Curepto', 71),
(7104, 'Empedrado', 71),
(7105, 'Maule', 71),
(7106, 'Pelarco', 71),
(7107, 'Pencahue', 71),
(7108, 'Río Claro', 71),
(7109, 'San Clemente', 71),
(7110, 'San Rafael', 71),
(7201, 'Cauquenes', 72),
(7202, 'Chanco', 72),
(7203, 'Pelluhue', 72),
(7301, 'Curicó', 73),
(7302, 'Hualañé', 73),
(7303, 'Licantén', 73),
(7304, 'Molina', 73),
(7305, 'Rauco', 73),
(7306, 'Romeral', 73),
(7307, 'Sagrada Familia', 73),
(7308, 'Teno', 73),
(7309, 'Vichuquén', 73),
(7401, 'Linares', 74),
(7402, 'Colbún', 74),
(7403, 'Longaví', 74),
(7404, 'Parral', 74),
(7405, 'Retiro', 74),
(7406, 'San Javier', 74),
(7407, 'Villa Alegre', 74),
(7408, 'Yerbas Buenas', 74),
(8101, 'Concepción', 81),
(8102, 'Coronel', 81),
(8103, 'Chiguayante', 81),
(8104, 'Florida', 81),
(8105, 'Hualqui', 81),
(8106, 'Lota', 81),
(8107, 'Penco', 81),
(8108, 'San Pedro de la Paz', 81),
(8109, 'Santa Juana', 81),
(8110, 'Talcahuano', 81),
(8111, 'Tomé', 81),
(8112, 'Hualpén', 81),
(8201, 'Lebu', 82),
(8202, 'Arauco', 82),
(8203, 'Cañete', 82),
(8204, 'Contulmo', 82),
(8205, 'Curanilahue', 82),
(8206, 'Los Álamos', 82),
(8207, 'Tirúa', 82),
(8301, 'Los Ángeles', 83),
(8302, 'Antuco', 83),
(8303, 'Cabrero', 83),
(8304, 'Laja', 83),
(8305, 'Mulchén', 83),
(8306, 'Nacimiento', 83),
(8307, 'Negrete', 83),
(8308, 'Quilaco', 83),
(8309, 'Quilleco', 83),
(8310, 'San Rosendo', 83),
(8311, 'Santa Bárbara', 83),
(8312, 'Tucapel', 83),
(8313, 'Yumbel', 83),
(8314, 'Alto Biobío', 83),
(8401, 'Chillán', 84),
(8402, 'Bulnes', 84),
(8403, 'Cobquecura', 84),
(8404, 'Coelemu', 84),
(8405, 'Coihueco', 84),
(8406, 'Chillán Viejo', 84),
(8407, 'El Carmen', 84),
(8408, 'Ninhue', 84),
(8409, 'Ñiquén', 84),
(8410, 'Pemuco', 84),
(8411, 'Pinto', 84),
(8412, 'Portezuelo', 84),
(8413, 'Quillón', 84),
(8414, 'Quirihue', 84),
(8415, 'Ránquil', 84),
(8416, 'San Carlos', 84),
(8417, 'San Fabián', 84),
(8418, 'San Ignacio', 84),
(8419, 'San Nicolás', 84),
(8420, 'Treguaco', 84),
(8421, 'Yungay', 84),
(9101, 'Temuco', 91),
(9102, 'Carahue', 91),
(9103, 'Cunco', 91),
(9104, 'Curarrehue', 91),
(9105, 'Freire', 91),
(9106, 'Galvarino', 91),
(9107, 'Gorbea', 91),
(9108, 'Lautaro', 91),
(9109, 'Loncoche', 91),
(9110, 'Melipeuco', 91),
(9111, 'Nueva Imperial', 91),
(9112, 'Padre las Casas', 91),
(9113, 'Perquenco', 91),
(9114, 'Pitrufquén', 91),
(9115, 'Pucón', 91),
(9116, 'Saavedra', 91),
(9117, 'Teodoro Schmidt', 91),
(9118, 'Toltén', 91),
(9119, 'Vilcún', 91),
(9120, 'Villarrica', 91),
(9121, 'Cholchol', 91),
(9201, 'Angol', 92),
(9202, 'Collipulli', 92),
(9203, 'Curacautín', 92),
(9204, 'Ercilla', 92),
(9205, 'Lonquimay', 92),
(9206, 'Los Sauces', 92),
(9207, 'Lumaco', 92),
(9208, 'Purén', 92),
(9209, 'Renaico', 92),
(9210, 'Traiguén', 92),
(9211, 'Victoria', 92),
(10101, 'Puerto Montt', 101),
(10102, 'Calbuco', 101),
(10103, 'Cochamó', 101),
(10104, 'Fresia', 101),
(10105, 'Frutillar', 101),
(10106, 'Los Muermos', 101),
(10107, 'Llanquihue', 101),
(10108, 'Maullín', 101),
(10109, 'Puerto Varas', 101),
(10201, 'Castro', 102),
(10202, 'Ancud', 102),
(10203, 'Chonchi', 102),
(10204, 'Curaco de Vélez', 102),
(10205, 'Dalcahue', 102),
(10206, 'Puqueldón', 102),
(10207, 'Queilén', 102),
(10208, 'Quellón', 102),
(10209, 'Quemchi', 102),
(10210, 'Quinchao', 102),
(10301, 'Osorno', 103),
(10302, 'Puerto Octay', 103),
(10303, 'Purranque', 103),
(10304, 'Puyehue', 103),
(10305, 'Río Negro', 103),
(10306, 'San Juan de la Costa', 103),
(10307, 'San Pablo', 103),
(10401, 'Chaitén', 104),
(10402, 'Futaleufú', 104),
(10403, 'Hualaihué', 104),
(10404, 'Palena', 104),
(11101, 'Coyhaique', 111),
(11102, 'Lago Verde', 111),
(11201, 'Aysén', 112),
(11202, 'Cisnes', 112),
(11203, 'Guaitecas', 112),
(11301, 'Cochrane', 113),
(11302, 'O''Higgins', 113),
(11303, 'Tortel', 113),
(11401, 'Chile Chico', 114),
(11402, 'Río Ibáñez', 114),
(12101, 'Punta Arenas', 121),
(12102, 'Laguna Blanca', 121),
(12103, 'Río Verde', 121),
(12104, 'San Gregorio', 121),
(12201, 'Cabo de Hornos', 122),
(12202, 'Antártica', 122),
(12301, 'Porvenir', 123),
(12302, 'Primavera', 123),
(12303, 'Timaukel', 123),
(12401, 'Natales', 124),
(12402, 'Torres del Paine', 124),
(13101, 'Santiago', 131),
(13102, 'Cerrillos', 131),
(13103, 'Cerro Navia', 131),
(13104, 'Conchalí', 131),
(13105, 'El Bosque', 131),
(13106, 'Estación Central', 131),
(13107, 'Huechuraba', 131),
(13108, 'Independencia', 131),
(13109, 'La Cisterna', 131),
(13110, 'La Florida', 131),
(13111, 'La Granja', 131),
(13112, 'La Pintana', 131),
(13113, 'La Reina', 131),
(13114, 'Las Condes', 131),
(13115, 'Lo Barnechea', 131),
(13116, 'Lo Espejo', 131),
(13117, 'Lo Prado', 131),
(13118, 'Macul', 131),
(13119, 'Maipú', 131),
(13120, 'Ñuñoa', 131),
(13121, 'Pedro Aguirre Cerda', 131),
(13122, 'Peñalolén', 131),
(13123, 'Providencia', 131),
(13124, 'Pudahuel', 131),
(13125, 'Quilicura', 131),
(13126, 'Quinta Normal', 131),
(13127, 'Recoleta', 131),
(13128, 'Renca', 131),
(13129, 'San Joaquín', 131),
(13130, 'San Miguel', 131),
(13131, 'San Ramón', 131),
(13132, 'Vitacura', 131),
(13201, 'Puente Alto', 132),
(13202, 'Pirque', 132),
(13203, 'San José de Maipo', 132),
(13301, 'Colina', 133),
(13302, 'Lampa', 133),
(13303, 'Tiltil', 133),
(13401, 'San Bernardo', 134),
(13402, 'Buin', 134),
(13403, 'Calera de Tango', 134),
(13404, 'Paine', 134),
(13501, 'Melipilla', 135),
(13502, 'Alhué', 135),
(13503, 'Curacaví', 135),
(13504, 'María Pinto', 135),
(13505, 'San Pedro', 135),
(13601, 'Talagante', 136),
(13602, 'El Monte', 136),
(13603, 'Isla de Maipo', 136),
(13604, 'Padre Hurtado', 136),
(13605, 'Peñaflor', 136),
(14101, 'Valdivia', 141),
(14102, 'Corral', 141),
(14103, 'Lanco', 141),
(14104, 'Los Lagos', 141),
(14105, 'Máfil', 141),
(14106, 'Mariquina', 141),
(14107, 'Paillaco', 141),
(14108, 'Panguipulli', 141),
(14201, 'La Unión', 142),
(14202, 'Futrono', 142),
(14203, 'Lago Ranco', 142),
(14204, 'Río Bueno', 142),
(15101, 'Arica', 151),
(15102, 'Camarones', 151),
(15201, 'Putre', 152),
(15202, 'General Lagos', 152);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `propiedades`
--

CREATE TABLE IF NOT EXISTS `propiedades` (
  `id_propiedad` int(11) unsigned NOT NULL auto_increment,
  `cod_propiedad` varchar(11) NOT NULL,
  `id_propietario` int(11) unsigned NOT NULL,
  `nombre_propietario` varchar(115) NOT NULL,
  `tipo_propiedad` varchar(15) NOT NULL,
  `operacion` varchar(10) NOT NULL,
  `valor` decimal(12,0) NOT NULL default '0',
  `tipo_valor` varchar(5) NOT NULL,
  `direccion` varchar(100) NOT NULL,
  `num_direccion` varchar(10) NOT NULL,
  `num_depa` varchar(10) NOT NULL,
  `sector` varchar(50) NOT NULL,
  `comuna` varchar(20) NOT NULL,
  `ciudad` varchar(25) NOT NULL,
  `captador_id` int(11) unsigned NOT NULL default '0',
  `comision_captador` int(3) NOT NULL default '0',
  `exclusividad` varchar(2) NOT NULL,
  `observaciones` text NOT NULL,
  `lat_googlemap` float(10,6) default NULL,
  `lng_googlemap` float(10,6) default NULL,
  `activada` int(1) NOT NULL default '1',
  `publicada` int(1) NOT NULL default '1',
  `fecha_ingreso` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id_propiedad`),
  UNIQUE KEY `cod_propiedad` (`cod_propiedad`),
  KEY `id_propietario` (`id_propietario`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;

--
-- Volcado de datos para la tabla `propiedades`
--

INSERT INTO `propiedades` (`id_propiedad`, `cod_propiedad`, `id_propietario`, `nombre_propietario`, `tipo_propiedad`, `operacion`, `valor`, `tipo_valor`, `direccion`, `num_direccion`, `num_depa`, `sector`, `comuna`, `ciudad`, `captador_id`, `comision_captador`, `exclusividad`, `observaciones`, `lat_googlemap`, `lng_googlemap`, `activada`, `publicada`, `fecha_ingreso`) VALUES
(4, 'DV-4', 1, '(44.444.444-4)  Ororo Iqadi', 'Departamento', 'Venta', '3000', 'U.F.', 'cualquiera', '', '', 'Av. Alemania', 'Temuco', 'Temuco', 0, 0, '', 'Departamento ubicado en Sector Avenida Alemania, excelente ubicación, amplio, 2 dormitorios, mas servicio, 3 baños, cocina amplia, amoblada, terraza.', 0.000000, 0.000000, 1, 1, '2012-06-27 20:05:21'),
(5, 'RA-5', 5, '(44.444.444-4)  Ororo Iqadi', 'Casa', 'Arriendo', '30', 'U.F.', 'Centro', '', '', 'Avenida Alemania', 'Temuco', 'Temuco', 0, 0, 'No', 'Propiedad uso Comercial - Oficinas, sector Avenida Alemania, frente Portal Temuco.   Aproximadamente cuatro oficinas, con tres baños y estacionamiento para vehiculos.', 0.000000, 0.000000, 1, 1, '2012-07-04 23:16:05'),
(6, 'RA-6', 5, '(44.444.444-4)  Ororo Iqadi', 'Casa', 'Arriendo', '650000', '$', 'claro solar', '', '', 'Avenida Alemania', 'Temuco', 'Temuco', 0, 0, 'No', 'Propiedad ubicada en sector Avenida Alemania, calle Recreo para uso comercial.-', 0.000000, 0.000000, 1, 1, '2012-07-04 23:19:21'),
(7, 'RA-7', 1, '(22.222.222-2) Elton John', 'Casa', 'Arriendo', '350000', '$', 'Antonio Varas', '979', '', 'Barrio Ingles', 'Temuco', 'Temuco', 0, 0, 'No', 'Propiedad ubicada en sector Barrio Inglés.  Cuenta con Living-Comedor, cocina amoblada, tres domitorios (uno en suite), dos baños, escritorio.', 0.000000, 0.000000, 1, 1, '2012-07-04 23:22:10'),
(8, 'DV-8', 1, '(22.222.222-2) Elton John', 'Departamento', 'Venta', '58000000', '$', 'Edificio Simon', '800', '', 'Lomas de Mirasur', 'Temuco', 'Temuco', 0, 0, '', 'Departamento ubicado en Edificio Simon 8, Temuco\r\n\r\nDescripción:  living-comedor, cocina amoblada, tres dormitorios, dos baños, un estacionamiento y bodega.', 0.000000, 0.000000, 1, 1, '2012-07-04 23:30:34'),
(13, 'OV-13', 1, '(22.222.222-2) Elton John', 'Oficina', 'Venta', '22', 'U.F.', 'Aldunate', '620', '8', 'Centro', 'Temuco', 'Temuco', 0, 0, '', '22 UF X MT2\r\n\r\n1 RESTAURANTE     120 Mt2\r\nOFICINAS                 880 MT2\r\n\r\nTAMBIEN ESTA DISPONIBLE PARA ARRIENDO EN 0,22 UF X MT2', 0.000000, 0.000000, 1, 1, '2012-07-05 00:20:42'),
(16, 'CV-16', 1, '(22.222.222-2) Elton John', 'Campo', 'Venta', '8400000000', '$', 'cualquiera', '', '', 'Sector Palena, Sector Lago Yelcho,', 'Chaitén', 'Chaiten', 0, 0, 'No', 'CLASIFICACIÓN :\r\n\r\nIV-I-50,00 V = 4.000,00\r\nVI -I = 60.000 VII\r\nVII - I = 59.950\r\n\r\nTOTAL      : 240.000 HÁ.\r\ns/escrituras : 250.000 HÁ.', 0.000000, 0.000000, 1, 1, '2012-07-05 00:32:58'),
(17, 'PA-17', 1, '(44.444.444-4)  Ororo Iqadi', 'Parcela', 'Arriendo', '130000000', '$', 'A 400 mt. del Camino', '', '', 'Nipu, entre Lican a Coñaipe, Klmt. 8200', 'Villarrica', 'Villarrica', 0, 0, '', 'PARCELA DE AGRADO                     \r\n8.114 mt2\r\n\r\nAGUA Y LUZ DE SAESA\r\nPLAYA A 70 MTS.', 0.000000, 0.000000, 1, 1, '2012-07-05 00:55:39'),
(18, 'RA-18', 4, '(33.333.333-3) James Howlett', 'Casa', 'Arriendo', '2900', 'U.F.', 'Canada', '1625', '', 'Camino Labranza', 'Vilcún', 'Vilcun', 0, 0, 'No', '', 0.000000, 0.000000, 0, 0, '2012-07-18 12:17:15'),
(19, 'RV-19', 4, '(33.333.333-3) James Howlett', 'Casa', 'Venta', '150000000', '$', 'colón', '987', '', 'Camino Labranza', 'Los Ángeles', 'Los Angeles', 0, 0, 'No', '', -37.469536, -72.373741, 0, 1, '2012-08-23 23:58:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `propiedades_fotos`
--

CREATE TABLE IF NOT EXISTS `propiedades_fotos` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `id_propiedad` int(11) unsigned NOT NULL,
  `imagen_real` varchar(255) NOT NULL,
  `imagen_thumb` varchar(255) NOT NULL,
  `nombre_imagen` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `id_propiedad` (`id_propiedad`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

--
-- Volcado de datos para la tabla `propiedades_fotos`
--

INSERT INTO `propiedades_fotos` (`id`, `id_propiedad`, `imagen_real`, `imagen_thumb`, `nombre_imagen`) VALUES
(16, 18, 'uploads/casa/thumbs/casa-ID18-living.jpg', 'uploads/casa/thumbs/casa-ID18-living-240x150.jpg', 'Living'),
(17, 18, 'uploads/casa/thumbs/casa-ID18-jardin.jpg', 'uploads/casa/thumbs/casa-ID18-jardin-240x150.jpg', 'Jardín'),
(18, 18, 'uploads/casa/thumbs/casa-ID18-comedor.jpg', 'uploads/casa/thumbs/casa-ID18-comedor-240x150.jpg', 'Comedor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `provincia`
--

CREATE TABLE IF NOT EXISTS `provincia` (
  `PROVINCIA_ID` int(3) NOT NULL default '0',
  `PROVINCIA_NOMBRE` varchar(23) default NULL,
  `PROVINCIA_REGION_ID` int(2) default NULL,
  PRIMARY KEY  (`PROVINCIA_ID`),
  KEY `PROVINCIA_REGION_ID` (`PROVINCIA_REGION_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `provincia`
--

INSERT INTO `provincia` (`PROVINCIA_ID`, `PROVINCIA_NOMBRE`, `PROVINCIA_REGION_ID`) VALUES
(11, 'Iquique', 1),
(14, 'Tamarugal', 1),
(21, 'Antofagasta', 2),
(22, 'El Loa', 2),
(23, 'Tocopilla', 2),
(31, 'Copiapó', 3),
(32, 'Chañaral', 3),
(33, 'Huasco', 3),
(41, 'Elqui', 4),
(42, 'Choapa', 4),
(43, 'Limarí', 4),
(51, 'Valparaíso', 5),
(52, 'Isla de Pascua', 5),
(53, 'Los Andes', 5),
(54, 'Petorca', 5),
(55, 'Quillota', 5),
(56, 'San Antonio', 5),
(57, 'San Felipe de Aconcagua', 5),
(58, 'Marga Marga', 5),
(61, 'Cachapoal', 6),
(62, 'Cardenal Caro', 6),
(63, 'Colchagua', 6),
(71, 'Talca', 7),
(72, 'Cauquenes', 7),
(73, 'Curicó', 7),
(74, 'Linares', 7),
(81, 'Concepción', 8),
(82, 'Arauco', 8),
(83, 'Biobío', 8),
(84, 'Ñuble', 8),
(91, 'Cautín', 9),
(92, 'Malleco', 9),
(101, 'Llanquihue', 10),
(102, 'Chiloé', 10),
(103, 'Osorno', 10),
(104, 'Palena', 10),
(111, 'Coihaique', 11),
(112, 'Aisén', 11),
(113, 'Capitán Prat', 11),
(114, 'General Carrera', 11),
(121, 'Magallanes', 12),
(122, 'Antártica Chilena', 12),
(123, 'Tierra del Fuego', 12),
(124, 'Última Esperanza', 12),
(131, 'Santiago', 13),
(132, 'Cordillera', 13),
(133, 'Chacabuco', 13),
(134, 'Maipo', 13),
(135, 'Melipilla', 13),
(136, 'Talagante', 13),
(141, 'Valdivia', 14),
(142, 'Ranco', 14),
(151, 'Arica', 15),
(152, 'Parinacota', 15);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `region`
--

CREATE TABLE IF NOT EXISTS `region` (
  `REGION_ID` int(2) NOT NULL default '0',
  `REGION_NOMBRE` varchar(50) default NULL,
  PRIMARY KEY  (`REGION_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `region`
--

INSERT INTO `region` (`REGION_ID`, `REGION_NOMBRE`) VALUES
(1, 'Tarapacá'),
(2, 'Antofagasta'),
(3, 'Atacama'),
(4, 'Coquimbo'),
(5, 'Valparaíso'),
(6, 'Región del Libertador Gral. Bernardo O’Higgins'),
(7, 'Región del Maule'),
(8, 'Región del Biobío'),
(9, 'Región de la Araucanía'),
(10, 'Región de Los Lagos'),
(11, 'Región Aisén del Gral. Carlos Ibáñez del Campo'),
(12, 'Región de Magallanes y de la Antártica Chilena'),
(13, 'Región Metropolitana de Santiago'),
(14, 'Región de Los Ríos'),
(15, 'Arica y Parinacota');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sectores`
--

CREATE TABLE IF NOT EXISTS `sectores` (
  `id` int(4) unsigned NOT NULL auto_increment,
  `sector_nombre` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Volcado de datos para la tabla `sectores`
--

INSERT INTO `sectores` (`id`, `sector_nombre`) VALUES
(1, 'Centro'),
(2, 'Pueblo Nuevo'),
(3, 'Amanecer'),
(4, 'Camino Labranza'),
(5, 'Labranza'),
(6, 'Av. Alemania'),
(7, 'San Martín'),
(8, 'Barrio Inglés'),
(9, 'Cataluña'),
(10, 'Lomas de Mirasur');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE IF NOT EXISTS `usuarios` (
  `id_usuario` int(11) unsigned NOT NULL auto_increment,
  `rut_usuario` varchar(12) NOT NULL,
  `password` varchar(128) NOT NULL,
  `nombre` varchar(35) NOT NULL,
  `apellido` varchar(35) NOT NULL,
  `sexo_usuario` varchar(9) NOT NULL,
  `telefono` varchar(30) NOT NULL,
  `celular` varchar(30) NOT NULL,
  `email` varchar(80) NOT NULL,
  `tipo_usuario` int(1) NOT NULL default '0',
  `privilegios_opcionales` varchar(255) default NULL,
  `ultimo_login` datetime default NULL,
  `fecha_ingreso` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id_usuario`),
  UNIQUE KEY `rut_usuario` (`rut_usuario`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `rut_usuario`, `password`, `nombre`, `apellido`, `sexo_usuario`, `telefono`, `celular`, `email`, `tipo_usuario`, `privilegios_opcionales`, `ultimo_login`, `fecha_ingreso`) VALUES
(1, '11111111-1', '21232f297a57a5a743894a0e4a801fc3', 'Administrador', 'del Sistema', 'Masculino', '', '', 'admin@admin.com', 1, NULL, '2013-09-10 02:10:45', '2012-07-02 20:29:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_historial`
--

CREATE TABLE IF NOT EXISTS `usuarios_historial` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `id_usuario` int(11) unsigned NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
