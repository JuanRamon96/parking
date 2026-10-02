<?php
class cuenta
{
    public function _consultar()
    {
        $omodelo = new m_modelo('parking_subs');
        $id = isset($_SESSION['user_parking_admin']['ID_Usuario']) ? (int)$_SESSION['user_parking_admin']['ID_Usuario'] : 0;

        if ($id <= 0) {
            echo json_encode(['error' => 'Sesión no válida']);
            return;
        }

        $query = "SELECT ID_Usuario, Nombre, Correo, Estatus, Fecha_Alta FROM usuarios_admin WHERE ID_Usuario = '$id' LIMIT 1";
        $row = $omodelo->_consultar($query);

        if (is_array($row) && count($row) > 0) {
            echo json_encode($row[0]);
        } else {
            echo json_encode(['error' => 'Usuario administrador no encontrado']);
        }
    }

    public function _modificar()
    {
        $omodelo = new m_modelo('parking_subs');
        extract($_POST);

        $id = isset($_SESSION['user_parking_admin']['ID_Usuario']) ? (int)$_SESSION['user_parking_admin']['ID_Usuario'] : 0;

        if ($id <= 0) {
            echo "Sesión expirada";
            return;
        }

        $nombre = $omodelo->escape(trim($nombre ?? ''));
        $correo = $omodelo->escape(trim($correo ?? ''));
        $contrasenaActual = trim($contrasenaActual ?? '');
        $nuevaContrasena = trim($nuevaContrasena ?? '');

        if ($nombre == '' || $correo == '') {
            echo "Faltan datos obligatorios";
            return;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo "Formato de correo no válido";
            return;
        }

        // Comprobar correo único en usuarios_admin
        $check = $omodelo->_consultar("SELECT ID_Usuario FROM usuarios_admin WHERE Correo = '$correo' AND ID_Usuario != '$id' LIMIT 1");
        if (is_array($check) && count($check) > 0) {
            echo "Correo Duplicado";
            return;
        }

        // Consultar contraseña actual almacenada
        $userRow = $omodelo->_consultar("SELECT Contrasena FROM usuarios_admin WHERE ID_Usuario = '$id' LIMIT 1");
        if (!is_array($userRow) || count($userRow) === 0) {
            echo "Usuario no existe";
            return;
        }

        $sqlPass = "";
        if ($nuevaContrasena != '') {
            if ($contrasenaActual == '') {
                echo "Contrasena Actual Requerida";
                return;
            }

            if (!password_verify($contrasenaActual, $userRow[0]['Contrasena'])) {
                echo "Contrasena Actual Incorrecta";
                return;
            }

            if (strlen($nuevaContrasena) < 6) {
                echo "La nueva contraseña debe tener al menos 6 caracteres";
                return;
            }

            $nuevoHash = password_hash($nuevaContrasena, PASSWORD_BCRYPT, ['cost' => 10]);
            $sqlPass = ", Contrasena = '$nuevoHash'";
        }

        $query = "UPDATE usuarios_admin SET Nombre = '$nombre', Correo = '$correo' $sqlPass WHERE ID_Usuario = '$id'";
        $error = $omodelo->_insertar($query);

        if ($error == 'si') {
            echo "Error al actualizar la base de datos";
        } else {
            $_SESSION['user_parking_admin']['Nombre'] = $nombre;
            $_SESSION['user_parking_admin']['Correo'] = $correo;
            echo "Correcto";
        }
    }
}

class perfil extends cuenta {}
?>
