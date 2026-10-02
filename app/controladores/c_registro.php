<?php
class registro
{
    public function _insertar()
    {
        header('Content-Type: application/json; charset=utf-8');
        $omodelo = new m_modelo('parking_subs');
        extract($_POST);

        $nombreNegocio = $omodelo->escape($nombreNegocio ?? '');
        $nombreContacto = $omodelo->escape($nombreContacto ?? '');
        $correo = strtolower($omodelo->escape($correo ?? ''));
        $telefono = $omodelo->escape($telefono ?? '');
        $contrasena = trim($contrasena ?? '');

        if (empty($nombreNegocio)) {
            echo json_encode(['status' => 'error', 'message' => 'Por favor escribe el nombre de tu estacionamiento.']);
            return;
        }

        if (empty($nombreContacto)) {
            echo json_encode(['status' => 'error', 'message' => 'Por favor escribe tu nombre completo.']);
            return;
        }

        if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Ingresa un correo electrónico válido.']);
            return;
        }

        if (strlen($contrasena) < 6) {
            echo json_encode(['status' => 'error', 'message' => 'La contraseña debe tener al menos 6 caracteres.']);
            return;
        }

        // Verificar si el correo ya existe
        $existe = $omodelo->_consultar("SELECT ID_Suscripcion FROM suscripciones WHERE Correo = '$correo' LIMIT 1");
        if (is_array($existe) && count($existe) > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Este correo electrónico ya está registrado. Por favor inicia sesión.']);
            return;
        }

        // Generar Código Corto único de 6 caracteres (ej: PK-9241)
        $sufijo = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
        $codigoCorto = "PK-" . $sufijo;

        // Generar Token de Enlace único de 32 caracteres
        $tokenEnlace = "pk_live_" . bin2hex(random_bytes(12));

        $hashContrasena = password_hash($contrasena, PASSWORD_BCRYPT);

        // Insertar en parking_subs.suscripciones con 7 días de prueba gratis
        $querySub = "INSERT INTO suscripciones (
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
        ) VALUES (
            '$nombreNegocio',
            '$nombreContacto',
            '$correo',
            '$telefono',
            '$hashContrasena',
            'Prueba',
            NOW(),
            DATE_ADD(NOW(), INTERVAL 7 DAY),
            0,
            'Prueba',
            '$tokenEnlace',
            '$codigoCorto',
            ''
        )";

        $res = $omodelo->_insertar($querySub);
        if ($res === 'si') {
            echo json_encode(['status' => 'error', 'message' => 'Error al registrar la suscripción: ' . mysqli_error($omodelo->link)]);
            return;
        }

        $idSub = mysqli_insert_id($omodelo->link);
        $bdName = "parking_" . $idSub;

        // Actualizar el nombre de BD
        $omodelo->_insertar("UPDATE suscripciones SET BD = '$bdName' WHERE ID_Suscripcion = $idSub");

        // Aprovisionar la base de datos y tablas del cliente
        $omodelo->_crear($idSub, $nombreNegocio, $nombreContacto, $correo, $hashContrasena);

        // Iniciar sesión automáticamente
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $rowSub = $omodelo->_consultar("SELECT * FROM suscripciones WHERE ID_Suscripcion = $idSub LIMIT 1");
        if (is_array($rowSub) && count($rowSub) > 0) {
            unset($rowSub[0]['Contrasena']);
            $_SESSION['user_parking_sub'] = $rowSub[0];
            $_SESSION['user_parking_bd'] = $bdName;
            $_SESSION['user_estacionamiento'] = [
                'ID_Usuario' => $idSub,
                'Nombre' => $nombreContacto,
                'Negocio' => $nombreNegocio,
                'Correo' => $correo,
                'Estatus' => 'Prueba'
            ];
        }

        echo json_encode([
            'status' => 'success',
            'message' => '¡Cuenta creada con éxito! Tu periodo de 7 días de prueba está activo.',
            'codigo' => $codigoCorto,
            'token' => $tokenEnlace
        ]);
    }
}
?>
