<?php
date_default_timezone_set('America/Mexico_City');
include_once __DIR__ . "/config/conexion.php";

class m_modelo extends conexion
{
    public $link;
    public $numerofilas;
    public $error;

    public function __construct($database = null)
    {
        $this->link = parent::__construct($database);
    }

    public function _insertar($query)
    {
        $result = $this->link->query($query);
        $this->numerofilas = $this->link->affected_rows;
        if (!$result) {
            $this->error = 'si';
            return 'si';
        } else {
            $this->error = 'no';
            return 'no';
        }
    }

    public function _consultar($query)
    {
        $result = $this->link->query($query);
        $resultado = array();

        if (!$result) {
            $this->error = 'si';
            $this->numerofilas = 0;
            return 'si';
        }

        $this->error = 'no';
        $this->numerofilas = $result->num_rows;
        while ($fila = $result->fetch_assoc()) {
            $resultado[] = $fila;
        }

        return $resultado;
    }

    public function escape($str)
    {
        return $this->link->real_escape_string(trim($str ?? ''));
    }

    public function movimiento($descripcion, $idUsuario = '')
    {
        // Safe logging placeholder
        return true;
    }

    /**
     * Aprovisiona una nueva base de datos y tablas para un estacionamiento (Tenant)
     */
    public function _crear($id, $nombreNegocio, $nombreContacto, $correo, $contrasena)
    {
        set_time_limit(300);

        $dbName = "parking_" . intval($id);
        $this->link->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $this->link->query("USE `$dbName`;");

        // 1. Tabla cajas
        $this->link->query("CREATE TABLE IF NOT EXISTS `cajas` (
            `ID_Caja` int(11) NOT NULL AUTO_INCREMENT,
            `Nombre` varchar(60) NOT NULL,
            `Estatus` varchar(30) NOT NULL DEFAULT 'Cerrada',
            PRIMARY KEY (`ID_Caja`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $this->link->query("INSERT IGNORE INTO `cajas` (`ID_Caja`, `Nombre`, `Estatus`) VALUES (1, 'Caja Principal', 'Cerrada');");

        // 2. Tabla configuracion (Tarifas iniciales y datos del negocio)
        $this->link->query("CREATE TABLE IF NOT EXISTS `configuracion` (
            `ID_Configuracion` int(11) NOT NULL AUTO_INCREMENT,
            `Media` double NOT NULL DEFAULT 10,
            `Precio` double NOT NULL DEFAULT 20,
            `Extra` double NOT NULL DEFAULT 10,
            `Nombre` varchar(150) DEFAULT '',
            `Domicilio` varchar(255) DEFAULT '',
            `Telefono` varchar(50) DEFAULT '',
            `Leyenda` text DEFAULT NULL,
            PRIMARY KEY (`ID_Configuracion`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $nombreNegocioEsc = $this->escape($nombreNegocio);
        $this->link->query("INSERT IGNORE INTO `configuracion` (`ID_Configuracion`, `Media`, `Precio`, `Extra`, `Nombre`, `Domicilio`, `Telefono`, `Leyenda`) 
            VALUES (1, 10, 20, 10, '$nombreNegocioEsc', '', '', '* No nos hacemos responsables de robo parcial o total, ni daños ocasionados por terceros *');");

        // 3. Tabla detalles_caja (Cortes de turno)
        $this->link->query("CREATE TABLE IF NOT EXISTS `detalles_caja` (
            `ID_Detalle_Caja` int(11) NOT NULL AUTO_INCREMENT,
            `Dispositivo` varchar(60) NOT NULL DEFAULT 'Tablet 1',
            `FK_Caja` int(11) NOT NULL,
            `Fecha_Apertura` datetime NOT NULL,
            `Monto_Apertura` double NOT NULL,
            `Fecha_Cierre` datetime DEFAULT NULL,
            `Monto_Cierre` double NOT NULL DEFAULT 0,
            `Ingresos` double NOT NULL DEFAULT 0,
            `Balance` double NOT NULL DEFAULT 0,
            `Diferencia` double NOT NULL DEFAULT 0,
            PRIMARY KEY (`ID_Detalle_Caja`),
            KEY `FK_Caja` (`FK_Caja`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // 4. Tabla registros (Entradas y salidas de vehículos)
        $this->link->query("CREATE TABLE IF NOT EXISTS `registros` (
            `ID_Registro` int(11) NOT NULL AUTO_INCREMENT,
            `Dispositivo` varchar(60) NOT NULL DEFAULT 'Tablet 1',
            `Folio_Tablet` varchar(30) DEFAULT NULL,
            `FK_Detalle_Caja` int(11) NOT NULL DEFAULT 1,
            `Media` double NOT NULL DEFAULT 10,
            `Precio` double NOT NULL DEFAULT 20,
            `Extra` double NOT NULL DEFAULT 10,
            `Entrada` datetime NOT NULL,
            `Salida` datetime DEFAULT NULL,
            `Tipo` varchar(60) NOT NULL DEFAULT 'sedan',
            `Descripcion` tinytext NOT NULL,
            `Placas` varchar(60) NOT NULL DEFAULT 'S/P',
            `Estatus` varchar(30) NOT NULL DEFAULT 'Pendiente',
            `Horas` varchar(30) NOT NULL DEFAULT '0:00',
            `Total` double NOT NULL DEFAULT 0,
            `Fecha_Registro` datetime NOT NULL,
            PRIMARY KEY (`ID_Registro`),
            KEY `Folio_Tablet` (`Folio_Tablet`),
            KEY `FK_Detalle_Caja` (`FK_Detalle_Caja`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // 5. Tabla usuarios locales del negocio
        $this->link->query("CREATE TABLE IF NOT EXISTS `usuarios` (
            `ID_Usuario` int(11) NOT NULL AUTO_INCREMENT,
            `Nombre` varchar(100) NOT NULL,
            `Correo` varchar(100) NOT NULL,
            `Contrasena` varchar(255) NOT NULL,
            `Estatus` varchar(30) NOT NULL DEFAULT 'Activo',
            `Fecha_Registro` datetime NOT NULL,
            PRIMARY KEY (`ID_Usuario`),
            UNIQUE KEY `Correo` (`Correo`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $nombreEsc = $this->escape($nombreContacto);
        $correoEsc = $this->escape($correo);
        $passEsc = $this->escape($contrasena);
        $this->link->query("INSERT IGNORE INTO `usuarios` (`Nombre`, `Correo`, `Contrasena`, `Estatus`, `Fecha_Registro`) 
            VALUES ('$nombreEsc', '$correoEsc', '$passEsc', 'Activo', NOW());");

        // Regresar a parking_subs
        $this->link->query("USE `parking_subs`;");
        return true;
    }
}
?>
