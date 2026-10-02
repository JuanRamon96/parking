<?php
class dispositivos
{
    public function _consultar()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $idSub = (int)($_SESSION['user_estacionamiento']['ID_Usuario'] ?? 0);
        if ($idSub <= 0) {
            echo json_encode(['status' => 'error', 'session_expired' => true, 'message' => 'Tu sesión expiró. Vuelve a iniciar sesión.']);
            return;
        }
        $mSubs = new m_modelo('parking_subs');

        $rowSub = $mSubs->_consultar("SELECT ID_Suscripcion, Nombre_Negocio, Token_Enlace, Codigo_Corto, Plan, Estatus, Fecha_Vence, Ilimitado FROM suscripciones WHERE ID_Suscripcion = $idSub LIMIT 1");

        if (!is_array($rowSub) || count($rowSub) === 0) {
            echo json_encode(['status' => 'error', 'message' => 'No se encontró la información del negocio.']);
            return;
        }

        $sub = $rowSub[0];

        // Tablets del estacionamiento, identificadas por su ID único (el nombre puede repetirse).
        // Se juntan entradas (tablet donde entró el auto) y cobros (tablet que cobró).
        $mTenant = new m_modelo();
        asegurarColumnasTablets($mTenant);

        $actividad = $mTenant->_consultar("SELECT Clave, MAX(Fecha) AS Ultima, SUM(Entradas) AS Entradas, SUM(Cobros) AS Cobros FROM (
                SELECT " . sqlClaveEntrada() . " AS Clave, MAX(Entrada) AS Fecha, COUNT(*) AS Entradas, 0 AS Cobros
                FROM registros GROUP BY Clave
                UNION ALL
                SELECT " . sqlClaveCobro() . ", MAX(Salida), 0, COUNT(*)
                FROM registros WHERE Estatus IN ('Completado', 'Cobrado') GROUP BY 1
            ) t GROUP BY Clave ORDER BY Ultima DESC");

        $tablets = [];
        if (is_array($actividad)) {
            foreach ($actividad as $a) {
                $clave = (string)$a['Clave'];
                $esViejo = strpos($clave, 'nombre:') === 0;
                $tablets[] = [
                    'Dispositivo'      => nombreTablet($mTenant, $clave),
                    'ID_Tablet'        => $esViejo ? '' : $clave,
                    'Ultima_Actividad' => $a['Ultima'] ? date('d/m/Y H:i', strtotime($a['Ultima'])) : '',
                    'Total_Vehiculos'  => (int)$a['Entradas'],
                    'Total_Cobros'     => (int)$a['Cobros']
                ];
            }
        }

        // Construir URL base del servidor
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
            || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
            || (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'parkingpro.top') !== false);
        $protocol = $isHttps ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        // Si se accede desde localhost en la PC, sustituir por la IP LAN local para que los celulares/tablets puedan conectarse
        $hostParts = explode(':', $host);
        $hostName = strtolower($hostParts[0]);
        $port = isset($hostParts[1]) ? (':' . $hostParts[1]) : '';

        $lanIp = '';
        if ($hostName === 'localhost' || $hostName === '127.0.0.1') {
            $ips = gethostbynamel(gethostname());
            if (is_array($ips)) {
                foreach ($ips as $ip) {
                    if ($ip !== '127.0.0.1' && strpos($ip, '192.168.56.') !== 0 && strpos($ip, '169.254.') !== 0) {
                        $lanIp = $ip;
                        $hostName = $ip;
                        break;
                    }
                }
            }
        }

        $baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $servidorUrl = $protocol . $hostName . $port . $baseDir . '/subir.php';

        echo json_encode([
            'status' => 'success',
            'codigo' => $sub['Codigo_Corto'],
            'token' => $sub['Token_Enlace'],
            'nombre' => $sub['Nombre_Negocio'],
            'plan' => $sub['Plan'],
            'servidorUrl' => $servidorUrl,
            'lanIp' => $lanIp,
            'tablets' => $tablets
        ]);
    }
}
?>
