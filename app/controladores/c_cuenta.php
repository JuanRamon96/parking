<?php
/**
 * Mi Cuenta: nombre, correo y contraseña del dueño del estacionamiento.
 *
 * El inicio de sesión se valida contra parking_subs.suscripciones, así que los
 * cambios se guardan ahí. También se replican en la tabla usuarios del
 * estacionamiento para mantener ambas iguales.
 */
class cuenta
{
    private function idSuscripcion()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return (int)($_SESSION['user_estacionamiento']['ID_Usuario'] ?? 0);
    }

    private function bdTenant()
    {
        $bd = $_SESSION['user_parking_bd'] ?? ($_SESSION['user_parking_sub']['BD'] ?? '');
        if (preg_match('/^parking_[A-Za-z0-9_]+$/', $bd) && $bd !== 'parking_subs') {
            return $bd;
        }
        return '';
    }

    public function _consultar()
    {
        $id = $this->idSuscripcion();
        if ($id <= 0) {
            echo json_encode(array('session_expired' => true));
            return;
        }

        $omodelo = new m_modelo('parking_subs');
        $row = $omodelo->_consultar("SELECT ID_Suscripcion AS ID_Usuario, Nombre_Contacto AS Nombre, Correo, Estatus, Fecha_Alta AS Fecha_Registro
                                     FROM suscripciones WHERE ID_Suscripcion = $id LIMIT 1");

        if (is_array($row) && count($row) > 0) {
            echo json_encode($row[0]);
        } else {
            echo json_encode(array('error' => 'Usuario no encontrado'));
        }
    }

    public function _modificar()
    {
        $id = $this->idSuscripcion();
        if ($id <= 0) {
            echo "Tu sesión expiró. Vuelve a iniciar sesión.";
            return;
        }

        $omodelo = new m_modelo('parking_subs');

        $nombre = trim((string)($_POST['nombre'] ?? ''));
        $correo = strtolower(trim((string)($_POST['correo'] ?? '')));
        $contrasenaActual = trim((string)($_POST['contrasenaActual'] ?? ''));
        $nuevaContrasena = trim((string)($_POST['nuevaContrasena'] ?? ''));

        if ($nombre === '' || $correo === '') {
            echo "Faltan datos obligatorios";
            return;
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo "El correo electrónico no es válido.";
            return;
        }

        $nombreEsc = $omodelo->escape($nombre);
        $correoEsc = $omodelo->escape($correo);

        // Correo único entre todas las cuentas
        $check = $omodelo->_consultar("SELECT ID_Suscripcion FROM suscripciones WHERE Correo = '$correoEsc' AND ID_Suscripcion != $id LIMIT 1");
        if (is_array($check) && count($check) > 0) {
            echo "Correo Duplicado";
            return;
        }

        // Datos actuales
        $actual = $omodelo->_consultar("SELECT Correo, Contrasena FROM suscripciones WHERE ID_Suscripcion = $id LIMIT 1");
        if (!is_array($actual) || count($actual) === 0) {
            echo "Usuario no existe";
            return;
        }
        $correoAnterior = $actual[0]['Correo'];

        $nuevoHash = '';
        if ($nuevaContrasena !== '') {
            if ($contrasenaActual === '') {
                echo "Contrasena Actual Requerida";
                return;
            }
            if (strlen($nuevaContrasena) < 6) {
                echo "La nueva contraseña debe tener al menos 6 caracteres.";
                return;
            }
            if (!password_verify($contrasenaActual, $actual[0]['Contrasena'])) {
                echo "Contrasena Actual Incorrecta";
                return;
            }
            $nuevoHash = password_hash($nuevaContrasena, PASSWORD_BCRYPT);
        }

        $sqlPass = ($nuevoHash !== '') ? ", Contrasena = '$nuevoHash'" : "";
        $error = $omodelo->_insertar("UPDATE suscripciones SET Nombre_Contacto = '$nombreEsc', Correo = '$correoEsc' $sqlPass WHERE ID_Suscripcion = $id");

        if ($error == 'si') {
            echo "Error al actualizar: " . mysqli_error($omodelo->link);
            return;
        }

        // Replicar en la tabla usuarios del estacionamiento (si existe)
        $bd = $this->bdTenant();
        if ($bd !== '') {
            $correoAnteriorEsc = $omodelo->escape($correoAnterior);
            try {
                $omodelo->_insertar("UPDATE `$bd`.`usuarios` SET Nombre = '$nombreEsc', Correo = '$correoEsc' $sqlPass WHERE Correo = '$correoAnteriorEsc'");
            } catch (Throwable $e) {
                error_log('[cuenta] No se pudo replicar en ' . $bd . '.usuarios: ' . $e->getMessage());
            }
        }

        // Actualizar la sesión
        $_SESSION['user_estacionamiento']['Nombre'] = $nombre;
        $_SESSION['user_estacionamiento']['Correo'] = $correo;
        if (isset($_SESSION['user_parking_sub'])) {
            $_SESSION['user_parking_sub']['Nombre_Contacto'] = $nombre;
            $_SESSION['user_parking_sub']['Correo'] = $correo;
        }

        echo "Correcto";
    }
}
?>
