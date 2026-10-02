<?php
class login
{
    public function _consultar()
    {
        $omodelo = new m_modelo('parking_subs');
        extract($_POST);

        $correo = $omodelo->escape(trim($correo ?? ''));
        $contrasena = trim($contrasena ?? '');

        if (empty($correo) || empty($contrasena)) {
            echo '0';
            return;
        }

        $row = $omodelo->_consultar("SELECT ID_Usuario, Nombre, Correo, Contrasena, Estatus FROM usuarios_admin WHERE Correo = '$correo' AND Estatus = 'Desbloqueado' LIMIT 1");

        if (!is_array($row) || count($row) === 0) {
            echo '0';
            return;
        }

        if (password_verify($contrasena, $row[0]['Contrasena'])) {
            $_SESSION['user_parking_admin'] = [
                'ID_Usuario' => $row[0]['ID_Usuario'],
                'Nombre' => $row[0]['Nombre'],
                'Correo' => $row[0]['Correo']
            ];
            echo 'Correcto';
        } else {
            echo '0';
        }
    }

    public function _eliminar()
    {
        unset($_SESSION['user_parking_admin']);
        echo 'Correcto';
    }
}
?>
