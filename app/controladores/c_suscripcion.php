<?php
require_once __DIR__ . '/../modelo/m_modelo.php';

class suscripcion
{
    // Precio de cada plan en MXN: el servidor decide el monto, nunca el navegador
    const PLANES = array(
        'Mensual'   => 250,
        'Anual'     => 2500,
        'Ilimitado' => 4999
    );

    private function idSuscripcion()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $id = (int)($_SESSION['user_estacionamiento']['ID_Usuario'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'session_expired' => true, 'message' => 'Tu sesión expiró. Vuelve a iniciar sesión.']);
            exit();
        }
        return $id;
    }

    public function _consultar()
    {
        header('Content-Type: application/json; charset=utf-8');
        $idSub = $this->idSuscripcion();
        $mSubs = new m_modelo('parking_subs');

        $rowSub = $mSubs->_consultar("SELECT ID_Suscripcion, Nombre_Negocio, Correo, Plan, Fecha_Alta, Fecha_Vence, Ilimitado, Estatus FROM suscripciones WHERE ID_Suscripcion = $idSub LIMIT 1");

        if (!is_array($rowSub) || count($rowSub) === 0) {
            echo json_encode(['status' => 'error', 'message' => 'No se encontró la suscripción.']);
            return;
        }

        $sub = $rowSub[0];

        // Calcular días restantes
        $esIlimitado = intval($sub['Ilimitado']) === 1;
        $timestampVence = strtotime($sub['Fecha_Vence']);
        $diasRestantes = ($timestampVence !== false) ? (int)ceil(($timestampVence - time()) / 86400) : 0;

        $estadoReal = $sub['Estatus'];
        if (!$esIlimitado && $diasRestantes <= 0) {
            $estadoReal = 'Vencido';
            if ($sub['Estatus'] !== 'Vencido') {
                $mSubs->_insertar("UPDATE suscripciones SET Estatus = 'Vencido' WHERE ID_Suscripcion = $idSub");
            }
        }

        // Historial de pagos
        $pagos = [];
        $resPagos = $mSubs->_consultar("SELECT ID_Pago, Monto, Plan, Metodo_Pago, ID_Transaccion_PayPal, Fecha_Pago FROM pagos WHERE FK_Suscripcion = $idSub ORDER BY Fecha_Pago DESC");
        if (is_array($resPagos)) {
            $pagos = $resPagos;
        }

        echo json_encode([
            'status' => 'success',
            'plan' => $sub['Plan'],
            'estatus' => $estadoReal,
            'esIlimitado' => $esIlimitado,
            'fechaAlta' => $sub['Fecha_Alta'],
            'fechaVence' => $sub['Fecha_Vence'],
            'diasRestantes' => $diasRestantes,
            'pagos' => $pagos
        ]);
    }

    /**
     * Activa un plan SOLO después de confirmar con PayPal que la orden existe,
     * está pagada, es por el monto del plan y no se ha usado antes.
     */
    public function _insertar()
    {
        header('Content-Type: application/json; charset=utf-8');
        $idSub = $this->idSuscripcion();
        $mSubs = new m_modelo('parking_subs');

        $planElegido = trim((string)($_POST['plan'] ?? ''));
        $orderId = trim((string)($_POST['orderId'] ?? ''));

        if (!isset(self::PLANES[$planElegido])) {
            echo json_encode(['status' => 'error', 'message' => 'El plan seleccionado no es válido.']);
            return;
        }
        if (!preg_match('/^[A-Z0-9]{10,40}$/', $orderId)) {
            echo json_encode(['status' => 'error', 'message' => 'El número de orden de PayPal no es válido.']);
            return;
        }

        $monto = self::PLANES[$planElegido];
        $orderEsc = $mSubs->escape($orderId);

        // 1. La misma orden no se puede aplicar dos veces
        $usada = $mSubs->_consultar("SELECT ID_Pago FROM pagos WHERE ID_Transaccion_PayPal = '$orderEsc' LIMIT 1");
        if (is_array($usada) && count($usada) > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Este pago ya fue aplicado anteriormente.']);
            return;
        }

        // 2. Confirmar la orden directamente con PayPal
        $verificacion = $this->verificarOrdenPayPal($orderId, $monto);
        if (!$verificacion['ok']) {
            error_log("[suscripcion] Pago no verificado. Sub $idSub, orden $orderId: " . $verificacion['message']);
            echo json_encode(['status' => 'error', 'message' => $verificacion['message']]);
            return;
        }

        // 3. Activar o extender el plan
        if ($planElegido === 'Ilimitado') {
            $querySub = "UPDATE suscripciones SET 
                Plan = 'Ilimitado',
                Ilimitado = 1,
                Estatus = 'Activo',
                Fecha_Vence = '2099-12-31 23:59:59'
            WHERE ID_Suscripcion = $idSub";
        } else {
            $dias = ($planElegido === 'Anual') ? 365 : 30;
            $querySub = "UPDATE suscripciones SET 
                Plan = '$planElegido',
                Ilimitado = 0,
                Estatus = 'Activo',
                Fecha_Vence = DATE_ADD(GREATEST(NOW(), Fecha_Vence), INTERVAL $dias DAY)
            WHERE ID_Suscripcion = $idSub";
        }

        $resUpdate = $mSubs->_insertar($querySub);
        if ($resUpdate === 'si') {
            error_log("[suscripcion] Pago verificado pero no se pudo activar. Sub $idSub, orden $orderId");
            echo json_encode(['status' => 'error', 'message' => 'Tu pago fue confirmado, pero hubo un error al activar el plan. Contáctanos para aplicarlo.']);
            return;
        }

        // 4. Registrar el pago con los datos confirmados por PayPal (no los que manda el navegador)
        $montoPagado = (float)$verificacion['monto'];
        $detalles = $mSubs->escape(json_encode($verificacion['detalles'], JSON_UNESCAPED_UNICODE));

        $mSubs->_insertar("INSERT INTO pagos (
            FK_Suscripcion,
            Monto,
            Plan,
            Metodo_Pago,
            ID_Transaccion_PayPal,
            Fecha_Pago,
            Detalles
        ) VALUES (
            $idSub,
            $montoPagado,
            '$planElegido',
            'PayPal',
            '$orderEsc',
            NOW(),
            '$detalles'
        )");

        // Mantener la sesión al día
        if (isset($_SESSION['user_estacionamiento'])) {
            $_SESSION['user_estacionamiento']['Estatus'] = 'Activo';
        }

        echo json_encode([
            'status' => 'success',
            'message' => '¡Tu suscripción se ha acreditado correctamente!'
        ]);
    }

    /**
     * Consulta la orden en la API de PayPal.
     * Requiere las variables de entorno PAYPAL_CLIENT_ID y PAYPAL_SECRET
     * (y PAYPAL_MODE=sandbox para pruebas; por defecto es live).
     */
    private function verificarOrdenPayPal($orderId, $montoEsperado)
    {
        $clientId = getenv('PAYPAL_CLIENT_ID');
        $secret = getenv('PAYPAL_SECRET');
        $modo = strtolower((string)(getenv('PAYPAL_MODE') ?: 'live'));

        if (!$clientId || !$secret) {
            return ['ok' => false, 'message' => 'No pudimos confirmar tu pago en este momento. Contáctanos con tu número de orden para activarlo.'];
        }

        $base = ($modo === 'sandbox') ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';

        // Token de acceso
        $ch = curl_init($base . '/v1/oauth2/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_USERPWD        => $clientId . ':' . $secret,
            CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 15
        ]);
        $rawToken = curl_exec($ch);
        $codeToken = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $token = json_decode((string)$rawToken, true)['access_token'] ?? '';
        if ($codeToken !== 200 || $token === '') {
            error_log("[suscripcion] PayPal token HTTP $codeToken");
            return ['ok' => false, 'message' => 'No pudimos comunicarnos con PayPal para confirmar tu pago. Contáctanos con tu número de orden.'];
        }

        // Detalle de la orden
        $ch = curl_init($base . '/v2/checkout/orders/' . rawurlencode($orderId));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token, 'Accept: application/json'],
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 15
        ]);
        $rawOrden = curl_exec($ch);
        $codeOrden = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $orden = json_decode((string)$rawOrden, true);
        if ($codeOrden !== 200 || !is_array($orden)) {
            error_log("[suscripcion] PayPal orden $orderId HTTP $codeOrden");
            return ['ok' => false, 'message' => 'PayPal no reconoce esta orden de pago.'];
        }

        if (($orden['status'] ?? '') !== 'COMPLETED') {
            return ['ok' => false, 'message' => 'El pago todavía no aparece como completado en PayPal.'];
        }

        $unidad = $orden['purchase_units'][0] ?? [];
        $captura = $unidad['payments']['captures'][0] ?? [];

        if (($captura['status'] ?? '') !== 'COMPLETED') {
            return ['ok' => false, 'message' => 'El cobro en PayPal no se completó.'];
        }

        $moneda = $captura['amount']['currency_code'] ?? ($unidad['amount']['currency_code'] ?? '');
        $valor = (float)($captura['amount']['value'] ?? ($unidad['amount']['value'] ?? 0));

        if ($moneda !== 'MXN' || $valor + 0.01 < $montoEsperado) {
            return ['ok' => false, 'message' => 'El monto pagado no corresponde al plan seleccionado.'];
        }

        return [
            'ok' => true,
            'monto' => $valor,
            'detalles' => [
                'order_id'   => $orden['id'] ?? $orderId,
                'capture_id' => $captura['id'] ?? '',
                'status'     => $orden['status'],
                'monto'      => $valor,
                'moneda'     => $moneda,
                'pagador'    => $orden['payer']['email_address'] ?? '',
                'fecha'      => $captura['create_time'] ?? ''
            ]
        ];
    }
}
?>
