<?php
class dashboard
{
    private function valor($omodelo, $query, $campo)
    {
        $res = $omodelo->_consultar($query);
        if ($res != 'si' && $omodelo->numerofilas > 0) {
            return $res[0][$campo];
        }
        return 0;
    }

    public function _consultar()
    {
        $omodelo = new m_modelo();
        asegurarColumnasTablets($omodelo);
        extract($_POST);

        $tipo = isset($tipo) ? trim($tipo) : '';

        // Si se solicita la tabla con myDataTable (paginación, búsqueda, ordenamiento)
        if ($tipo == 'tabla' || isset($limit)) {
            $buscar = isset($buscar) ? $omodelo->escape($buscar) : '';
            $limit = isset($limit) && (int)$limit > 0 ? (int)$limit : 25;
            $pagina = isset($pagina) && (int)$pagina > 0 ? (int)$pagina : 1;
            $ordenColumna = isset($ordenColumna) && trim($ordenColumna) != '' ? $omodelo->escape($ordenColumna) : 'Entrada';
            $orden = isset($orden) && strtolower($orden) == 'asc' ? 'ASC' : 'DESC';

            $columnasValidas = array(
                'ID_Registro' => 'ID_Registro',
                'Dispositivo' => 'Dispositivo',
                'Placas' => 'Placas',
                'Tipo' => 'Tipo',
                'Entrada' => 'Entrada',
                'Salida' => 'Salida',
                'Horas' => 'Horas',
                'Total' => 'Total',
                'Estatus' => 'Estatus'
            );
            $orderField = isset($columnasValidas[$ordenColumna]) ? $columnasValidas[$ordenColumna] : 'ID_Registro';

            $where = "WHERE 1=1";
            if (trim($buscar) != '') {
                $palabras = explode(' ', trim($buscar));
                for ($i = 0; $i < count($palabras); $i++) {
                    $p = $omodelo->escape($palabras[$i]);
                    $where .= " AND CONCAT_WS(' ', ID_Registro, IFNULL(Folio_Tablet, ''), IFNULL(Dispositivo, ''), IFNULL(Dispositivo_Cobro, ''), Placas, Tipo, Descripcion, Estatus, Total, DATE_FORMAT(Entrada, '%d/%m/%Y %H:%i'), IFNULL(DATE_FORMAT(Salida, '%d/%m/%Y %H:%i'), '')) REGEXP '$p'";
                }
            }

            $offset = ($pagina - 1) * $limit;

            $countQuery = "SELECT COUNT(*) AS Num FROM registros $where";
            $countRow = $omodelo->_consultar($countQuery);
            $numRows = ($countRow != 'si' && $omodelo->numerofilas > 0) ? (int)$countRow[0]['Num'] : 0;

            $query = "SELECT 
                ID_Registro,
                " . sqlClaveEntrada() . " AS ClaveEntrada,
                " . sqlClaveCobro() . " AS ClaveCobro,
                Folio_Tablet,
                Placas,
                Tipo,
                Descripcion,
                DATE_FORMAT(Entrada, '%d/%m/%Y %H:%i') AS Entrada,
                IF(Salida IS NULL, '<span class=\"badge bg-warning text-dark\">En patio</span>', DATE_FORMAT(Salida, '%d/%m/%Y %H:%i')) AS Salida,
                Horas,
                Total,
                Estatus
            FROM registros $where ORDER BY $orderField $orden LIMIT $limit OFFSET $offset";

            $rows = $omodelo->_consultar($query);
            $arreglo = array('data' => array(), 'totales' => array('NumRows' => $numRows));

            if ($rows != 'si' && $omodelo->numerofilas > 0) {
                $total = $omodelo->numerofilas;
                for ($i = 0; $i < $total; $i++) {
                    $r = $rows[$i];
                    $estatusTexto = trim($r['Estatus']);
                    $badgeEstatus = '<span class="badge bg-secondary">' . htmlspecialchars($estatusTexto) . '</span>';
                    if (strcasecmp($estatusTexto, 'Pendiente') === 0 || strcasecmp($estatusTexto, 'Activo') === 0) {
                        $badgeEstatus = '<span class="badge bg-warning text-dark fw-bold px-2 py-1">Pendiente</span>';
                    } else if (strcasecmp($estatusTexto, 'Completado') === 0 || strcasecmp($estatusTexto, 'Cobrado') === 0) {
                        $badgeEstatus = '<span class="badge bg-success fw-bold px-2 py-1">Completado</span>';
                    } else if (strcasecmp($estatusTexto, 'Cancelado') === 0) {
                        $badgeEstatus = '<span class="badge bg-danger fw-bold px-2 py-1">Cancelado</span>';
                    }

                    $tipoVehiculo = htmlspecialchars($r['Tipo']);
                    if ($r['Descripcion'] != '') {
                        $tipoVehiculo .= '<br><small class="text-muted">' . htmlspecialchars($r['Descripcion']) . '</small>';
                    }

                    $folioMostrar = !empty($r['Folio_Tablet']) ? htmlspecialchars($r['Folio_Tablet']) : str_pad($r['ID_Registro'], 5, '0', STR_PAD_LEFT);
                    $cobrado = in_array($estatusTexto, array('Completado', 'Cobrado'), true);
                    $dispositivoBadge = celdaDispositivoRegistro($omodelo, $r['ClaveEntrada'], $r['ClaveCobro'], $cobrado);

                    $arreglo['data'][$i] = array(
                        'ID' => $r['ID_Registro'],
                        'ID_Registro' => '<strong>#' . $folioMostrar . '</strong>',
                        'Dispositivo' => $dispositivoBadge,
                        'Placas' => '<span class="badge bg-light text-dark border font-monospace fs-6 px-2 py-1">' . htmlspecialchars($r['Placas']) . '</span>',
                        'Tipo' => $tipoVehiculo,
                        'Entrada' => $r['Entrada'],
                        'Salida' => $r['Salida'],
                        'Horas' => htmlspecialchars($r['Horas'] != '' ? $r['Horas'] : '-'),
                        'Total' => '<span class="dinero fw-bold text-dark">' . number_format($r['Total'], 2, '.', '') . '</span>',
                        'Estatus' => $badgeEstatus
                    );
                }
            }

            echo json_encode($arreglo);
            return;
        }

        // Si se solicitan KPIs y gráfica para el dashboard
        $arreglo = array();

        $vehiculosHoy = (int) $this->valor($omodelo, "SELECT COUNT(*) AS Num FROM registros WHERE DATE(Entrada) = CURDATE()", 'Num');
        $activosAhora = (int) $this->valor($omodelo, "SELECT COUNT(*) AS Num FROM registros WHERE Estatus = 'Pendiente' OR Estatus = 'Activo' OR Salida IS NULL", 'Num');
        $ingresosHoy = (float) $this->valor($omodelo, "SELECT IFNULL(SUM(Total), 0) AS Monto FROM registros WHERE DATE(Salida) = CURDATE() AND (Estatus = 'Completado' OR Estatus = 'Cobrado')", 'Monto');
        $cortesHoy = (int) $this->valor($omodelo, "SELECT COUNT(*) AS Num FROM detalles_caja WHERE DATE(Fecha_Cierre) = CURDATE()", 'Num');
        $totalHistorico = (float) $this->valor($omodelo, "SELECT IFNULL(SUM(Total), 0) AS Monto FROM registros WHERE Estatus = 'Completado' OR Estatus = 'Cobrado'", 'Monto');

        $arreglo['kpis'] = array(
            'vehiculosHoy' => $vehiculosHoy,
            'activosAhora' => $activosAhora,
            'ingresosHoy' => $ingresosHoy,
            'cortesHoy' => $cortesHoy,
            'totalHistorico' => $totalHistorico
        );

        // Serie diaria últimos 14 días para gráfica lineal
        $dias = array();
        $ingresos = array();
        $vehiculos = array();

        for ($i = 13; $i >= 0; $i--) {
            $diaFecha = date('Y-m-d', strtotime("-$i days"));
            $diaLabel = date('d/m', strtotime($diaFecha));

            $monto = (float) $this->valor($omodelo, "SELECT IFNULL(SUM(Total), 0) AS Monto FROM registros WHERE DATE(Salida) = '$diaFecha' AND Estatus IN ('Completado', 'Cobrado')", 'Monto');
            $vehs = (int) $this->valor($omodelo, "SELECT COUNT(*) AS Num FROM registros WHERE DATE(Entrada) = '$diaFecha'", 'Num');

            $dias[] = $diaLabel;
            $ingresos[] = $monto;
            $vehiculos[] = $vehs;
        }

        $arreglo['grafica'] = array(
            'dias' => $dias,
            'ingresos' => $ingresos,
            'vehiculos' => $vehiculos
        );

        echo json_encode($arreglo);
    }
}
?>
