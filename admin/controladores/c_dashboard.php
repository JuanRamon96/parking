<?php
class dashboard
{
    public function _consultar()
    {
        header('Content-Type: application/json; charset=utf-8');
        $omodelo = new m_modelo('parking_subs');

        // Total clientes registrados
        $rowClientes = $omodelo->_consultar("SELECT COUNT(*) AS Total FROM suscripciones");
        $totalClientes = (is_array($rowClientes) && count($rowClientes) > 0) ? (int)$rowClientes[0]['Total'] : 0;

        // Clientes activos o en prueba
        $rowActivos = $omodelo->_consultar("SELECT COUNT(*) AS Total FROM suscripciones WHERE (Ilimitado = 1 OR Fecha_Vence > NOW()) AND Estatus != 'Vencido'");
        $totalActivos = (is_array($rowActivos) && count($rowActivos) > 0) ? (int)$rowActivos[0]['Total'] : 0;

        // Ingresos totales recaudados
        $rowIngresos = $omodelo->_consultar("SELECT IFNULL(SUM(Monto), 0) AS Total FROM pagos");
        $totalIngresos = (is_array($rowIngresos) && count($rowIngresos) > 0) ? (float)$rowIngresos[0]['Total'] : 0;

        // Ingresos por mes (últimos 6 meses)
        $rowMes = $omodelo->_consultar("SELECT DATE_FORMAT(Fecha_Pago, '%Y-%m') AS Mes, IFNULL(SUM(Monto), 0) AS Total FROM pagos WHERE Fecha_Pago >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(Fecha_Pago, '%Y-%m') ORDER BY Mes ASC");
        $ingresosPorMes = is_array($rowMes) ? $rowMes : [];

        // Registros de suscripciones por mes
        $rowRegMes = $omodelo->_consultar("SELECT DATE_FORMAT(Fecha_Alta, '%Y-%m') AS Mes, COUNT(*) AS Total FROM suscripciones WHERE Fecha_Alta >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(Fecha_Alta, '%Y-%m') ORDER BY Mes ASC");
        $registrosPorMes = is_array($rowRegMes) ? $rowRegMes : [];

        // Últimos 5 clientes
        $rowUltimos = $omodelo->_consultar("SELECT ID_Suscripcion, Nombre_Negocio, Correo, Plan, Estatus, Codigo_Corto, Fecha_Alta, Ilimitado, Fecha_Vence FROM suscripciones ORDER BY Fecha_Alta DESC LIMIT 5");
        $ultimosClientes = is_array($rowUltimos) ? $rowUltimos : [];

        // Últimos 5 pagos
        $rowPagos = $omodelo->_consultar("SELECT p.ID_Pago, p.Monto, p.Plan, p.Metodo_Pago, p.Fecha_Pago, s.Nombre_Negocio, p.ID_Transaccion_PayPal FROM pagos p INNER JOIN suscripciones s ON s.ID_Suscripcion = p.FK_Suscripcion ORDER BY p.Fecha_Pago DESC LIMIT 5");
        $ultimosPagos = is_array($rowPagos) ? $rowPagos : [];

        echo json_encode([
            'totalClientes'   => $totalClientes,
            'totalActivos'    => $totalActivos,
            'totalIngresos'   => $totalIngresos,
            'ingresosPorMes'  => $ingresosPorMes,
            'registrosPorMes' => $registrosPorMes,
            'ultimosClientes' => $ultimosClientes,
            'ultimosPagos'    => $ultimosPagos
        ]);
    }
}
?>
