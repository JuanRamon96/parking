<?php
/**
 * Reportes y cortes.
 *
 * Las tablets se identifican por su ID único (ver identidad_tablets.php):
 *  - Vehículos registrados: se cuentan en la tablet donde ENTRARON.
 *  - Cobros e ingresos:     se cuentan en la tablet que COBRÓ (la que tiene el dinero).
 *  - Cortes:                en la tablet que hizo el corte.
 */
class reportes
{
    // Estatus que cuentan como cobrado (la app usa 'Completado'; 'Cobrado' por compatibilidad)
    const COBRADO = "Estatus IN ('Completado', 'Cobrado')";

    private function valor($omodelo, $query, $campo)
    {
        $res = $omodelo->_consultar($query);
        if ($res != 'si' && $omodelo->numerofilas > 0) {
            return $res[0][$campo];
        }
        return 0;
    }

    private function filas($omodelo, $query)
    {
        $res = $omodelo->_consultar($query);
        return (is_array($res)) ? $res : array();
    }

    /** Clave de la tablet elegida en el filtro (vacío = todas). */
    private function tabletFiltro($omodelo)
    {
        $t = isset($_POST['dispositivo']) ? trim((string)$_POST['dispositivo']) : '';
        if ($t === '') {
            return '';
        }
        // Compatibilidad: si llega un nombre suelto (versión anterior del JS), se trata como dato viejo
        if (strpos($t, 'nombre:') !== 0 && !preg_match('/^[A-Za-z0-9_\-]{6,40}$/', $t)) {
            $t = 'nombre:' . $t;
        }
        return $omodelo->escape($t);
    }

    /** " AND <expresión> = '<clave>'" o vacío si no hay filtro. */
    private function filtro($omodelo, $expresion)
    {
        $t = $this->tabletFiltro($omodelo);
        return $t === '' ? '' : " AND $expresion = '$t'";
    }

    private function fechaValida($valor, $defecto)
    {
        $valor = trim((string)$valor);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) && strtotime($valor) !== false) {
            return $valor;
        }
        return $defecto;
    }

    public function _consultar()
    {
        $omodelo = new m_modelo();
        asegurarColumnasTablets($omodelo);
        extract($_POST);

        $tipo = isset($tipo) ? trim($tipo) : 'resumen';
        $fechaInicio = $this->fechaValida($fechaInicio ?? '', date('Y-m-01'));
        $fechaFin = $this->fechaValida($fechaFin ?? '', date('Y-m-d'));
        if ($fechaInicio > $fechaFin) {
            $tmp = $fechaInicio;
            $fechaInicio = $fechaFin;
            $fechaFin = $tmp;
        }

        if ($tipo == 'resumen') {
            $this->consultarResumen($omodelo, $fechaInicio, $fechaFin);
        } else if ($tipo == 'registros') {
            $this->consultarRegistros($omodelo, $fechaInicio, $fechaFin);
        } else if ($tipo == 'cortes') {
            $this->consultarCortes($omodelo, $fechaInicio, $fechaFin);
        } else if ($tipo == 'tablets') {
            $this->consultarTablets($omodelo);
        } else if ($tipo == 'detalle_corte') {
            $this->consultarDetalleCorte($omodelo);
        } else {
            echo json_encode(array('error' => 'Tipo de reporte inválido'));
        }
    }

    /* ============================================================
     * LISTA DE TABLETS (para el selector)
     * ============================================================ */
    private function consultarTablets($omodelo)
    {
        $tablets = array();
        foreach (mapaNombresTablets($omodelo) as $clave => $nombre) {
            $tablets[] = array('id' => $clave, 'nombre' => $nombre);
        }
        usort($tablets, function ($a, $b) {
            return strnatcasecmp($a['nombre'], $b['nombre']);
        });
        echo json_encode(array('tablets' => $tablets));
    }

    /* ============================================================
     * RESUMEN: KPIs, gráfica y totales por tablet
     * ============================================================ */
    private function consultarResumen($omodelo, $inicio, $fin)
    {
        $fEntrada = $this->filtro($omodelo, sqlClaveEntrada());
        $fCobro   = $this->filtro($omodelo, sqlClaveCobro());
        $fCorte   = $this->filtro($omodelo, sqlClaveCorte());
        $cob = self::COBRADO;

        $totalIngresos = (float) $this->valor($omodelo, "SELECT IFNULL(SUM(Total), 0) AS Monto FROM registros WHERE DATE(Salida) BETWEEN '$inicio' AND '$fin' AND $cob $fCobro", 'Monto');
        $totalVehiculos = (int) $this->valor($omodelo, "SELECT COUNT(*) AS Num FROM registros WHERE DATE(Entrada) BETWEEN '$inicio' AND '$fin' $fEntrada", 'Num');
        $totalCortes = (int) $this->valor($omodelo, "SELECT COUNT(*) AS Num FROM detalles_caja WHERE DATE(Fecha_Apertura) BETWEEN '$inicio' AND '$fin' $fCorte", 'Num');
        $activosAhora = (int) $this->valor($omodelo, "SELECT COUNT(*) AS Num FROM registros WHERE (Estatus = 'Pendiente' OR Estatus = 'Activo' OR Salida IS NULL) $fEntrada", 'Num');

        // Serie diaria: una consulta agrupada en lugar de una por día
        $mapIngresos = array();
        foreach ($this->filas($omodelo, "SELECT DATE(Salida) AS Dia, IFNULL(SUM(Total), 0) AS Monto FROM registros WHERE DATE(Salida) BETWEEN '$inicio' AND '$fin' AND $cob $fCobro GROUP BY DATE(Salida)") as $r) {
            $mapIngresos[$r['Dia']] = (float)$r['Monto'];
        }
        $mapVehiculos = array();
        foreach ($this->filas($omodelo, "SELECT DATE(Entrada) AS Dia, COUNT(*) AS Num FROM registros WHERE DATE(Entrada) BETWEEN '$inicio' AND '$fin' $fEntrada GROUP BY DATE(Entrada)") as $r) {
            $mapVehiculos[$r['Dia']] = (int)$r['Num'];
        }

        $fechas = array();
        $ingresos = array();
        $vehiculos = array();

        $start = new DateTime($inicio);
        $end = new DateTime($fin);
        $end->modify('+1 day');
        $period = new DatePeriod($start, new DateInterval('P1D'), $end);

        foreach ($period as $dt) {
            $f = $dt->format('Y-m-d');
            $fechas[] = $dt->format('d/m');
            $ingresos[] = isset($mapIngresos[$f]) ? $mapIngresos[$f] : 0;
            $vehiculos[] = isset($mapVehiculos[$f]) ? $mapVehiculos[$f] : 0;
        }

        echo json_encode(array(
            'kpis' => array(
                'totalIngresos' => $totalIngresos,
                'totalVehiculos' => $totalVehiculos,
                'totalCortes' => $totalCortes,
                'activosAhora' => $activosAhora
            ),
            'grafica' => array(
                'fechas' => $fechas,
                'ingresos' => $ingresos,
                'vehiculos' => $vehiculos
            ),
            'porTablet' => $this->resumenPorTablet($omodelo, $inicio, $fin)
        ));
    }

    /**
     * Totales del periodo agrupados por tablet (siempre muestra todas, para comparar).
     *  - vehiculos:  entradas registradas en esa tablet
     *  - cobrados / ingresos: salidas cobradas EN esa tablet (aunque el auto entrara por otra)
     *  - deOtras:    de esos cobros, cuántos entraron por otra tablet
     */
    private function resumenPorTablet($omodelo, $inicio, $fin)
    {
        $cE = sqlClaveEntrada();
        $cC = sqlClaveCobro();
        $cob = self::COBRADO;
        $tablets = array();

        $base = function ($clave) use ($omodelo) {
            return array(
                'id' => $clave,
                'dispositivo' => nombreTablet($omodelo, $clave),
                'vehiculos' => 0,
                'cobrados' => 0,
                'deOtras' => 0,
                'ingresos' => 0,
                'cortes' => 0,
                'diferencia' => 0
            );
        };

        foreach ($this->filas($omodelo, "SELECT $cE AS Clave, COUNT(*) AS Num FROM registros WHERE DATE(Entrada) BETWEEN '$inicio' AND '$fin' GROUP BY Clave") as $r) {
            if (!isset($tablets[$r['Clave']])) $tablets[$r['Clave']] = $base($r['Clave']);
            $tablets[$r['Clave']]['vehiculos'] = (int)$r['Num'];
        }

        foreach ($this->filas($omodelo, "SELECT $cC AS Clave, COUNT(*) AS Num, IFNULL(SUM(Total), 0) AS Monto,
                    SUM(IF($cC <> $cE, 1, 0)) AS DeOtras
                FROM registros WHERE DATE(Salida) BETWEEN '$inicio' AND '$fin' AND $cob GROUP BY Clave") as $r) {
            if (!isset($tablets[$r['Clave']])) $tablets[$r['Clave']] = $base($r['Clave']);
            $tablets[$r['Clave']]['cobrados'] = (int)$r['Num'];
            $tablets[$r['Clave']]['ingresos'] = (float)$r['Monto'];
            $tablets[$r['Clave']]['deOtras'] = (int)$r['DeOtras'];
        }

        foreach ($this->filas($omodelo, "SELECT " . sqlClaveCorte() . " AS Clave, COUNT(*) AS Num, IFNULL(SUM(Diferencia), 0) AS Dif FROM detalles_caja WHERE DATE(Fecha_Apertura) BETWEEN '$inicio' AND '$fin' GROUP BY Clave") as $r) {
            if (!isset($tablets[$r['Clave']])) $tablets[$r['Clave']] = $base($r['Clave']);
            $tablets[$r['Clave']]['cortes'] = (int)$r['Num'];
            $tablets[$r['Clave']]['diferencia'] = (float)$r['Dif'];
        }

        $lista = array_values($tablets);
        usort($lista, function ($a, $b) {
            return strnatcasecmp($a['dispositivo'], $b['dispositivo']);
        });
        return $lista;
    }

    /* ============================================================
     * TABLA DE REGISTROS
     * ============================================================ */
    private function consultarRegistros($omodelo, $inicio, $fin)
    {
        extract($_POST);

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

        $cE = sqlClaveEntrada();
        $cC = sqlClaveCobro();

        // Con filtro de tablet: los autos que entraron por ella o que ella cobró
        $where = "WHERE DATE(Entrada) BETWEEN '$inicio' AND '$fin'";
        $t = $this->tabletFiltro($omodelo);
        if ($t !== '') {
            $where .= " AND ($cE = '$t' OR (" . self::COBRADO . " AND $cC = '$t'))";
        }
        if (trim($buscar) != '') {
            $palabras = explode(' ', trim($buscar));
            for ($i = 0; $i < count($palabras); $i++) {
                $p = $omodelo->escape($palabras[$i]);
                $where .= " AND CONCAT_WS(' ', ID_Registro, IFNULL(Folio_Tablet, ''), IFNULL(Dispositivo, ''), IFNULL(Dispositivo_Cobro, ''), Placas, Tipo, Descripcion, Estatus, Total, DATE_FORMAT(Entrada, '%d/%m/%Y %H:%i'), IFNULL(DATE_FORMAT(Salida, '%d/%m/%Y %H:%i'), '')) REGEXP '$p'";
            }
        }

        $offset = ($pagina - 1) * $limit;

        $countRow = $omodelo->_consultar("SELECT COUNT(*) AS Num, IFNULL(SUM(Total), 0) AS SumaTotal FROM registros $where");
        $numRows = ($countRow != 'si' && $omodelo->numerofilas > 0) ? (int)$countRow[0]['Num'] : 0;
        $sumaTotal = ($countRow != 'si' && $omodelo->numerofilas > 0) ? (float)$countRow[0]['SumaTotal'] : 0;

        $query = "SELECT
            ID_Registro,
            $cE AS ClaveEntrada,
            $cC AS ClaveCobro,
            Folio_Tablet,
            FK_Detalle_Caja,
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
        $arreglo = array('data' => array(), 'totales' => array('NumRows' => $numRows, 'Total' => $sumaTotal));

        if ($rows != 'si' && $omodelo->numerofilas > 0) {
            $total = $omodelo->numerofilas;
            for ($i = 0; $i < $total; $i++) {
                $r = $rows[$i];
                $tipoVehiculo = htmlspecialchars($r['Tipo']);
                if ($r['Descripcion'] != '') {
                    $tipoVehiculo .= '<br><small class="text-muted">' . htmlspecialchars($r['Descripcion']) . '</small>';
                }

                $folioMostrar = !empty($r['Folio_Tablet']) ? htmlspecialchars($r['Folio_Tablet']) : str_pad($r['ID_Registro'], 5, '0', STR_PAD_LEFT);
                $cobrado = in_array(trim($r['Estatus']), array('Completado', 'Cobrado'), true);

                $arreglo['data'][$i] = array(
                    'ID' => $r['ID_Registro'],
                    'ID_Registro' => '<strong>#' . $folioMostrar . '</strong>',
                    'Dispositivo' => celdaDispositivoRegistro($omodelo, $r['ClaveEntrada'], $r['ClaveCobro'], $cobrado),
                    'Placas' => '<span class="badge bg-light text-dark border font-monospace fs-6 px-2 py-1">' . htmlspecialchars($r['Placas']) . '</span>',
                    'Tipo' => $tipoVehiculo,
                    'Entrada' => $r['Entrada'],
                    'Salida' => $r['Salida'],
                    'Horas' => htmlspecialchars($r['Horas'] != '' ? $r['Horas'] : '-'),
                    'Total' => '<span class="dinero fw-bold text-navy">' . number_format($r['Total'], 2, '.', '') . '</span>',
                    'Estatus' => $this->badgeEstatus($r['Estatus'])
                );
            }
        }

        echo json_encode($arreglo);
    }

    /* ============================================================
     * TABLA DE CORTES
     * ============================================================ */
    private function consultarCortes($omodelo, $inicio, $fin)
    {
        extract($_POST);

        $buscar = isset($buscar) ? $omodelo->escape($buscar) : '';
        $limit = isset($limit) && (int)$limit > 0 ? (int)$limit : 25;
        $pagina = isset($pagina) && (int)$pagina > 0 ? (int)$pagina : 1;
        $ordenColumna = isset($ordenColumna) && trim($ordenColumna) != '' ? $omodelo->escape($ordenColumna) : 'Fecha_Apertura';
        $orden = isset($orden) && strtolower($orden) == 'asc' ? 'ASC' : 'DESC';

        $columnasValidas = array(
            'ID_Detalle_Caja' => 'ID_Detalle_Caja',
            'Caja' => 'detalles_caja.Dispositivo',
            'Fecha_Apertura' => 'Fecha_Apertura',
            'Fecha_Cierre' => 'Fecha_Cierre',
            'Monto_Apertura' => 'Monto_Apertura',
            'Ingresos' => 'Ingresos',
            'Monto_Cierre' => 'Monto_Cierre',
            'Diferencia' => 'Diferencia'
        );
        $orderField = isset($columnasValidas[$ordenColumna]) ? $columnasValidas[$ordenColumna] : 'ID_Detalle_Caja';

        $cCorte = sqlClaveCorte('detalles_caja');
        $where = "WHERE DATE(detalles_caja.Fecha_Apertura) BETWEEN '$inicio' AND '$fin'" . $this->filtro($omodelo, $cCorte);
        if (trim($buscar) != '') {
            $palabras = explode(' ', trim($buscar));
            for ($i = 0; $i < count($palabras); $i++) {
                $p = $omodelo->escape($palabras[$i]);
                $where .= " AND CONCAT_WS(' ', ID_Detalle_Caja, IFNULL(detalles_caja.Dispositivo, ''), IFNULL(cajas.Nombre, ''), DATE_FORMAT(Fecha_Apertura, '%d/%m/%Y %H:%i'), IFNULL(DATE_FORMAT(Fecha_Cierre, '%d/%m/%Y %H:%i'), '')) REGEXP '$p'";
            }
        }

        $offset = ($pagina - 1) * $limit;

        $countRow = $omodelo->_consultar("SELECT COUNT(*) AS Num FROM detalles_caja LEFT JOIN cajas ON FK_Caja = ID_Caja $where");
        $numRows = ($countRow != 'si' && $omodelo->numerofilas > 0) ? (int)$countRow[0]['Num'] : 0;

        $query = "SELECT
            ID_Detalle_Caja,
            $cCorte AS ClaveCorte,
            detalles_caja.Dispositivo,
            IFNULL(cajas.Nombre, 'Caja') AS Caja,
            DATE_FORMAT(Fecha_Apertura, '%d/%m/%Y %H:%i') AS AperturaFmt,
            IF(IFNULL(YEAR(Fecha_Cierre), 0) = 0, '<span class=\"badge bg-success\">Abierta</span>', DATE_FORMAT(Fecha_Cierre, '%d/%m/%Y %H:%i')) AS CierreFmt,
            Monto_Apertura,
            Ingresos,
            Monto_Cierre,
            Diferencia
        FROM detalles_caja
        LEFT JOIN cajas ON FK_Caja = ID_Caja
        $where ORDER BY $orderField $orden LIMIT $limit OFFSET $offset";

        $rows = $omodelo->_consultar($query);
        $arreglo = array('data' => array(), 'totales' => array('NumRows' => $numRows));

        if ($rows != 'si' && $omodelo->numerofilas > 0) {
            $total = $omodelo->numerofilas;
            for ($i = 0; $i < $total; $i++) {
                $r = $rows[$i];
                $nombre = nombreTablet($omodelo, $r['ClaveCorte'], !empty($r['Dispositivo']) ? $r['Dispositivo'] : $r['Caja']);

                $arreglo['data'][$i] = array(
                    'ID' => $r['ID_Detalle_Caja'],
                    'ID_Detalle_Caja' => '<strong>Corte #' . $r['ID_Detalle_Caja'] . '</strong>',
                    'Caja' => badgeTablet($nombre),
                    'Fecha_Apertura' => $r['AperturaFmt'],
                    'Fecha_Cierre' => $r['CierreFmt'],
                    'Monto_Apertura' => '<span class="dinero">' . number_format($r['Monto_Apertura'], 2, '.', '') . '</span>',
                    'Ingresos' => '<span class="dinero fw-bold text-success">' . number_format($r['Ingresos'], 2, '.', '') . '</span>',
                    'Monto_Cierre' => '<span class="dinero">' . number_format($r['Monto_Cierre'], 2, '.', '') . '</span>',
                    'Diferencia' => $this->badgeDiferencia($r['Diferencia']),
                    'Acciones' => '<button type="button" class="btn btn-sm btn-outline-success bVerCorte text-nowrap" attrid="' . (int)$r['ID_Detalle_Caja'] . '"><i class="bi bi-eye me-1"></i>Ver</button>'
                );
            }
        }

        echo json_encode($arreglo);
    }

    /* ============================================================
     * DETALLE DE UN CORTE
     * ============================================================ */
    private function consultarDetalleCorte($omodelo)
    {
        $id = isset($_POST['idCorte']) ? (int)$_POST['idCorte'] : 0;
        $cob = self::COBRADO;
        $cE = sqlClaveEntrada();
        $cC = sqlClaveCobro();

        $corteRows = $this->filas($omodelo, "SELECT
                ID_Detalle_Caja,
                " . sqlClaveCorte() . " AS ClaveCorte,
                " . sqlNombreEntrada() . " AS Dispositivo,
                Fecha_Apertura,
                IF(IFNULL(YEAR(Fecha_Cierre), 0) = 0, NULL, Fecha_Cierre) AS Fecha_Cierre,
                Monto_Apertura, Ingresos, Balance, Monto_Cierre, Diferencia
            FROM detalles_caja WHERE ID_Detalle_Caja = $id LIMIT 1");

        if (count($corteRows) === 0) {
            echo json_encode(array('status' => 'error', 'message' => 'No se encontró el corte.'));
            return;
        }
        $c = $corteRows[0];
        $clave = $omodelo->escape($c['ClaveCorte']);

        $campos = "Folio_Tablet, ID_Registro, Placas, Tipo, Descripcion,
                   $cE AS ClaveEntrada, $cC AS ClaveCobro,
                   DATE_FORMAT(Entrada, '%d/%m/%Y %H:%i') AS Entrada,
                   IFNULL(DATE_FORMAT(Salida, '%d/%m/%Y %H:%i'), '') AS Salida,
                   Horas, Total, Estatus";

        // 1. Registros enlazados al corte. Solo los que son de esta tablet:
        //    cobros hechos por ella, o pendientes que entraron por ella.
        $registros = $this->filas($omodelo, "SELECT $campos FROM registros
            WHERE FK_Detalle_Caja = $id
              AND (($cob AND $cC = '$clave') OR (NOT ($cob) AND $cE = '$clave'))
            ORDER BY Entrada ASC");
        $metodo = 'enlazados';

        // 2. Si no hay enlazados (datos antiguos), usar los cobros de esa tablet durante el turno
        if (count($registros) === 0) {
            $apertura = $omodelo->escape($c['Fecha_Apertura']);
            $cierre = $c['Fecha_Cierre'] ? "'" . $omodelo->escape($c['Fecha_Cierre']) . "'" : 'NOW()';

            $registros = $this->filas($omodelo, "SELECT $campos FROM registros
                WHERE $cC = '$clave' AND $cob AND Salida BETWEEN '$apertura' AND $cierre
                ORDER BY Salida ASC");
            $metodo = 'turno';
        }

        $sumaCobrados = 0;
        $numCobrados = 0;
        $numPendientes = 0;
        $numDeOtras = 0;
        $lista = array();

        foreach ($registros as $r) {
            $esCobrado = in_array(trim($r['Estatus']), array('Completado', 'Cobrado'), true);
            $entroEnOtra = $esCobrado && $r['ClaveEntrada'] !== $r['ClaveCobro'];
            if ($esCobrado) {
                $sumaCobrados += (float)$r['Total'];
                $numCobrados++;
                if ($entroEnOtra) $numDeOtras++;
            } else {
                $numPendientes++;
            }

            $lista[] = array(
                'folio' => !empty($r['Folio_Tablet']) ? $r['Folio_Tablet'] : str_pad($r['ID_Registro'], 5, '0', STR_PAD_LEFT),
                'placas' => $r['Placas'],
                'tipo' => $r['Tipo'],
                'descripcion' => $r['Descripcion'],
                'entrada' => $r['Entrada'],
                'salida' => $r['Salida'],
                'horas' => $r['Horas'],
                'total' => (float)$r['Total'],
                'cobrado' => $esCobrado,
                // Si el auto entró por otra tablet, su nombre (se muestra en el detalle)
                'origen' => $entroEnOtra ? nombreTablet($omodelo, $r['ClaveEntrada']) : ''
            );
        }

        $ingresosReportados = (float)$c['Ingresos'];

        echo json_encode(array(
            'status' => 'success',
            'corte' => array(
                'id' => (int)$c['ID_Detalle_Caja'],
                'dispositivo' => nombreTablet($omodelo, $c['ClaveCorte'], $c['Dispositivo']),
                'apertura' => date('d/m/Y H:i', strtotime($c['Fecha_Apertura'])),
                'cierre' => $c['Fecha_Cierre'] ? date('d/m/Y H:i', strtotime($c['Fecha_Cierre'])) : null,
                'montoApertura' => (float)$c['Monto_Apertura'],
                'ingresos' => $ingresosReportados,
                'balance' => (float)$c['Balance'],
                'montoCierre' => (float)$c['Monto_Cierre'],
                'diferencia' => (float)$c['Diferencia']
            ),
            'registros' => $lista,
            'metodo' => $metodo,
            'numCobrados' => $numCobrados,
            'numPendientes' => $numPendientes,
            'numDeOtras' => $numDeOtras,
            'sumaCobrados' => round($sumaCobrados, 2),
            'diferenciaRegistros' => round($ingresosReportados - $sumaCobrados, 2)
        ));
    }

    /* ============================================================
     * UTILIDADES DE PRESENTACIÓN
     * ============================================================ */
    private function badgeEstatus($estatus)
    {
        $e = trim((string)$estatus);
        if (strcasecmp($e, 'Pendiente') === 0 || strcasecmp($e, 'Activo') === 0) {
            return '<span class="badge bg-warning text-dark fw-bold px-2 py-1">Pendiente</span>';
        }
        if (strcasecmp($e, 'Completado') === 0 || strcasecmp($e, 'Cobrado') === 0) {
            return '<span class="badge bg-success fw-bold px-2 py-1">Completado</span>';
        }
        if (strcasecmp($e, 'Cancelado') === 0) {
            return '<span class="badge bg-danger fw-bold px-2 py-1">Cancelado</span>';
        }
        return '<span class="badge bg-secondary">' . htmlspecialchars($e) . '</span>';
    }

    private function badgeDiferencia($dif)
    {
        $dif = (float)$dif;
        if ($dif > 0) {
            return '<span class="badge bg-success">+$' . number_format($dif, 2) . '</span>';
        }
        if ($dif < 0) {
            return '<span class="badge bg-danger">-$' . number_format(abs($dif), 2) . '</span>';
        }
        return '<span class="badge bg-secondary">$0.00</span>';
    }
}
?>
