<?php
class reportes_admin
{
    public function _consultar()
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        $omodelo = new m_modelo('parking_subs');
        extract($_POST);

        $tipo = $tipo ?? '';

        if ($tipo === 'pagos') {
            $buscar  = $omodelo->escape($buscar ?? '');
            $limit   = max(1, (int)($limit ?? 25));
            $pagina  = max(1, (int)($pagina ?? 1));
            $offset  = ($pagina * $limit) - $limit;

            $ordenColumna = $omodelo->escape($ordenColumna ?? 'Fecha');
            $orden = (isset($orden) && strtolower($orden) === 'asc') ? 'ASC' : 'DESC';

            $columnasMap = [
                'ID_PayPal' => 'p.ID_Transaccion_PayPal',
                'Negocio'   => 's.Nombre_Negocio',
                'Plan'      => 'p.Plan',
                'Monto'     => 'p.Monto',
                'Metodo'    => 'p.Metodo_Pago',
                'Fecha'     => 'p.Fecha_Pago'
            ];
            $campoOrden = $columnasMap[$ordenColumna] ?? 'p.Fecha_Pago';

            $busqueda = '';
            if (trim($buscar) !== '') {
                $busqueda = "AND (p.ID_Transaccion_PayPal LIKE '%$buscar%' OR s.Nombre_Negocio LIKE '%$buscar%' OR s.Correo LIKE '%$buscar%' OR p.Plan LIKE '%$buscar%' OR p.Metodo_Pago LIKE '%$buscar%' OR p.Monto LIKE '%$buscar%')";
            }

            $countQuery = "SELECT COUNT(*) AS Num, IFNULL(SUM(p.Monto), 0) AS SumaTotal 
                           FROM pagos p 
                           LEFT JOIN suscripciones s ON s.ID_Suscripcion = p.FK_Suscripcion 
                           WHERE 1=1 $busqueda";
            $countRow = $omodelo->_consultar($countQuery);
            $numRows = (is_array($countRow) && count($countRow) > 0) ? (int)$countRow[0]['Num'] : 0;
            $sumaTotal = (is_array($countRow) && count($countRow) > 0) ? (float)$countRow[0]['SumaTotal'] : 0.0;

            $query = "SELECT p.ID_Pago, p.Monto, p.Plan, p.Metodo_Pago, p.ID_Transaccion_PayPal, p.Fecha_Pago, s.Nombre_Negocio, s.Correo 
                      FROM pagos p 
                      LEFT JOIN suscripciones s ON s.ID_Suscripcion = p.FK_Suscripcion 
                      WHERE 1=1 $busqueda 
                      ORDER BY $campoOrden $orden 
                      LIMIT $limit OFFSET $offset";

            $rows = $omodelo->_consultar($query);
            $arreglo = [
                'data' => [],
                'totales' => [
                    'NumRows' => $numRows,
                    'Total'   => '$' . number_format($sumaTotal, 2)
                ]
            ];

            if (is_array($rows) && count($rows) > 0) {
                foreach ($rows as $i => $p) {
                    $ref = $p['ID_Transaccion_PayPal'] ?: ('PAY-' . $p['ID_Pago']);
                    $badgePlan = '<span class="badge bg-primary-subtle text-primary border px-2 py-1 fw-bold">' . htmlspecialchars($p['Plan']) . '</span>';
                    $negocioHtml = '<div class="fw-bold text-dark">' . htmlspecialchars($p['Nombre_Negocio'] ?: 'Negocio General') . '</div>' .
                                   '<small class="text-muted"><i class="fa-regular fa-envelope me-1"></i>' . htmlspecialchars($p['Correo'] ?: '-') . '</small>';
                    $metodoBadge = '<span class="badge bg-light text-dark border px-2 py-1"><i class="fa-brands fa-paypal text-primary me-1"></i>' . htmlspecialchars($p['Metodo_Pago']) . '</span>';
                    $montoHtml = '<span class="fw-bold text-success dinero">$' . number_format((float)$p['Monto'], 2) . '</span>';
                    $fechaHtml = date('d/m/Y H:i', strtotime($p['Fecha_Pago']));

                    $arreglo['data'][$i] = [
                        'ID'        => $p['ID_Pago'],
                        'ID_PayPal' => '<code>' . htmlspecialchars($ref) . '</code>',
                        'Negocio'   => $negocioHtml,
                        'Plan'      => $badgePlan,
                        'Monto'     => $montoHtml,
                        'Metodo'    => $metodoBadge,
                        'Fecha'     => $fechaHtml
                    ];
                }
            }

            echo json_encode($arreglo);
            return;
        }

        // Resumen / KPIs y Gráficas
        $rowPlanes = $omodelo->_consultar("SELECT Plan, COUNT(*) AS Total FROM suscripciones GROUP BY Plan ORDER BY Total DESC");
        $rowMes = $omodelo->_consultar("SELECT DATE_FORMAT(Fecha_Pago, '%Y-%m') AS Mes, SUM(Monto) AS Total, COUNT(*) AS Num FROM pagos WHERE Fecha_Pago >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY DATE_FORMAT(Fecha_Pago, '%Y-%m') ORDER BY Mes ASC");
        $rowResumen = $omodelo->_consultar("SELECT IFNULL(SUM(Monto), 0) AS Total, COUNT(*) AS Num FROM pagos");

        echo json_encode([
            'porPlan'     => is_array($rowPlanes) ? $rowPlanes : [],
            'ingresosMes' => is_array($rowMes) ? $rowMes : [],
            'resumen'     => (is_array($rowResumen) && count($rowResumen) > 0) ? $rowResumen[0] : ['Total' => 0, 'Num' => 0]
        ]);
    }
}
?>
