<?php
include_once dirname(__DIR__) . '/modelo/m_modelo.php';

class c_perfil
{
    public function actualizarPerfil($idUsuario, $nombre, $correo, $passActual = '', $passNueva = '', $passConfirmar = '')
    {
        $model = new m_modelo();
        $idUsuario = intval($idUsuario);
        $nombre = $model->escape($nombre);
        $correo = $model->escape($correo);

        if (empty($nombre) || empty($correo)) {
            return [
                'status' => 'error',
                'message' => 'El nombre y el correo son obligatorios.'
            ];
        }

        // Verificar que el correo no esté ocupado por otro usuario
        $check = $model->_consultar("SELECT ID_Usuario FROM usuarios WHERE Correo = '$correo' AND ID_Usuario != $idUsuario LIMIT 1");
        if (is_array($check) && count($check) > 0) {
            return [
                'status' => 'error',
                'message' => 'Este correo ya está registrado por otra cuenta.'
            ];
        }

        // Si se solicitó cambio de contraseña
        $setPassSql = "";
        if (!empty($passNueva) || !empty($passActual)) {
            if (empty($passActual)) {
                return [
                    'status' => 'error',
                    'message' => 'Debes ingresar tu contraseña actual para autorizar el cambio.'
                ];
            }

            if (strlen($passNueva) < 6) {
                return [
                    'status' => 'error',
                    'message' => 'La nueva contraseña debe tener al menos 6 caracteres.'
                ];
            }

            if ($passNueva !== $passConfirmar) {
                return [
                    'status' => 'error',
                    'message' => 'La confirmación de la nueva contraseña no coincide.'
                ];
            }

            // Validar passActual contra la BD
            $userCheck = $model->_consultar("SELECT Contrasena FROM usuarios WHERE ID_Usuario = $idUsuario LIMIT 1");
            if (!is_array($userCheck) || count($userCheck) === 0 || !password_verify($passActual, $userCheck[0]['Contrasena'])) {
                return [
                    'status' => 'error',
                    'message' => 'La contraseña actual ingresada es incorrecta.'
                ];
            }

            $nuevoHash = password_hash($passNueva, PASSWORD_DEFAULT);
            $setPassSql = ", Contrasena = '$nuevoHash'";
        }

        $sql = "UPDATE usuarios SET Nombre = '$nombre', Correo = '$correo' $setPassSql WHERE ID_Usuario = $idUsuario";
        $res = $model->_insertar($sql);

        if ($res === 'no') {
            // Actualizar datos de la sesión activa
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_estacionamiento']['Nombre'] = $nombre;
            $_SESSION['user_estacionamiento']['Correo'] = $correo;

            return [
                'status' => 'success',
                'message' => 'Perfil actualizado exitosamente.'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'No se pudieron guardar los cambios en la base de datos.'
            ];
        }
    }
}
?>
