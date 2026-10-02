<?php
/**
 * Identidad de tablets en el panel web.
 *
 * Cada tablet tiene un ID único generado por la app (Dispositivo_UID). El nombre
 * ("Tablet 1", "Entrada Norte") es solo informativo y puede repetirse entre tablets.
 *
 *   registros.Dispositivo_UID        tablet donde ENTRÓ el vehículo
 *   registros.Dispositivo_Cobro_UID  tablet que COBRÓ (dueña del dinero)
 *   registros.Dispositivo_Cobro      nombre de la tablet que cobró
 *   detalles_caja.Dispositivo_UID    tablet del corte
 *
 * Los datos de antes de esta versión no tienen ID: se identifican por nombre con el
 * prefijo "nombre:" (ej. "nombre:Tablet 1"), igual que antes.
 */

/** Nombre mostrado cuando un registro viejo no trae nombre de tablet. */
const TABLET_NOMBRE_DEFECTO = 'Tablet 1';

/**
 * Crea las columnas de identidad si aún no existen en la BD del estacionamiento.
 * subir.php también las crea en la primera sincronización; esto cubre el caso en
 * que se abra el panel antes de que alguna tablet actualizada sincronice.
 * Se revisa una sola vez por sesión y por base de datos.
 */
function asegurarColumnasTablets($omodelo)
{
    $bd = $_SESSION['user_parking_bd'] ?? '';
    if ($bd !== '' && !empty($_SESSION['__columnas_tablets_ok'][$bd])) {
        return;
    }

    $columnas = array(
        array('registros',     'Dispositivo_UID',       "VARCHAR(40) NULL AFTER Dispositivo"),
        array('registros',     'Dispositivo_Cobro',     "VARCHAR(60) NULL AFTER Dispositivo_UID"),
        array('registros',     'Dispositivo_Cobro_UID', "VARCHAR(40) NULL AFTER Dispositivo_Cobro"),
        array('detalles_caja', 'Dispositivo_UID',       "VARCHAR(40) NULL AFTER Dispositivo"),
    );

    try {
        foreach ($columnas as $c) {
            list($tabla, $col, $def) = $c;
            $existe = $omodelo->_consultar("SHOW COLUMNS FROM `$tabla` LIKE '$col'");
            if (!is_array($existe) || count($existe) === 0) {
                $omodelo->_insertar("ALTER TABLE `$tabla` ADD COLUMN `$col` $def");
            }
        }
        if ($bd !== '') {
            $_SESSION['__columnas_tablets_ok'][$bd] = true;
        }
    } catch (Throwable $e) {
        error_log('[identidad_tablets] No se pudieron crear las columnas: ' . $e->getMessage());
    }
}

/* ------------------------------------------------------------
 * Expresiones SQL de la "clave" de cada tablet
 * (ID si existe; si no, "nombre:<nombre>" para datos viejos)
 * ------------------------------------------------------------ */

/** Nombre de la tablet de entrada, con el valor por defecto para registros viejos. */
function sqlNombreEntrada($alias = '')
{
    $p = $alias !== '' ? "$alias." : '';
    return "IF(IFNULL({$p}Dispositivo, '') = '', '" . TABLET_NOMBRE_DEFECTO . "', {$p}Dispositivo)";
}

/** Clave de la tablet donde ENTRÓ el vehículo (tabla registros). */
function sqlClaveEntrada($alias = '')
{
    $p = $alias !== '' ? "$alias." : '';
    return "IFNULL(NULLIF({$p}Dispositivo_UID, ''), CONCAT('nombre:', " . sqlNombreEntrada($alias) . "))";
}

/** Nombre de la tablet que COBRÓ (si no se guardó, la misma de entrada). */
function sqlNombreCobro($alias = '')
{
    $p = $alias !== '' ? "$alias." : '';
    return "IFNULL(NULLIF({$p}Dispositivo_Cobro, ''), " . sqlNombreEntrada($alias) . ")";
}

/** Clave de la tablet que COBRÓ (tabla registros). Es la dueña del dinero. */
function sqlClaveCobro($alias = '')
{
    $p = $alias !== '' ? "$alias." : '';
    return "IFNULL(NULLIF({$p}Dispositivo_Cobro_UID, ''), IFNULL(NULLIF({$p}Dispositivo_UID, ''), CONCAT('nombre:', " . sqlNombreCobro($alias) . ")))";
}

/** Clave de la tablet de un corte (tabla detalles_caja). */
function sqlClaveCorte($alias = '')
{
    $p = $alias !== '' ? "$alias." : '';
    return "IFNULL(NULLIF({$p}Dispositivo_UID, ''), CONCAT('nombre:', " . sqlNombreEntrada($alias) . "))";
}

/**
 * Mapa clave => nombre a mostrar de todas las tablets del estacionamiento.
 * Usa el nombre más reciente de cada tablet. Si dos tablets distintas se llaman
 * igual, se les agrega el final de su ID para distinguirlas: "Tablet 1 (·a7b3)".
 */
function mapaNombresTablets($omodelo)
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $q = "SELECT Clave, Nombre, MAX(Fecha) AS Fecha FROM (
            SELECT " . sqlClaveEntrada() . " AS Clave, " . sqlNombreEntrada() . " AS Nombre, Entrada AS Fecha FROM registros
            UNION ALL
            SELECT " . sqlClaveCobro() . ", " . sqlNombreCobro() . ", Salida FROM registros WHERE Salida IS NOT NULL
            UNION ALL
            SELECT " . sqlClaveCorte() . ", " . sqlNombreEntrada() . ", Fecha_Apertura FROM detalles_caja
          ) t GROUP BY Clave, Nombre ORDER BY Fecha ASC";

    $res = $omodelo->_consultar($q);
    $nombres = array();
    if (is_array($res)) {
        foreach ($res as $r) {
            // Ordenado por fecha ascendente: el último nombre visto es el más reciente
            $nombres[$r['Clave']] = $r['Nombre'];
        }
    }

    // Distinguir tablets distintas que comparten nombre
    $conteo = array_count_values(array_map('mb_strtolower', $nombres));
    foreach ($nombres as $clave => $nombre) {
        if (($conteo[mb_strtolower($nombre)] ?? 0) > 1 && strpos($clave, 'nombre:') !== 0) {
            $nombres[$clave] = $nombre . ' (·' . substr($clave, -4) . ')';
        }
    }

    $cache = $nombres;
    return $cache;
}

/** Nombre a mostrar para una clave de tablet. */
function nombreTablet($omodelo, $clave, $respaldo = '')
{
    $mapa = mapaNombresTablets($omodelo);
    if (isset($mapa[$clave])) {
        return $mapa[$clave];
    }
    if (strpos((string)$clave, 'nombre:') === 0) {
        return substr($clave, 7);
    }
    return $respaldo !== '' ? $respaldo : TABLET_NOMBRE_DEFECTO;
}

/** Badge HTML de una tablet. */
function badgeTablet($nombre, $extraClase = '')
{
    return '<span class="badge bg-light text-dark border px-2 py-1 text-nowrap ' . $extraClase . '">'
        . '<i class="fa-solid fa-tablet-screen-button me-1 text-primary"></i>'
        . htmlspecialchars($nombre !== '' ? $nombre : TABLET_NOMBRE_DEFECTO) . '</span>';
}

/**
 * Celda "Dispositivo" de un registro: tablet de entrada y, si cobró otra, cuál.
 */
function celdaDispositivoRegistro($omodelo, $claveEntrada, $claveCobro, $cobrado)
{
    $html = badgeTablet(nombreTablet($omodelo, $claveEntrada));
    if ($cobrado && $claveCobro !== '' && $claveCobro !== $claveEntrada) {
        $html .= '<br><small class="text-muted text-nowrap"><i class="bi bi-cash-coin me-1"></i>Cobró: '
            . htmlspecialchars(nombreTablet($omodelo, $claveCobro)) . '</small>';
    }
    return $html;
}
?>
