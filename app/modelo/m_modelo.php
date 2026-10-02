<?php
date_default_timezone_set('America/Mexico_City');
include_once __DIR__ . "/config/conexion.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if (file_exists(__DIR__ . '/PHPMailer/vendor/autoload.php')) {
    require_once __DIR__ . '/PHPMailer/vendor/autoload.php';
}

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
            `Dispositivo_UID` varchar(40) DEFAULT NULL,
            `Dispositivo_Cobro` varchar(60) DEFAULT NULL,
            `Dispositivo_Cobro_UID` varchar(40) DEFAULT NULL,
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
            UNIQUE KEY `uk_ticket` (`Dispositivo_UID`, `Folio_Tablet`, `Entrada`),
            KEY `Folio_Tablet` (`Folio_Tablet`),
            KEY `FK_Detalle_Caja` (`FK_Detalle_Caja`),
            KEY `Cobro_UID` (`Dispositivo_Cobro_UID`, `Salida`)
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

    public function _email($destino, $asunto, $mensaje)
    {
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: ParkingPro <contacto@invitia.mx>\r\n";
            return @mail($destino, $asunto, $mensaje, $headers);
        }

        $mail = new PHPMailer(true);
        try {
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
                    'verify_peer' => false,
                    'verify_peer_name' => false
                )
            );

            $mail->isSMTP();
            $mail->Host = '216.246.113.191';
            $mail->SMTPAuth = true;
            $mail->Username = 'contacto@invitia.mx';
            $mail->Password = 'Invitia_2026';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom('contacto@invitia.mx', 'ParkingPro Soporte');
            $mail->addAddress($destino);

            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body = $mensaje;
            $mail->AltBody = strip_tags($mensaje);

            return $mail->send();
        } catch (\Exception $e) {
            error_log("Error enviando email: " . $mail->ErrorInfo);
            return false;
        }
    }

    public function _plantillaCorreo($titulo, $subtitulo, $contenidoHtml, $botonTexto = '', $botonUrl = '', $pieNota = '')
    {
        $botonHtml = '';
        if (!empty($botonTexto) && !empty($botonUrl)) {
            $botonHtml = '
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 28px auto 10px auto;">
                <tr>
                    <td align="center" style="border-radius: 50px; background: #0284C7; text-align: center;">
                        <a href="' . $botonUrl . '" target="_blank" style="background: #0284C7; border: 1px solid #0284C7; font-family: \'Segoe UI\', Arial, sans-serif; font-size: 15px; font-weight: 600; color: #FFFFFF; text-decoration: none; padding: 13px 32px; border-radius: 50px; display: inline-block; letter-spacing: 0.5px;">
                            ' . $botonTexto . '
                        </a>
                    </td>
                </tr>
            </table>';
        }

        $notaExtra = '';
        if (!empty($pieNota)) {
            $notaExtra = '
            <p style="margin: 22px 0 0 0; font-size: 12px; color: #888888; text-align: center; line-height: 1.5;">
                ' . $pieNota . '
            </p>';
        }

        $subtituloHtml = '';
        if (!empty($subtitulo)) {
            $subtituloHtml = '<p style="margin: 6px 0 0 0; font-size: 13.5px; color: #0284C7; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;">' . $subtitulo . '</p>';
        }

        return '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $titulo . '</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F8FAFC; font-family: \'Segoe UI\', Arial, Helvetica, sans-serif;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; padding: 35px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #FFFFFF; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); overflow: hidden; border: 1px solid #E2E8F0;">
                    <tr>
                        <td align="center" style="background-color: #0F172A; padding: 30px 25px 25px 25px; border-bottom: 3px solid #0284C7;">
                            <span style="font-family: \'Segoe UI\', Arial, sans-serif; font-size: 24px; color: #FFFFFF; font-weight: bold; letter-spacing: 1px; display: block;">Parking<span style="color: #38BDF8;">Pro</span></span>
                            <span style="font-size: 11px; color: #94A3B8; letter-spacing: 1.5px; text-transform: uppercase; display: block; margin-top: 4px;">Punto de Venta & Control de Estacionamiento</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px 35px 15px 35px; text-align: center; background-color: #FFFFFF;">
                            <h1 style="margin: 0; font-size: 22px; font-weight: 700; color: #0F172A; line-height: 1.3;">' . $titulo . '</h1>
                            ' . $subtituloHtml . '
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 35px 25px 35px; font-size: 14.5px; line-height: 1.6; color: #334155;">
                            ' . $contenidoHtml . '
                            ' . $botonHtml . '
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #F8FAFC; padding: 20px 35px; border-top: 1px solid #E2E8F0; font-size: 12px; color: #64748B; text-align: center;">
                            ' . $notaExtra . '
                            <p style="margin: 8px 0 0 0; font-size: 11px; color: #94A3B8;">&copy; ' . date('Y') . ' ParkingPro. Todos los derechos reservados.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }
}
?>
