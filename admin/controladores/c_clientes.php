<?php
class clientes
{
    public function _consultar()
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        $omodelo = new m_modelo('parking_subs');
        extract($_POST);

        $buscar  = $omodelo->escape($buscar ?? '');
        $limit   = max(1, (int)($limit ?? 10));
        $pagina  = max(1, (int)($pagina ?? 1));
        $offset  = ($pagina * $limit) - $limit;

        $busqueda = '';
        if (trim($buscar) !== '') {
            $busqueda = "AND (Nombre_Negocio LIKE '%$buscar%' OR Correo LIKE '%$buscar%' OR Codigo_Corto LIKE '%$buscar%' OR Plan LIKE '%$buscar%' OR BD LIKE '%$buscar%')";
        }

        $ordenColumna = $omodelo->escape($ordenColumna ?? 'Fecha');
        $orden = (isset($orden) && strtolower($orden) === 'asc') ? 'ASC' : 'DESC';

        $colMap = [
            'Fecha'    => 'Fecha_Alta',
            'Negocio'  => 'Nombre_Negocio',
            'Codigo'   => 'Codigo_Corto',
            'BD'       => 'BD',
            'Plan'     => 'Plan',
            'Estatus'  => 'Estatus',
            'Vence'    => 'Fecha_Vence',
            'ID'       => 'ID_Suscripcion'
        ];
        $campoOrden = $colMap[$ordenColumna] ?? 'ID_Suscripcion';

        $query = "SELECT ID_Suscripcion, Nombre_Negocio, Correo, Plan, Estatus, Codigo_Corto, Fecha_Alta, Fecha_Vence, Ilimitado, BD,
            (SELECT COUNT(*) FROM suscripciones WHERE 1=1 $busqueda) AS Num
        FROM suscripciones 
        WHERE 1=1 $busqueda 
        ORDER BY $campoOrden $orden 
        LIMIT $limit OFFSET $offset";

        $row = $omodelo->_consultar($query);
        $arreglo = ['data' => [], 'totales' => ['NumRows' => 0]];

        if (is_array($row) && count($row) > 0) {
            foreach ($row as $i => $r) {
                // Verificar si está vencido
                $esIlimitado = intval($r['Ilimitado']) === 1;
                $vencido = !$esIlimitado && (strtotime($r['Fecha_Vence']) < time());
                $estatusReal = $vencido ? 'Vencido' : $r['Estatus'];

                $badgeEstatus = '<span class="badge bg-secondary-subtle text-secondary border px-2 py-1">'.$estatusReal.'</span>';
                switch ($estatusReal) {
                    case 'Activo':
                        $badgeEstatus = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fa-solid fa-check-circle me-1"></i>Activo</span>';
                        break;
                    case 'Prueba':
                        $badgeEstatus = '<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1"><i class="fa-solid fa-gift me-1"></i>Prueba</span>';
                        break;
                    case 'Vencido':
                        $badgeEstatus = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fa-solid fa-exclamation-triangle me-1"></i>Vencido</span>';
                        break;
                }

                $badgePlan = '<span class="badge bg-light text-dark border px-2 py-1">'.$r['Plan'].'</span>';
                switch ($r['Plan']) {
                    case 'Ilimitado':
                        $badgePlan = '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 fw-bold"><i class="fa-solid fa-infinity me-1"></i>Ilimitado</span>';
                        break;
                    case 'Anual':
                        $badgePlan = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fw-bold">Anual ($2,500)</span>';
                        break;
                    case 'Mensual':
                        $badgePlan = '<span class="badge bg-secondary-subtle text-dark border px-2 py-1 fw-bold">Mensual ($250)</span>';
                        break;
                    case 'Prueba':
                        $badgePlan = '<span class="badge bg-light text-dark border px-2 py-1">Prueba Gratis</span>';
                        break;
                }

                $venceTexto = $esIlimitado ? '<span class="text-success fw-bold"><i class="fa-solid fa-infinity me-1"></i>Vitalicio</span>' : date('d/m/Y', strtotime($r['Fecha_Vence']));

                $arreglo['data'][$i] = [
                    'ID'       => $r['ID_Suscripcion'],
                    'Fecha'    => date('d/m/Y', strtotime($r['Fecha_Alta'])),
                    'Negocio'  => '<div class="fw-bold text-dark">'.htmlspecialchars($r['Nombre_Negocio']).'</div><small class="text-muted"><i class="fa-regular fa-envelope me-1"></i>'.htmlspecialchars($r['Correo']).'</small>',
                    'Codigo'   => '<span class="font-monospace fw-bold px-2 py-1 bg-light border rounded">'.$r['Codigo_Corto'].'</span>',
                    'BD'       => '<code>'.($r['BD'] ?: ('parking_'.$r['ID_Suscripcion'])).'</code>',
                    'Plan'     => $badgePlan,
                    'Estatus'  => $badgeEstatus,
                    'Vence'    => $venceTexto,
                    'Acciones' => '
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary bEditarPlan" attrID="'.$r['ID_Suscripcion'].'" attrNombre="'.htmlspecialchars($r['Nombre_Negocio']).'" attrPlan="'.$r['Plan'].'" title="Cambiar Plan">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger bEliminarCliente" attrID="'.$r['ID_Suscripcion'].'" attrNombre="'.htmlspecialchars($r['Nombre_Negocio']).'" title="Eliminar Suscripción">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>'
                ];
            }
            $arreglo['totales'] = ['NumRows' => (int)$row[0]['Num']];
        }

        echo json_encode($arreglo);
    }

    public function _modificar()
    {
        $omodelo = new m_modelo('parking_subs');
        extract($_POST);

        $id = (int)($id ?? 0);
        $plan = $omodelo->escape($plan ?? 'Mensual');
        $diasExtra = (int)($diasExtra ?? 30);

        if ($id <= 0) {
            echo "Error: ID de cliente inválido";
            return;
        }

        if ($plan === 'Ilimitado') {
            $query = "UPDATE suscripciones SET Plan = 'Ilimitado', Ilimitado = 1, Estatus = 'Activo', Fecha_Vence = '2099-12-31 23:59:59' WHERE ID_Suscripcion = $id";
        } else {
            $query = "UPDATE suscripciones SET Plan = '$plan', Ilimitado = 0, Estatus = 'Activo', Fecha_Vence = DATE_ADD(GREATEST(NOW(), Fecha_Vence), INTERVAL $diasExtra DAY) WHERE ID_Suscripcion = $id";
        }

        $error = $omodelo->_insertar($query);
        echo ($error === 'si') ? "Error: " . mysqli_error($omodelo->link) : "Correcto";
    }

    public function _eliminar()
    {
        $omodelo = new m_modelo('parking_subs');
        extract($_POST);

        $id = (int)($id ?? 0);
        if ($id <= 0) {
            echo "Error: ID inválido";
            return;
        }

        // 1. Obtener la base de datos asignada al cliente
        $sub = $omodelo->_consultar("SELECT BD FROM suscripciones WHERE ID_Suscripcion = $id LIMIT 1");
        $bdName = '';
        if (is_array($sub) && count($sub) > 0) {
            $bdName = trim($sub[0]['BD'] ?? '');
        }

        if (empty($bdName)) {
            $bdName = "parking_" . $id;
        }

        // Sanitizar nombre de base de datos
        $bdName = preg_replace('/[^a-zA-Z0-9_]/', '', $bdName);

        // Bases de datos protegidas del sistema que NUNCA deben eliminarse
        $dbProtegidas = ['parking_subs', 'mysql', 'information_schema', 'performance_schema', 'sys'];

        if (!empty($bdName) && !in_array(strtolower($bdName), $dbProtegidas)) {
            @$omodelo->link->query("DROP DATABASE IF EXISTS `$bdName`");
        }

        // 2. Eliminar pagos vinculados al cliente si existen
        $omodelo->_insertar("DELETE FROM pagos WHERE FK_Suscripcion = $id");

        // 3. Eliminar registro de suscripción
        $error = $omodelo->_insertar("DELETE FROM suscripciones WHERE ID_Suscripcion = $id");
        echo ($error === 'si') ? "Error: " . mysqli_error($omodelo->link) : "Correcto";
    }
}
?>
