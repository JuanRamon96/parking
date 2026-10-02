<?php
class login
{
    public function _consultar()
    {
        $omodelo = new m_modelo('parking_subs');
        extract($_POST);
        $fecha = date('Y-m-d H:i:s');

        $correo = $omodelo->link->real_escape_string(trim($correo ?? ''));
        $contrasena = trim($contrasena ?? '');

        if ($correo == '' || $contrasena == '') {
            echo "0";
            return;
        }

        $query = "SELECT 
            ID_Suscripcion,
            Nombre_Negocio,
            Nombre_Contacto,
            Correo,
            Telefono,
            Contrasena,
            Plan,
            Fecha_Alta,
            Fecha_Vence,
            Ilimitado,
            Estatus,
            Token_Enlace,
            Codigo_Corto,
            BD
        FROM suscripciones WHERE Correo = '$correo' LIMIT 1";

        $row = $omodelo->_consultar($query);
        $numerofilas = $omodelo->numerofilas;

        if ($row == "si") {
            echo "Error 1: " . mysqli_error($omodelo->link);
        } else {
            if ($numerofilas > 0) {
                if (password_verify($contrasena, $row[0]['Contrasena'])) {
                    unset($row[0]['Contrasena']);
                    $_SESSION['user_parking_sub'] = $row[0];
                    $_SESSION['user_parking_bd'] = $row[0]['BD'];
                    $_SESSION['user_estacionamiento'] = [
                        'ID_Usuario' => $row[0]['ID_Suscripcion'],
                        'Nombre' => $row[0]['Nombre_Contacto'],
                        'Negocio' => $row[0]['Nombre_Negocio'],
                        'Correo' => $row[0]['Correo'],
                        'Estatus' => $row[0]['Estatus']
                    ];
                    echo "Correcto";
                } else {
                    echo "0";
                }
            } else {
                echo "No existe";
            }
        }
    }

    public function _eliminar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['user_estacionamiento']);
        unset($_SESSION['user_parking_sub']);
        unset($_SESSION['user_parking_bd']);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        echo "Correcto";
    }

    public function _modificar()
    {
        $omodelo = new m_modelo('parking_subs');
        extract($_POST);
        $correo = $omodelo->link->real_escape_string(trim($correo ?? ''));

        if (empty($correo)) {
            echo "Error 3 No existe";
            return;
        }

        // Buscar en parking_subs.suscripciones
        $query = "SELECT ID_Suscripcion, Nombre_Negocio, Nombre_Contacto, BD FROM suscripciones WHERE Correo = '$correo' LIMIT 1";
        $rowSub = $omodelo->_consultar($query);

        if (!is_array($rowSub) || count($rowSub) === 0) {
            echo "Error 3 No existe";
            return;
        }

        $sub = $rowSub[0];

        // Generar clave temporal de 8 caracteres
        $cadena = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789";
        $newcontra = "";
        for ($i = 0; $i < 8; $i++) {
            $newcontra .= substr($cadena, rand(0, strlen($cadena) - 1), 1);
        }
        $hash = password_hash($newcontra, PASSWORD_BCRYPT);

        // Actualizar en parking_subs.suscripciones
        $updSub = $omodelo->_insertar("UPDATE suscripciones SET Contrasena = '$hash' WHERE Correo = '$correo'");
        if ($updSub === 'si') {
            echo "Error 2: " . mysqli_error($omodelo->link);
            return;
        }

        // Si tiene BD asignada, actualizar también en su tabla usuarios interna
        if (!empty($sub['BD'])) {
            $bd = $sub['BD'];
            $omodelo->_insertar("UPDATE `$bd`.`usuarios` SET Contrasena = '$hash' WHERE Correo = '$correo'");
        }

        // Contenido del email
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $urlLogin = 'http://' . $host . '/estacionamiento/web/app/';

        $contenidoRecuperar = '
        <p style="margin: 0 0 16px 0;">Hemos recibido una solicitud para restablecer la contraseña de acceso a tu cuenta en <b>ParkingPro</b> para el negocio <b>' . htmlspecialchars($sub['Nombre_Negocio']) . '</b>.</p>
        <p style="margin: 0 0 20px 0;">Se ha generado una clave provisional segura para que puedas ingresar nuevamente a tu panel:</p>
        
        <div style="background-color: #F0F9FF; border: 2px dashed #0284C7; border-radius: 12px; padding: 20px 24px; text-align: center; margin: 24px 0;">
            <span style="display: block; font-size: 12px; text-transform: uppercase; letter-spacing: 1.5px; color: #64748B; font-weight: 700; margin-bottom: 6px;">Tu Contraseña Temporal</span>
            <span style="font-family: \'Consolas\', \'Courier New\', monospace; font-size: 28px; font-weight: 800; color: #0284C7; letter-spacing: 4px;">' . $newcontra . '</span>
        </div>
        
        <div style="background-color: #FFFBEB; border-left: 4px solid #F59E0B; border-radius: 8px; padding: 14px 18px; margin: 20px 0;">
            <p style="margin: 0; font-size: 13px; color: #92400E; line-height: 1.5;">
                <b>Recomendación de Seguridad:</b> Esta contraseña es provisional. Te recomendamos cambiarla desde la sección "Mi Cuenta" una vez que ingreses a tu panel.
            </p>
        </div>';

        $cuerpoRecuperar = $omodelo->_plantillaCorreo(
            'Restablecer Contraseña',
            'Clave de acceso provisional',
            $contenidoRecuperar,
            'Iniciar Sesión en ParkingPro',
            $urlLogin,
            'Si tú no solicitaste este cambio, puedes ignorar este mensaje o escribir a soporte@estacionamiento.com.'
        );

        $omodelo->_email(
            $correo,
            'Restaurar tu contraseña de acceso - ParkingPro',
            $cuerpoRecuperar
        );

        echo "Correcto";
    }
}
?>
