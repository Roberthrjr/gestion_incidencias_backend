-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: gestion_incidencias_mp
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `areas`
--

DROP TABLE IF EXISTS `areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `areas` (
  `id_area` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion_area` varchar(100) NOT NULL,
  `id_sede` int(11) NOT NULL,
  `fecha_creacion_area` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion_area` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_area`),
  KEY `id_sede` (`id_sede`),
  CONSTRAINT `areas_ibfk_1` FOREIGN KEY (`id_sede`) REFERENCES `sedes` (`id_sede`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blacklist_tokens`
--

DROP TABLE IF EXISTS `blacklist_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blacklist_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `token` text NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_expiracion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cargos`
--

DROP TABLE IF EXISTS `cargos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cargos` (
  `id_cargo` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion_cargo` varchar(100) NOT NULL,
  `fecha_creacion_cargo` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion_cargo` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_cargo`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion_categoria` varchar(100) NOT NULL,
  `fecha_creacion_categoria` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion_categoria` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `componentes`
--

DROP TABLE IF EXISTS `componentes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `componentes` (
  `id_componente` int(11) NOT NULL AUTO_INCREMENT,
  `numero_serie_componente` varchar(50) DEFAULT NULL,
  `descripcion_componente` varchar(100) NOT NULL,
  `marca_componente` varchar(50) DEFAULT NULL,
  `modelo_componente` varchar(50) DEFAULT NULL,
  `id_equipo` int(11) NOT NULL,
  `foto_componente` text DEFAULT NULL,
  `estado_componente` varchar(20) DEFAULT 'activo',
  `fecha_creacion_componente` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion_componente` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_componente`),
  KEY `id_equipo` (`id_equipo`),
  CONSTRAINT `componentes_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `configuracion_red`
--

DROP TABLE IF EXISTS `configuracion_red`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuracion_red` (
  `id_red` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_conexion` varchar(50) NOT NULL,
  `direccion_ip` varchar(50) DEFAULT NULL,
  `grupo_trabajo` varchar(50) DEFAULT NULL,
  `id_equipo` int(11) NOT NULL,
  `fecha_creacion_red` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion_red` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_red`),
  KEY `id_equipo` (`id_equipo`),
  CONSTRAINT `configuracion_red_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `equipos`
--

DROP TABLE IF EXISTS `equipos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `equipos` (
  `id_equipo` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_patrimonial_equipo` varchar(100) DEFAULT NULL,
  `nombre_equipo` varchar(100) NOT NULL,
  `marca_equipo` varchar(50) DEFAULT NULL,
  `modelo_equipo` varchar(50) DEFAULT NULL,
  `foto_equipo` text DEFAULT NULL,
  `id_area` int(11) NOT NULL,
  `id_subcategoria` int(11) DEFAULT NULL,
  `estado_equipo` varchar(20) DEFAULT 'activo',
  `fecha_creacion_equipo` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion_equipo` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_equipo`),
  KEY `id_area` (`id_area`),
  KEY `id_subcategoria` (`id_subcategoria`),
  CONSTRAINT `equipos_ibfk_1` FOREIGN KEY (`id_area`) REFERENCES `areas` (`id_area`) ON DELETE CASCADE,
  CONSTRAINT `equipos_ibfk_2` FOREIGN KEY (`id_subcategoria`) REFERENCES `subcategorias` (`id_subcategoria`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `estados_incidencias`
--

DROP TABLE IF EXISTS `estados_incidencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estados_incidencias` (
  `id_estado` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion_estado` varchar(50) NOT NULL,
  `fecha_creacion_estado` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion_estado` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_estado`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `historial_incidencias`
--

DROP TABLE IF EXISTS `historial_incidencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `historial_incidencias` (
  `id_historial` int(11) NOT NULL AUTO_INCREMENT,
  `id_incidencia` int(11) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `estado_historial` varchar(50) DEFAULT NULL,
  `fecha_cambio_historial` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_historial`),
  KEY `id_incidencia` (`id_incidencia`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `historial_incidencias_ibfk_1` FOREIGN KEY (`id_incidencia`) REFERENCES `incidencias` (`id_incidencia`) ON DELETE CASCADE,
  CONSTRAINT `historial_incidencias_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `incidencias`
--

DROP TABLE IF EXISTS `incidencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incidencias` (
  `id_incidencia` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_incidencia` varchar(50) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `id_tipo_incidencia` int(11) DEFAULT NULL,
  `descripcion_incidencia` text NOT NULL,
  `id_prioridad` int(11) DEFAULT NULL,
  `id_estado` int(11) DEFAULT NULL,
  `fecha_creacion_incidencia` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion_incidencia` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_incidencia`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_equipo` (`id_equipo`),
  KEY `id_prioridad` (`id_prioridad`),
  KEY `id_estado` (`id_estado`),
  KEY `fk_tipo_incidencia` (`id_tipo_incidencia`),
  CONSTRAINT `fk_tipo_incidencia` FOREIGN KEY (`id_tipo_incidencia`) REFERENCES `tipos_incidencias` (`id_tipo_incidencia`) ON DELETE SET NULL,
  CONSTRAINT `incidencias_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `incidencias_ibfk_2` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE CASCADE,
  CONSTRAINT `incidencias_ibfk_4` FOREIGN KEY (`id_prioridad`) REFERENCES `prioridades` (`id_prioridad`) ON DELETE SET NULL,
  CONSTRAINT `incidencias_ibfk_5` FOREIGN KEY (`id_estado`) REFERENCES `estados_incidencias` (`id_estado`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `perifericos`
--

DROP TABLE IF EXISTS `perifericos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `perifericos` (
  `id_periferico` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_patrimonial_periferico` varchar(100) DEFAULT NULL,
  `numero_serie_periferico` varchar(50) DEFAULT NULL,
  `descripcion_periferico` varchar(100) NOT NULL,
  `marca_periferico` varchar(50) DEFAULT NULL,
  `modelo_periferico` varchar(50) DEFAULT NULL,
  `id_equipo` int(11) NOT NULL,
  `foto_periferico` text DEFAULT NULL,
  `estado_periferico` varchar(20) DEFAULT 'activo',
  `fecha_creacion_periferico` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion_periferico` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_periferico`),
  KEY `id_equipo` (`id_equipo`),
  CONSTRAINT `perifericos_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prioridades`
--

DROP TABLE IF EXISTS `prioridades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prioridades` (
  `id_prioridad` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion_prioridad` varchar(50) NOT NULL,
  `fecha_creacion_prioridad` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion_prioridad` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_prioridad`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `programas`
--

DROP TABLE IF EXISTS `programas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `programas` (
  `id_programa` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion_programa` varchar(100) NOT NULL,
  `version_programa` varchar(50) DEFAULT NULL,
  `licencia_programa` varchar(100) DEFAULT NULL,
  `id_equipo` int(11) NOT NULL,
  `foto_programa` text DEFAULT NULL,
  `estado_programa` varchar(20) DEFAULT 'activo',
  `fecha_creacion_programa` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion_programa` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_programa`),
  KEY `id_equipo` (`id_equipo`),
  CONSTRAINT `programas_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion_rol` varchar(50) NOT NULL,
  `fecha_creacion_rol` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion_rol` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sedes`
--

DROP TABLE IF EXISTS `sedes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sedes` (
  `id_sede` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion_sede` varchar(100) NOT NULL,
  `direccion_sede` varchar(255) DEFAULT NULL,
  `fecha_creacion_sede` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion_sede` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_sede`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subcategorias`
--

DROP TABLE IF EXISTS `subcategorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subcategorias` (
  `id_subcategoria` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion_subcategoria` varchar(100) NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `fecha_creacion_subcategoria` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion_subcategoria` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_subcategoria`),
  KEY `id_categoria` (`id_categoria`),
  CONSTRAINT `subcategorias_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tipos_incidencias`
--

DROP TABLE IF EXISTS `tipos_incidencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipos_incidencias` (
  `id_tipo_incidencia` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion_tipo_incidencia` varchar(100) NOT NULL,
  `fecha_creacion_tipo_incidencia` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion_tipo_incidencia` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_tipo_incidencia`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `tipo_documento` varchar(20) DEFAULT NULL,
  `numero_documento` varchar(20) DEFAULT NULL,
  `clave` varchar(255) NOT NULL,
  `estado` varchar(20) DEFAULT 'activo',
  `foto` text DEFAULT NULL,
  `id_area` int(11) NOT NULL,
  `id_cargo` int(11) DEFAULT NULL,
  `id_rol` int(11) DEFAULT NULL,
  `intentos_fallidos` int(11) DEFAULT 0,
  `refresh_token` varchar(255) DEFAULT NULL,
  `token_recuperacion` varchar(255) DEFAULT NULL,
  `fecha_expiracion_token` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `numero_documento` (`numero_documento`),
  KEY `id_cargo` (`id_cargo`),
  KEY `id_rol` (`id_rol`),
  KEY `fk_usuarios_areas` (`id_area`),
  CONSTRAINT `fk_usuarios_areas` FOREIGN KEY (`id_area`) REFERENCES `areas` (`id_area`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_cargo`) REFERENCES `cargos` (`id_cargo`) ON DELETE SET NULL,
  CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `valoraciones`
--

DROP TABLE IF EXISTS `valoraciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `valoraciones` (
  `id_valoracion` int(11) NOT NULL AUTO_INCREMENT,
  `id_incidencia` int(11) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `calificacion` tinyint(4) DEFAULT NULL CHECK (`calificacion` >= 1 and `calificacion` <= 5),
  `comentario_valoracion` text DEFAULT NULL,
  `fecha_creacion_valoracion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_valoracion`),
  KEY `id_incidencia` (`id_incidencia`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `valoraciones_ibfk_1` FOREIGN KEY (`id_incidencia`) REFERENCES `incidencias` (`id_incidencia`) ON DELETE CASCADE,
  CONSTRAINT `valoraciones_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-11-22 10:04:23
