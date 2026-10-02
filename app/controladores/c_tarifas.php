<?php
class tarifas
{
    private function sesionExpirada()
    {
        echo json_encode(['status' => 'error', 'session_expired' => true, 'message' => 'Tu sesión expiró. Vuelve a iniciar sesión.']);
        exit();
    }

    private function getTenantBD()
    {
        $bd = '';
        if (!empty($_SESSION['user_parking_bd'])) {
            $bd = $_SESSION['user_parking_bd'];
        } elseif (!empty($_SESSION['user_parking_sub']['BD'])) {
            $bd = $_SESSION['user_parking_sub']['BD'];
        }

        // Solo bases de estacionamiento válidas (nunca la maestra ni una inexistente)
        if (!preg_match('/^parking_[A-Za-z0-9_]+$/', $bd) || $bd === 'parking_subs') {
            $this->sesionExpirada();
        }
        return $bd;
    }

    private function getIDSub()
    {
        $id = 0;
        if (isset($_SESSION['user_parking_sub']['ID_Suscripcion'])) {
            $id = (int)$_SESSION['user_parking_sub']['ID_Suscripcion'];
        } elseif (isset($_SESSION['user_estacionamiento']['ID_Usuario'])) {
            $id = (int)$_SESSION['user_estacionamiento']['ID_Usuario'];
        }
        if ($id <= 0) {
            $this->sesionExpirada();
        }
        return $id;
    }

    public function _consultar()
    {
        $bd = $this->getTenantBD();
        $idSub = $this->getIDSub();

        $mTenant = new m_modelo($bd);
        $cfgRow = $mTenant->_consultar("SELECT * FROM configuracion ORDER BY ID_Configuracion DESC LIMIT 1");

        // Datos de respaldo de suscripciones
        $mSubs = new m_modelo('parking_subs');
        $subRow = $mSubs->_consultar("SELECT Nombre_Negocio, Telefono FROM suscripciones WHERE ID_Suscripcion = $idSub LIMIT 1");
        $nombreNegocioSub = (is_array($subRow) && count($subRow) > 0 && !empty($subRow[0]['Nombre_Negocio'])) ? $subRow[0]['Nombre_Negocio'] : 'Estacionamiento';
        $telefonoSub = (is_array($subRow) && count($subRow) > 0 && !empty($subRow[0]['Telefono'])) ? $subRow[0]['Telefono'] : '';

        $datos = [
            'nombre'    => $nombreNegocioSub,
            'domicilio' => '',
            'telefono'  => $telefonoSub,
            'leyenda'   => '* No nos hacemos responsables de robo parcial o total, ni daños ocasionados por terceros *',
            'media'     => 10.00,
            'precio'    => 20.00,
            'extra'     => 10.00
        ];

        if (is_array($cfgRow) && count($cfgRow) > 0) {
            $c = $cfgRow[0];
            if (!empty($c['Nombre'])) $datos['nombre'] = $c['Nombre'];
            if (!empty($c['Domicilio'])) $datos['domicilio'] = $c['Domicilio'];
            if (!empty($c['Telefono'])) $datos['telefono'] = $c['Telefono'];
            if (!empty($c['Leyenda'])) $datos['leyenda'] = $c['Leyenda'];
            $datos['media'] = isset($c['Media']) ? floatval($c['Media']) : 10.00;
            $datos['precio'] = isset($c['Precio']) ? floatval($c['Precio']) : 20.00;
            $datos['extra'] = isset($c['Extra']) ? floatval($c['Extra']) : 10.00;
        }

        echo json_encode(['status' => 'success', 'data' => $datos]);
    }

    public function _modificar()
    {
        $bd = $this->getTenantBD();
        $idSub = $this->getIDSub();

        $mTenant = new m_modelo($bd);
        extract($_POST);

        $nombre = $mTenant->escape(trim($nombre ?? ''));
        $domicilio = $mTenant->escape(trim($domicilio ?? ''));
        $telefono = $mTenant->escape(trim($telefono ?? ''));
        $leyenda = $mTenant->escape(trim($leyenda ?? ''));
        $media = floatval($media ?? 10.00);
        $precio = floatval($precio ?? 20.00);
        $extra = floatval($extra ?? 10.00);

        if (empty($nombre)) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre del estacionamiento es obligatorio.']);
            return;
        }

        // Verificar si existe registro en configuracion
        $check = $mTenant->_consultar("SELECT ID_Configuracion FROM configuracion ORDER BY ID_Configuracion DESC LIMIT 1");
        if (is_array($check) && count($check) > 0) {
            $idCfg = (int)$check[0]['ID_Configuracion'];
            $sql = "UPDATE configuracion SET 
                Nombre = '$nombre', 
                Domicilio = '$domicilio', 
                Telefono = '$telefono', 
                Leyenda = '$leyenda', 
                Media = $media, 
                Precio = $precio, 
                Extra = $extra 
                WHERE ID_Configuracion = $idCfg";
        } else {
            $sql = "INSERT INTO configuracion (Nombre, Domicilio, Telefono, Leyenda, Media, Precio, Extra) 
                VALUES ('$nombre', '$domicilio', '$telefono', '$leyenda', $media, $precio, $extra)";
        }

        $res = $mTenant->_insertar($sql);

        // Actualizar datos en parking_subs.suscripciones para consistencia
        $mSubs = new m_modelo('parking_subs');
        $mSubs->_insertar("UPDATE suscripciones SET Nombre_Negocio = '$nombre', Telefono = '$telefono' WHERE ID_Suscripcion = $idSub");

        // Actualizar sesión activa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user_estacionamiento'])) {
            $_SESSION['user_estacionamiento']['Negocio'] = $nombre;
        }
        if (isset($_SESSION['user_parking_sub'])) {
            $_SESSION['user_parking_sub']['Nombre_Negocio'] = $nombre;
            $_SESSION['user_parking_sub']['Telefono'] = $telefono;
        }

        if ($res === 'no' || $res === true) {
            echo json_encode(['status' => 'success', 'message' => '¡Tarifas y datos guardados correctamente!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar los datos en la base de datos.']);
        }
    }
}
?>
