<?php
/**
 * ParkingPro - Endpoint de la app de tablets
 *
 * Métodos soportados (campo "metodo" del JSON):
 *   - vincular_tablet : vincula una tablet por código corto (PK-1001) o token.
 *   - detectar        : analiza una foto (placa, color, tipo, marca). No usa la BD del tenant.
 *   - ping            : prueba de conexión (con o sin token).
 *   - vincular        : sube registros de vehículos y cortes de caja.
 *
 * Bases de datos:
 *   - parking_subs      : suscripciones (maestra).
 *   - parking_<ID> / BD : base de cada estacionamiento (tenant).
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Nunca imprimir warnings/notices en la salida: romperían el JSON. Se mandan al log.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

/* ============================================================
 * CONFIGURACIÓN
 * ============================================================ */

const BD_MAESTRA = 'parking_subs';

// Si se pone en true, "detectar" exige el token de una tablet vinculada y vigente.
// Déjalo en false hasta que detector.js mande el token.
const DETECTAR_REQUIERE_TOKEN = false;

const IMG_MAX_LADO        = 1600;      // px: la foto se reduce a este lado mayor antes del OCR
const OCR_MAX_BYTES       = 1000000;   // OCR.space gratuito rechaza imágenes de más de ~1 MB
const FOTO_MAX_BYTES      = 15000000;  // tope de la foto recibida (decodificada)
const DETECCION_MAX_SEG   = 15;        // la app corta a los 18 s; respondemos antes
const GEMINI_MODELO       = 'gemini-2.5-flash';

// Claves de OCR.space: primero se leen de la variable de entorno OCR_SPACE_KEYS
// (separadas por coma). Si no existe, se usan estas.
const OCR_SPACE_KEYS_DEFAULT = [
    'K82361738788957',
    'K88574136988957',
    'K81682337788957',
    'helloworld'
];

/* ============================================================
 * FLUJO PRINCIPAL
 * ============================================================ */

try {
    $data   = leerEntrada();
    $metodo = trim((string)($data['metodo'] ?? $_GET['metodo'] ?? 'vincular'));
    $token  = obtenerToken($data);

    // 1. Detección por foto: va primero porque NO necesita la BD del tenant.
    if ($metodo === 'detectar') {
        manejarDetectar($data, $token);
    }

    $model = obtenerModelo();

    // 2. Vinculación de tablet (no requiere token previo)
    if ($metodo === 'vincular_tablet') {
        manejarVincularTablet($model, $data, $token);
    }

    // 3. Sin token: solo se permite el ping para probar la dirección del servidor
    if ($token === '') {
        if ($metodo === 'ping') {
            responder([
                'status'    => 'success',
                'message'   => 'Servidor accesible. Esta tablet aún no está vinculada.',
                'vinculada' => false,
                'fecha'     => date('Y-m-d H:i:s')
            ]);
        }
        responderError('Esta tablet no está vinculada. Vincúlala en Ajustes con el código de tu panel web.', 'INVALID_TOKEN');
    }

    // 4. Validar suscripción y cambiar a la BD del estacionamiento
    $sub = obtenerSuscripcionPorToken($model, $token);
    if (!$sub) {
        responderError('Dispositivo no vinculado o clave inválida. Vuelve a vincular esta tablet en Ajustes.', 'INVALID_TOKEN');
    }

    if (suscripcionVencida($sub)) {
        $model->_insertar("UPDATE suscripciones SET Estatus = 'Vencido' WHERE ID_Suscripcion = " . intval($sub['ID_Suscripcion']));
        responderError(
            '⚠️ Tu periodo de suscripción ha vencido. Por favor renueva tu plan en el panel web para continuar subiendo registros y realizando cortes.',
            'SUBSCRIPTION_EXPIRED'
        );
    }

    $bdTenant = nombreBdTenant($sub);
    if (!cambiarABd($model, $bdTenant)) {
        responderError('La base de datos de este estacionamiento no está disponible. Contacta a soporte.', 'DB_UNAVAILABLE');
    }

    // 5. Métodos que operan sobre la BD del tenant
    switch ($metodo) {
        case 'ping':
            responder([
                'status'    => 'success',
                'message'   => 'Conexión exitosa con el servidor de estacionamiento',
                'vinculada' => true,
                'negocio'   => $sub['Nombre_Negocio'] ?? '',
                'fecha'     => date('Y-m-d H:i:s')
            ]);
            break;

        case 'vincular':
            manejarSincronizacion($model, $data);
            break;

        default:
            responderError('Método no soportado.', 'UNSUPPORTED_METHOD');
    }
} catch (Throwable $e) {
    error_log('[subir.php] ' . get_class($e) . ': ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
    responderError('Ocurrió un error en el servidor. Intenta de nuevo en unos momentos.', 'SERVER_ERROR');
}

/* ============================================================
 * UTILIDADES GENERALES
 * ============================================================ */

function responder(array $payload, int $http = 200): void
{
    http_response_code($http);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit();
}

function responderError(string $mensaje, string $codigo = '', int $http = 200): void
{
    $payload = ['status' => 'error', 'message' => $mensaje];
    if ($codigo !== '') {
        $payload['code'] = $codigo;
    }
    responder($payload, $http);
}

function leerEntrada(): array
{
    $raw  = file_get_contents('php://input');
    $data = ($raw !== false && $raw !== '') ? json_decode($raw, true) : null;

    if (!is_array($data)) {
        $data = !empty($_POST) ? $_POST : [];
    }
    return $data;
}

function obtenerToken(array $data): string
{
    $token = trim((string)($data['token'] ?? $_GET['token'] ?? ''));

    if ($token === '') {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(\S+)/i', $auth, $m)) {
            $token = $m[1];
        }
    }
    return $token;
}

function obtenerModelo()
{
    static $model = null;
    if ($model === null) {
        include_once __DIR__ . '/modelo/m_modelo.php';
        $model = new m_modelo(BD_MAESTRA);
    }
    return $model;
}

/* ============================================================
 * SUSCRIPCIONES Y BASES DE DATOS
 * ============================================================ */

function obtenerSuscripcionPorToken($model, string $token): ?array
{
    $t = $model->escape($token);
    $rows = $model->_consultar(
        "SELECT ID_Suscripcion, Nombre_Negocio, Token_Enlace, Codigo_Corto, Plan, Estatus, Fecha_Vence, Ilimitado, BD
         FROM suscripciones
         WHERE Token_Enlace = '$t'
         LIMIT 1"
    );
    return (is_array($rows) && count($rows) > 0) ? $rows[0] : null;
}

function obtenerSuscripcionPorCodigo($model, string $codigo): ?array
{
    $c = $model->escape($codigo);
    $rows = $model->_consultar(
        "SELECT ID_Suscripcion, Nombre_Negocio, Token_Enlace, Codigo_Corto, Plan, Estatus, Fecha_Vence, Ilimitado, BD
         FROM suscripciones
         WHERE Codigo_Corto = '$c' OR Token_Enlace = '$c'
         LIMIT 1"
    );
    return (is_array($rows) && count($rows) > 0) ? $rows[0] : null;
}

function suscripcionVencida(array $sub): bool
{
    if (intval($sub['Ilimitado'] ?? 0) === 1) {
        return false;
    }

    $vence = trim((string)($sub['Fecha_Vence'] ?? ''));
    if ($vence === '' || strpos($vence, '0000-00-00') === 0) {
        return true;
    }

    // Si solo trae fecha (Y-m-d), la suscripción vale hasta el final de ese día
    if (strlen($vence) === 10) {
        $vence .= ' 23:59:59';
    }

    $ts = strtotime($vence);
    return $ts === false || $ts < time();
}

/**
 * Nombre de la BD del estacionamiento: columna BD o parking_<ID_Suscripcion>.
 * Se valida el formato para evitar inyección en el USE y que nunca apunte a la maestra.
 */
function nombreBdTenant(array $sub): string
{
    $bd = trim((string)($sub['BD'] ?? ''));
    if ($bd === '') {
        $bd = 'parking_' . intval($sub['ID_Suscripcion']);
    }

    if (!preg_match('/^parking_[A-Za-z0-9_]{1,56}$/', $bd) || $bd === BD_MAESTRA) {
        throw new RuntimeException("Nombre de BD de tenant inválido: $bd");
    }
    return $bd;
}

/**
 * Cambia la conexión a la BD indicada y confirma que el cambio se hizo.
 */
function cambiarABd($model, string $bd): bool
{
    try {
        $model->_insertar("USE `$bd`");
        $r = $model->_consultar("SELECT DATABASE() AS bd");
        return is_array($r) && isset($r[0]['bd']) && $r[0]['bd'] === $bd;
    } catch (Throwable $e) {
        error_log("[subir.php] No se pudo usar la BD '$bd': " . $e->getMessage());
        return false;
    }
}

/* ============================================================
 * MÉTODO: vincular_tablet
 * ============================================================ */

function manejarVincularTablet($model, array $data, string $token): void
{
    $codigo = strtoupper(trim((string)($data['codigo'] ?? $_GET['codigo'] ?? '')));

    $sub = null;
    if ($codigo !== '') {
        $sub = obtenerSuscripcionPorCodigo($model, $codigo);
    } elseif ($token !== '') {
        $sub = obtenerSuscripcionPorToken($model, $token);
    } else {
        responderError('Por favor ingresa un código de vinculación válido (ej: PK-1001).');
    }

    if (!$sub) {
        responderError('Código de vinculación inválido o no encontrado. Revisa en tu panel web en "Tablets y Dispositivos".');
    }

    if (suscripcionVencida($sub)) {
        responderError(
            'La suscripción de este estacionamiento ha vencido. Renueva en el panel web para vincular tablets.',
            'SUBSCRIPTION_EXPIRED'
        );
    }

    $bdTenant = nombreBdTenant($sub);
    if (!cambiarABd($model, $bdTenant)) {
        responderError('La base de datos de este estacionamiento no está disponible. Contacta a soporte.', 'DB_UNAVAILABLE');
    }

    // Valores por defecto
    $tarifas       = ['media' => 10.0, 'precio' => 20.0, 'extra' => 10.0];
    $nombreNegocio = !empty($sub['Nombre_Negocio']) ? $sub['Nombre_Negocio'] : 'Estacionamiento';
    $domicilio     = '';
    $telefono      = '';
    $leyenda       = '* No nos hacemos responsables de robo parcial o total, ni daños ocasionados por terceros *';

    try {
        $cfgRow = $model->_consultar("SELECT * FROM configuracion ORDER BY ID_Configuracion DESC LIMIT 1");
        if (is_array($cfgRow) && count($cfgRow) > 0) {
            $cfg = $cfgRow[0];
            if (isset($cfg['Media']))  $tarifas['media']  = floatval($cfg['Media']);
            if (isset($cfg['Precio'])) $tarifas['precio'] = floatval($cfg['Precio']);
            if (isset($cfg['Extra']))  $tarifas['extra']  = floatval($cfg['Extra']);
            if (!empty($cfg['Nombre']))    $nombreNegocio = $cfg['Nombre'];
            if (!empty($cfg['Domicilio'])) $domicilio     = $cfg['Domicilio'];
            if (!empty($cfg['Telefono']))  $telefono      = $cfg['Telefono'];
            if (!empty($cfg['Leyenda']))   $leyenda       = $cfg['Leyenda'];
        }
    } catch (Throwable $e) {
        // Si la tabla configuracion no existe aún, se vincula con los valores por defecto
        error_log('[subir.php] No se pudo leer configuracion de ' . $bdTenant . ': ' . $e->getMessage());
    }

    responder([
        'status'        => 'success',
        'message'       => '¡Tablet vinculada correctamente con ' . $nombreNegocio . '!',
        'token'         => $sub['Token_Enlace'],
        'codigo'        => $sub['Codigo_Corto'],
        'nombreNegocio' => $nombreNegocio,
        'domicilio'     => $domicilio,
        'telefono'      => $telefono,
        'leyenda'       => $leyenda,
        'plan'          => $sub['Plan'],
        'tarifas'       => $tarifas
    ]);
}

/* ============================================================
 * MÉTODO: vincular (subir registros y cortes)
 * ============================================================ */

function manejarSincronizacion($model, array $data): void
{
    $registros = is_array($data['registros'] ?? null) ? $data['registros'] : [];
    $cortes    = is_array($data['cortes'] ?? null) ? $data['cortes'] : [];

    $registrosGuardados   = 0;
    $cortesGuardados      = 0;
    $cortesParaEnlazar    = [];
    $cobradosEnOtraTablet = [];   // tickets que la tablet tiene "adentro" pero ya se cobraron
    $cobrosDuplicados     = [];   // tickets cobrados en dos tablets distintas

    // Columnas de identidad de tablets. Se crean solas en cada BD de estacionamiento.
    // Va antes de la transacción porque ALTER TABLE hace commit implícito.
    asegurarColumnasIdentidad($model);

    $model->_insertar("START TRANSACTION");

    try {
        // ---------- CORTES DE CAJA (detalles_caja) ----------
        foreach ($cortes as $c) {
            if (!is_array($c)) continue;

            $dispositivo  = $model->escape(valor($c, ['dispositivo', 'Dispositivo'], 'Tablet 1'));
            $uidCorte     = limpiarUid(valor($c, ['dispositivo_uid', 'Dispositivo_UID'], ''));
            $uidCorteSql  = $uidCorte !== '' ? "'" . $model->escape($uidCorte) . "'" : 'NULL';
            $fechaApert   = $model->escape(fechaValida(valor($c, ['fecha_apertura', 'Fecha_Apertura'])) ?? date('Y-m-d H:i:s'));
            $fechaCierreV = fechaValida(valor($c, ['fecha_cierre', 'Fecha_Cierre']));
            $fechaCierre  = $model->escape($fechaCierreV ?? '0000-00-00 00:00:00');
            $montoApert   = floatval(valor($c, ['monto_apertura', 'Monto_Apertura'], 0));
            $montoCierre  = floatval(valor($c, ['monto_cierre', 'Monto_Cierre'], 0));
            $ingresos     = floatval(valor($c, ['ingresos', 'Ingresos'], 0));
            $balance      = floatval(valor($c, ['balance', 'Balance'], 0));
            $diferencia   = floatval(valor($c, ['diferencia', 'Diferencia'], 0));

            // El turno se identifica por el ID de la tablet (o por el nombre en apps viejas)
            $filtroTablet = $uidCorte !== ''
                ? "Dispositivo_UID = $uidCorteSql"
                : "Dispositivo = '$dispositivo' AND Dispositivo_UID IS NULL";

            $check = $model->_consultar(
                "SELECT ID_Detalle_Caja FROM detalles_caja
                 WHERE $filtroTablet AND Fecha_Apertura = '$fechaApert'
                 LIMIT 1"
            );

            // Transición: un corte que se subió antes con el subir.php viejo no tiene ID.
            // Si coincide por nombre y apertura, se le asigna el ID en lugar de duplicarlo.
            if ($uidCorte !== '' && (!is_array($check) || count($check) === 0)) {
                $check = $model->_consultar(
                    "SELECT ID_Detalle_Caja FROM detalles_caja
                     WHERE Dispositivo = '$dispositivo' AND Dispositivo_UID IS NULL
                       AND Fecha_Apertura = '$fechaApert'
                     LIMIT 1"
                );
                if (is_array($check) && count($check) > 0) {
                    $model->_insertar("UPDATE detalles_caja SET Dispositivo_UID = $uidCorteSql WHERE ID_Detalle_Caja = " . intval($check[0]['ID_Detalle_Caja']));
                }
            }

            if (is_array($check) && count($check) > 0) {
                $idDetalle = intval($check[0]['ID_Detalle_Caja']);
                $model->_insertar(
                    "UPDATE detalles_caja SET
                        Fecha_Cierre = '$fechaCierre',
                        Monto_Cierre = $montoCierre,
                        Ingresos     = $ingresos,
                        Balance      = $balance,
                        Diferencia   = $diferencia
                     WHERE ID_Detalle_Caja = $idDetalle"
                );
            } else {
                $model->_insertar(
                    "INSERT INTO detalles_caja
                        (FK_Caja, Dispositivo, Dispositivo_UID, Fecha_Apertura, Monto_Apertura, Fecha_Cierre, Monto_Cierre, Ingresos, Balance, Diferencia)
                     VALUES
                        (1, '$dispositivo', $uidCorteSql, '$fechaApert', $montoApert, '$fechaCierre', $montoCierre, $ingresos, $balance, $diferencia)"
                );
                $nuevo = $model->_consultar(
                    "SELECT ID_Detalle_Caja FROM detalles_caja
                     WHERE $filtroTablet AND Fecha_Apertura = '$fechaApert'
                     ORDER BY ID_Detalle_Caja DESC LIMIT 1"
                );
                $idDetalle = (is_array($nuevo) && count($nuevo) > 0) ? intval($nuevo[0]['ID_Detalle_Caja']) : 0;
            }

            if ($idDetalle > 0 && $fechaCierreV !== null) {
                $cortesParaEnlazar[] = [
                    'id'          => $idDetalle,
                    'uid'         => $uidCorte !== '' ? $model->escape($uidCorte) : '',
                    'dispositivo' => $dispositivo,
                    'apertura'    => $fechaApert,
                    'cierre'      => $model->escape($fechaCierreV)
                ];
            }
            $cortesGuardados++;
        }

        // Corte más reciente como respaldo para FK_Detalle_Caja
        $ultimoCorte = $model->_consultar("SELECT ID_Detalle_Caja FROM detalles_caja ORDER BY ID_Detalle_Caja DESC LIMIT 1");
        $fkDetalleActivo = (is_array($ultimoCorte) && count($ultimoCorte) > 0) ? intval($ultimoCorte[0]['ID_Detalle_Caja']) : 1;

        // ---------- REGISTROS DE VEHÍCULOS ----------
        foreach ($registros as $r) {
            if (!is_array($r)) continue;

            $folioRaw = trim((string)valor($r, ['folio', 'ID_Registro'], ''));
            if ($folioRaw === '') continue;

            $dispositivoRaw = trim((string)valor($r, ['dispositivo', 'Dispositivo'], 'Tablet 1'));
            $dispositivo    = $model->escape($dispositivoRaw);
            $folio          = $model->escape($folioRaw);
            $uidRaw         = limpiarUid(valor($r, ['dispositivo_uid', 'Dispositivo_UID'], ''));
            $uidSql         = $uidRaw !== '' ? "'" . $model->escape($uidRaw) . "'" : 'NULL';

            $fkDetalle = intval(valor($r, ['fk_detalle_caja', 'FK_Detalle_Caja'], $fkDetalleActivo));
            if ($fkDetalle <= 0) $fkDetalle = $fkDetalleActivo;

            $media  = floatval(valor($r, ['media', 'Media'], 10));
            $precio = floatval(valor($r, ['precio', 'Precio'], 20));
            $extra  = floatval(valor($r, ['extra', 'Extra'], 10));
            $total  = floatval(valor($r, ['total', 'Total'], 0));

            $entradaV = fechaValida(valor($r, ['entrada', 'Entrada'])) ?? date('Y-m-d H:i:s');
            $salidaV  = fechaValida(valor($r, ['salida', 'Salida']));
            $fechaRegV = fechaValida(valor($r, ['fecha_registro', 'Fecha_Registro'])) ?? $entradaV;

            $entrada  = $model->escape($entradaV);
            $salida   = $salidaV !== null ? "'" . $model->escape($salidaV) . "'" : 'NULL';
            $fechaReg = $model->escape($fechaRegV);

            $tipo = strtolower((string)valor($r, ['tipo', 'Tipo'], 'sedan'));
            if (!in_array($tipo, ['sedan', 'pickup', 'van'], true)) $tipo = 'sedan';

            $desc    = $model->escape(mb_substr((string)valor($r, ['descripcion', 'Descripcion'], ''), 0, 255));
            $placas  = $model->escape(mb_substr(strtoupper((string)valor($r, ['placas', 'Placas'], '')), 0, 20));
            $estatus = ((string)valor($r, ['estatus', 'Estatus'], 'Pendiente')) === 'Completado' ? 'Completado' : 'Pendiente';
            $horas   = $model->escape((string)valor($r, ['horas', 'Horas'], '0:00'));

            // Quién cobró: el ID manda; el nombre es solo informativo
            $cobroNombre = trim((string)valor($r, ['dispositivo_cobro', 'Dispositivo_Cobro'], ''));
            $cobroUid    = limpiarUid(valor($r, ['dispositivo_cobro_uid', 'Dispositivo_Cobro_UID'], ''));
            if ($estatus === 'Completado') {
                if ($cobroUid === '')    $cobroUid    = $uidRaw;          // apps viejas: cobró la misma
                if ($cobroNombre === '') $cobroNombre = $dispositivoRaw;
            } else {
                $cobroUid = $cobroNombre = '';
            }
            $cobroUidSql    = $cobroUid !== '' ? "'" . $model->escape($cobroUid) . "'" : 'NULL';
            $cobroNombreSql = $cobroNombre !== '' ? "'" . $model->escape(mb_substr($cobroNombre, 0, 60)) . "'" : 'NULL';
            // Para comparar cobros: ID si hay, nombre si es app vieja
            $cobroClave = $cobroUid !== '' ? $cobroUid : $cobroNombre;

            // El ticket se identifica por ID de la tablet de ENTRADA + folio + hora de entrada
            $filtroTablet = $uidRaw !== ''
                ? "Dispositivo_UID = $uidSql"
                : "Dispositivo = '$dispositivo' AND Dispositivo_UID IS NULL";

            $check = $model->_consultar(
                "SELECT ID_Registro, Estatus, Dispositivo_Cobro, Dispositivo_Cobro_UID, Dispositivo, Salida FROM registros
                 WHERE $filtroTablet
                   AND Folio_Tablet = '$folio'
                   AND (Entrada = '$entrada' OR Fecha_Registro = '$fechaReg')
                 LIMIT 1
                 FOR UPDATE"
            );

            // Transición: un ticket que se subió antes con el subir.php viejo no tiene ID.
            // Si coincide por nombre de tablet + folio + entrada, se le asigna el ID
            // en lugar de insertarlo duplicado.
            if ($uidRaw !== '' && (!is_array($check) || count($check) === 0)) {
                $check = $model->_consultar(
                    "SELECT ID_Registro, Estatus, Dispositivo_Cobro, Dispositivo_Cobro_UID, Dispositivo, Salida FROM registros
                     WHERE Dispositivo = '$dispositivo' AND Dispositivo_UID IS NULL
                       AND Folio_Tablet = '$folio'
                       AND (Entrada = '$entrada' OR Fecha_Registro = '$fechaReg')
                     LIMIT 1
                     FOR UPDATE"
                );
                if (is_array($check) && count($check) > 0) {
                    $model->_insertar("UPDATE registros SET Dispositivo_UID = $uidSql WHERE ID_Registro = " . intval($check[0]['ID_Registro']));
                }
            }

            if (is_array($check) && count($check) > 0) {
                $existente   = $check[0];
                $idExistente = intval($existente['ID_Registro']);
                $yaCobrado   = ($existente['Estatus'] ?? '') === 'Completado';
                $cobroPrevioUid    = trim((string)($existente['Dispositivo_Cobro_UID'] ?? ''));
                $cobroPrevioNombre = trim((string)($existente['Dispositivo_Cobro'] ?? '')) ?: trim((string)$existente['Dispositivo']);
                if ($yaCobrado && $cobroPrevioUid === '') {
                    $cobroPrevioUid = $uidRaw; // cobrado por una app vieja: fue la misma tablet
                }
                $cobroPrevioClave = $cobroPrevioUid !== '' ? $cobroPrevioUid : $cobroPrevioNombre;

                if ($yaCobrado && $estatus !== 'Completado') {
                    // CONFLICTO 1: la tablet lo tiene "adentro", pero otra ya lo cobró.
                    // Gana el cobro. La tablet debe quitarlo de su lista.
                    $cobradosEnOtraTablet[] = [
                        'dispositivo_uid' => $uidRaw,
                        'dispositivo'     => $dispositivoRaw,
                        'folio'           => $folioRaw,
                        'entrada'         => $entradaV,
                        'cobrado_por'     => $cobroPrevioNombre,
                        'salida'          => $existente['Salida']
                    ];
                } elseif ($yaCobrado && $cobroPrevioClave !== $cobroClave) {
                    // CONFLICTO 2: se cobró en dos tablets distintas.
                    // Se conserva el primer cobro y se reporta el duplicado.
                    $cobrosDuplicados[] = [
                        'dispositivo_uid' => $uidRaw,
                        'dispositivo'     => $dispositivoRaw,
                        'folio'           => $folioRaw,
                        'entrada'         => $entradaV,
                        'cobrado_por'     => $cobroPrevioNombre,
                        'cobro_extra'     => $cobroNombre,
                        'total_extra'     => $total
                    ];
                    error_log("[subir.php] Cobro duplicado: folio $folioRaw ($entradaV) de '$dispositivoRaw' cobrado por '$cobroPrevioClave' y por '$cobroClave' ($total)");
                } elseif ($estatus === 'Completado') {
                    // Cobro nuevo (o reenvío del mismo cobro): se guarda completo
                    $model->_insertar(
                        "UPDATE registros SET
                            FK_Detalle_Caja       = $fkDetalle,
                            Dispositivo_UID       = COALESCE(Dispositivo_UID, $uidSql),
                            Dispositivo_Cobro     = $cobroNombreSql,
                            Dispositivo_Cobro_UID = $cobroUidSql,
                            Salida      = $salida,
                            Horas       = '$horas',
                            Estatus     = 'Completado',
                            Total       = $total,
                            Tipo        = '$tipo',
                            Descripcion = '$desc',
                            Placas      = '$placas'
                         WHERE ID_Registro = $idExistente"
                    );
                } else {
                    // Sigue adentro en ambos lados: solo se actualizan los datos del vehículo
                    $model->_insertar(
                        "UPDATE registros SET
                            Tipo        = '$tipo',
                            Descripcion = '$desc',
                            Placas      = '$placas'
                         WHERE ID_Registro = $idExistente"
                    );
                }
            } else {
                $model->_insertar(
                    "INSERT INTO registros
                        (Dispositivo, Dispositivo_UID, Dispositivo_Cobro, Dispositivo_Cobro_UID, Folio_Tablet, FK_Detalle_Caja, Media, Precio, Extra, Entrada, Salida, Tipo, Descripcion, Placas, Estatus, Horas, Total, Fecha_Registro)
                     VALUES
                        ('$dispositivo', $uidSql, $cobroNombreSql, $cobroUidSql, '$folio', $fkDetalle, $media, $precio, $extra, '$entrada', $salida, '$tipo', '$desc', '$placas', '$estatus', '$horas', $total, '$fechaReg')"
                );
            }
            $registrosGuardados++;
        }

        // ---------- ENLAZAR REGISTROS CON SU CORTE REAL ----------
        // La tablet manda un ID de caja local (1, 2, 3...) que no coincide con el
        // ID_Detalle_Caja del servidor. Al llegar el corte se reasignan:
        //  - Cobrados: al corte de la tablet (por su ID) que COBRÓ, si la salida cae en ese turno
        //    (el dinero está en esa caja, aunque el auto haya entrado por otra tablet).
        //  - Pendientes: al corte de la tablet donde entraron, si entraron en ese turno.
        foreach ($cortesParaEnlazar as $ce) {
            if ($ce['uid'] !== '') {
                $quienCobro = "COALESCE(Dispositivo_Cobro_UID, Dispositivo_UID) = '{$ce['uid']}'";
                $quienEntro = "Dispositivo_UID = '{$ce['uid']}'";
            } else {
                // Apps viejas sin ID: por nombre
                $quienCobro = "COALESCE(NULLIF(Dispositivo_Cobro, ''), Dispositivo) = '{$ce['dispositivo']}' AND Dispositivo_Cobro_UID IS NULL";
                $quienEntro = "Dispositivo = '{$ce['dispositivo']}' AND Dispositivo_UID IS NULL";
            }
            $model->_insertar(
                "UPDATE registros SET FK_Detalle_Caja = {$ce['id']}
                 WHERE Estatus = 'Completado' AND $quienCobro
                   AND Salida >= '{$ce['apertura']}'
                   AND Salida <= '{$ce['cierre']}'"
            );
            $model->_insertar(
                "UPDATE registros SET FK_Detalle_Caja = {$ce['id']}
                 WHERE Estatus <> 'Completado' AND $quienEntro
                   AND Entrada >= '{$ce['apertura']}'
                   AND Entrada <= '{$ce['cierre']}'"
            );
        }

        $model->_insertar("COMMIT");
    } catch (Throwable $e) {
        try {
            $model->_insertar("ROLLBACK");
        } catch (Throwable $ignorar) {
        }
        throw $e;
    }

    responder([
        'status'              => 'success',
        'message'             => 'Correcto',
        'registros_guardados'     => $registrosGuardados,
        'cortes_guardados'        => $cortesGuardados,
        'cobrados_en_otra_tablet' => $cobradosEnOtraTablet,
        'cobros_duplicados'       => $cobrosDuplicados,
        'fecha'               => date('Y-m-d H:i:s')
    ]);
}

/**
 * Crea (si faltan) las columnas de identidad de tablets en la BD del estacionamiento:
 *   registros.Dispositivo_UID        ID de la tablet donde entró el vehículo
 *   registros.Dispositivo_Cobro      nombre de la tablet que cobró (informativo)
 *   registros.Dispositivo_Cobro_UID  ID de la tablet que cobró (dueña del dinero)
 *   detalles_caja.Dispositivo_UID    ID de la tablet del corte
 */
function asegurarColumnasIdentidad($model): void
{
    $columnas = [
        ['registros',     'Dispositivo_UID',       "VARCHAR(40) NULL AFTER Dispositivo"],
        ['registros',     'Dispositivo_Cobro',     "VARCHAR(60) NULL AFTER Dispositivo_UID"],
        ['registros',     'Dispositivo_Cobro_UID', "VARCHAR(40) NULL AFTER Dispositivo_Cobro"],
        ['detalles_caja', 'Dispositivo_UID',       "VARCHAR(40) NULL AFTER Dispositivo"],
    ];
    foreach ($columnas as [$tabla, $col, $def]) {
        $existe = $model->_consultar("SHOW COLUMNS FROM `$tabla` LIKE '$col'");
        if (!is_array($existe) || count($existe) === 0) {
            $model->_insertar("ALTER TABLE `$tabla` ADD COLUMN `$col` $def");
            error_log("[subir.php] Columna $tabla.$col creada");
        }
    }
}

/** Deja solo caracteres seguros en el ID de una tablet (máx. 40). */
function limpiarUid($valor): string
{
    return substr(preg_replace('/[^A-Za-z0-9_\-]/', '', (string)$valor), 0, 40);
}

/** Primer valor no vacío entre varias llaves posibles. */
function valor(array $arr, array $llaves, $default = null)
{
    foreach ($llaves as $k) {
        if (array_key_exists($k, $arr) && $arr[$k] !== null && $arr[$k] !== '' && $arr[$k] !== 'null') {
            return $arr[$k];
        }
    }
    return $default;
}

/** Devuelve la fecha en formato Y-m-d H:i:s, o null si no es válida. */
function fechaValida($valor): ?string
{
    if ($valor === null) return null;
    $s = trim(str_replace('T', ' ', (string)$valor));
    if ($s === '' || strpos($s, '0000-00-00') === 0) return null;

    $ts = strtotime($s);
    return $ts === false ? null : date('Y-m-d H:i:s', $ts);
}

/* ============================================================
 * MÉTODO: detectar (foto -> placa, color, tipo, marca)
 * ============================================================ */

function manejarDetectar(array $data, string $token): void
{
    @ini_set('memory_limit', '256M');
    @set_time_limit(40);
    $deadline = microtime(true) + DETECCION_MAX_SEG;

    // Validación opcional de la tablet
    if ($token !== '' || DETECTAR_REQUIERE_TOKEN) {
        if ($token === '') {
            responderError('Esta tablet no está vinculada. Vincúlala en Ajustes.', 'INVALID_TOKEN');
        }
        $sub = obtenerSuscripcionPorToken(obtenerModelo(), $token);
        if (!$sub) {
            responderError('Dispositivo no vinculado o clave inválida. Vuelve a vincular esta tablet en Ajustes.', 'INVALID_TOKEN');
        }
        if (suscripcionVencida($sub)) {
            responderError('⚠️ Tu suscripción ha vencido. Renueva en el panel web para seguir usando la detección.', 'SUBSCRIPTION_EXPIRED');
        }
    }

    $foto   = (string)($data['foto'] ?? $data['image'] ?? '');
    $apiKey = trim((string)($data['api_key'] ?? $data['apiKey'] ?? ''));

    if (($pos = strpos($foto, 'base64,')) !== false) {
        $foto = substr($foto, $pos + 7);
    }
    $foto = preg_replace('/\s+/', '', $foto);

    if ($foto === '') {
        responderError('No se recibió ninguna imagen para analizar.');
    }

    $binario = base64_decode($foto, true);
    if ($binario === false || strlen($binario) < 100) {
        responderError('La imagen recibida no es válida. Toma la foto de nuevo.');
    }
    if (strlen($binario) > FOTO_MAX_BYTES) {
        responderError('La foto es demasiado pesada. Toma la foto de nuevo.');
    }
    unset($foto);

    $img = prepararImagen($binario);
    unset($binario);

    // 1. Si enviaron una clave de Gemini, intentarlo primero
    if ($apiKey !== '') {
        $resultadoIA = analizarConGemini($img['b64'], $apiKey);
        if ($resultadoIA && (!empty($resultadoIA['placa']) || ($resultadoIA['es_vehiculo'] ?? true) === false)) {
            responder([
                'status' => 'success',
                'metodo' => 'ia_vision',
                'datos'  => $resultadoIA
            ]);
        }
    }

    // 2. Servicio gratuito: OCR.space + color por GD + clasificación
    $datos = detectarVehiculoGratuito($img['b64'], $img['im'], $deadline);

    responder([
        'status' => 'success',
        'metodo' => 'servicio_gratuito',
        'datos'  => $datos
    ]);
}

/**
 * Carga la imagen, la reduce a IMG_MAX_LADO y la recomprime por debajo del límite de OCR.space.
 * Devuelve ['im' => recurso GD o null, 'b64' => JPEG en base64].
 */
function prepararImagen(string $binario): array
{
    if (!function_exists('imagecreatefromstring')) {
        return ['im' => null, 'b64' => base64_encode($binario)];
    }

    $im = @imagecreatefromstring($binario);
    if (!$im) {
        return ['im' => null, 'b64' => base64_encode($binario)];
    }

    $w = imagesx($im);
    $h = imagesy($im);
    $ladoMayor = max($w, $h);

    if ($ladoMayor > IMG_MAX_LADO) {
        $escala = IMG_MAX_LADO / $ladoMayor;
        $reducida = @imagescale($im, max(1, (int)round($w * $escala)), max(1, (int)round($h * $escala)), IMG_BICUBIC);
        if ($reducida) {
            $im = $reducida;
        }
    }

    return ['im' => $im, 'b64' => jpegBase64BajoLimite($im, OCR_MAX_BYTES)];
}

/** Codifica a JPEG bajando la calidad hasta que el base64 quepa en $maxBytes. */
function jpegBase64BajoLimite($im, int $maxBytes): string
{
    $b64 = '';
    foreach ([85, 75, 65, 55, 45] as $calidad) {
        ob_start();
        imagejpeg($im, null, $calidad);
        $b64 = base64_encode((string)ob_get_clean());
        if (strlen($b64) <= $maxBytes) {
            break;
        }
    }
    return $b64;
}

/**
 * Servicio 100% gratuito de detección vehicular:
 * Placa (NOM-001 / EDOMEX / Estados), color dominante, tipo (pickup, sedan, van), marca y modelo.
 */
function detectarVehiculoGratuito(string $b64, $im, float $deadline): array
{
    $color = $im ? detectarColorVehiculoGD($im) : 'Blanco';

    $ocr      = consultarOCRSpace($b64, $im, $deadline);
    $texto    = $ocr['texto'] ?? '';
    $plateTop = $ocr['plateTop'] ?? null;

    $placa    = normalizarPlaca($texto);
    $vehiculo = clasificarVehiculoCompleto($texto, $im, $plateTop, $placa);

    return [
        'es_vehiculo' => true,
        'placa'       => $placa,
        'color'       => $color,
        'tipo'        => $vehiculo['tipo'],
        'marca'       => $vehiculo['marca'],
        'modelo'      => $vehiculo['modelo']
    ];
}

function detectarColorVehiculoGD($im): string
{
    $w = imagesx($im);
    $h = imagesy($im);

    $totalSamples = 0;
    $chromatic = [];
    $achromatic = [];

    // Paso de muestreo proporcional al tamaño (≈ igual número de muestras en cualquier resolución)
    $paso = max(2, (int)round(max($w, $h) / 320));

    // Muestreo de la carrocería (capó, salpicaderas y defensa, evitando cielo y suelo)
    $zones = [
        ['startX' => (int)($w * 0.25), 'endX' => (int)($w * 0.75), 'startY' => (int)($h * 0.35), 'endY' => (int)($h * 0.55)],
        ['startX' => (int)($w * 0.20), 'endX' => (int)($w * 0.80), 'startY' => (int)($h * 0.55), 'endY' => (int)($h * 0.75)]
    ];

    foreach ($zones as $z) {
        for ($x = $z['startX']; $x < $z['endX']; $x += $paso) {
            for ($y = $z['startY']; $y < $z['endY']; $y += $paso) {
                $rgb = imagecolorat($im, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $max = max($r, $g, $b);
                $min = min($r, $g, $b);
                $sat = $max > 0 ? ($max - $min) / $max : 0;
                $bri = ($r + $g + $b) / 3;
                $totalSamples++;

                // Ignorar sombras extremas (asfalto, llantas) y reflejos
                if ($bri < 30 || $bri > 248) {
                    continue;
                }

                // Descartar luz fría de pantallas donde R y G siguen altos
                $esBrilloFrioPantalla = ($bri > 165 && $r > 130 && $b < ($r * 1.40));

                if ($sat >= 0.25 && !$esBrilloFrioPantalla) {
                    if ($b > 70 && $b > ($r * 1.38) && $b > ($g * 1.15)) {
                        $chromatic['Azul'] = ($chromatic['Azul'] ?? 0) + 1;
                    } elseif ($r > 130 && $r > ($g * 1.35) && $r > ($b * 1.35)) {
                        $chromatic['Rojo'] = ($chromatic['Rojo'] ?? 0) + 1;
                    } elseif ($r > 70 && $r > ($g * 1.30) && $r > ($b * 1.25)) {
                        $chromatic['Vino'] = ($chromatic['Vino'] ?? 0) + 1;
                    } elseif ($g > 65 && $g > ($r * 1.20) && $g > ($b * 1.15)) {
                        $chromatic['Verde'] = ($chromatic['Verde'] ?? 0) + 1;
                    } elseif ($r > 160 && $g > 130 && $b < 90) {
                        $chromatic['Amarillo'] = ($chromatic['Amarillo'] ?? 0) + 1;
                    } elseif ($r > 160 && $g > 80 && $b < 60) {
                        $chromatic['Naranja'] = ($chromatic['Naranja'] ?? 0) + 1;
                    }
                } else {
                    if ($bri >= 115 && $sat < 0.14) {
                        $achromatic['Blanco'] = ($achromatic['Blanco'] ?? 0) + 1;
                    } elseif ($bri >= 150) {
                        $achromatic['Blanco'] = ($achromatic['Blanco'] ?? 0) + 1;
                    } elseif ($bri >= 60) {
                        $achromatic['Plata/Gris'] = ($achromatic['Plata/Gris'] ?? 0) + 1;
                    } else {
                        $achromatic['Negro'] = ($achromatic['Negro'] ?? 0) + 1;
                    }
                }
            }
        }
    }

    arsort($chromatic);

    $topCrom    = key($chromatic);
    $cantCrom   = $topCrom !== null ? $chromatic[$topCrom] : 0;
    $cantBlanco = $achromatic['Blanco'] ?? 0;
    $cantGris   = $achromatic['Plata/Gris'] ?? 0;
    $cantNegro  = $achromatic['Negro'] ?? 0;

    // Color cromático representativo (umbral relativo para que no dependa de la resolución)
    if ($topCrom && ($cantCrom / max(1, $totalSamples)) >= 0.12) {
        return $topCrom;
    }

    if ($cantBlanco >= $cantGris && $cantBlanco >= $cantNegro) {
        return 'Blanco';
    }
    if ($cantGris >= $cantBlanco && $cantGris >= $cantNegro) {
        return 'Plata/Gris';
    }
    if ($cantNegro > ($totalSamples * 0.40)) {
        return 'Negro';
    }
    return ($cantBlanco >= $cantGris) ? 'Blanco' : 'Plata/Gris';
}

/* ---------------- OCR.space ---------------- */

function llavesOCRSpace(): array
{
    $env = getenv('OCR_SPACE_KEYS');
    if ($env) {
        $llaves = array_values(array_filter(array_map('trim', explode(',', $env))));
        if ($llaves) return $llaves;
    }
    return OCR_SPACE_KEYS_DEFAULT;
}

function ejecutarLlamadaOCRSpace(string $b64, array $llaves, float $deadline): array
{
    $vacio = ['texto' => '', 'plateTop' => null];

    foreach ($llaves as $apiKey) {
        $restante = $deadline - microtime(true);
        if ($restante < 2) {
            break; // sin tiempo: mejor responder lo que haya a que la app corte por timeout
        }

        $ch = curl_init('https://api.ocr.space/parse/image');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => [
                'base64Image'       => 'data:image/jpeg;base64,' . $b64,
                'apikey'            => $apiKey,
                'language'          => 'spa',
                'isOverlayRequired' => 'true',
                'OCREngine'         => 2
            ],
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_TIMEOUT        => max(2, min(12, (int)floor($restante))),
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        $raw = curl_exec($ch);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $raw === '') {
            error_log('[subir.php] OCR.space sin respuesta: ' . $curlErr);
            continue;
        }

        $json = json_decode($raw, true);
        if (!is_array($json)) {
            error_log('[subir.php] OCR.space respuesta no JSON: ' . substr($raw, 0, 200));
            continue;
        }

        if (!empty($json['IsErroredOnProcessing'])) {
            $err = $json['ErrorMessage'] ?? ($json['error'] ?? '');
            $err = is_array($err) ? implode(' | ', $err) : (string)$err;
            error_log('[subir.php] OCR.space error: ' . $err);

            // Si el problema es la imagen (tamaño/formato), otra llave no lo va a arreglar
            if (stripos($err, 'size') !== false || stripos($err, 'dimension') !== false || stripos($err, 'invalid') !== false) {
                break;
            }
            continue; // cuota, llave inválida, etc.: probar la siguiente
        }

        $resultado = $json['ParsedResults'][0] ?? null;
        if (!$resultado) {
            continue;
        }

        $texto = (string)($resultado['ParsedText'] ?? '');
        $plateTop = null;
        foreach (($resultado['TextOverlay']['Lines'] ?? []) as $linea) {
            $words = $linea['Words'] ?? [];
            if (!empty($words) && normalizarPlaca($linea['LineText'] ?? '') !== '') {
                $plateTop = $words[0]['Top'] ?? null;
                break;
            }
        }

        // La llave funcionó: aunque no haya texto, otra llave daría lo mismo
        return ['texto' => $texto, 'plateTop' => $plateTop];
    }

    return $vacio;
}

function consultarOCRSpace(string $b64, $im, float $deadline): array
{
    $llaves = llavesOCRSpace();

    // 1. Imagen completa
    $res      = ejecutarLlamadaOCRSpace($b64, $llaves, $deadline);
    $texto    = $res['texto'];
    $plateTop = $res['plateTop'];

    // 2. Si no hubo placa: recorte de la zona central del vehículo (útil en fotos verticales)
    if (normalizarPlaca($texto) === '' && $im && ($deadline - microtime(true)) > 3) {
        $w = imagesx($im);
        $h = imagesy($im);

        $cropX = (int)($w * 0.15);
        $cropY = (int)($h * 0.28);
        $cropW = (int)($w * 0.70);
        $cropH = (int)($h * 0.48);

        $crop = @imagecrop($im, ['x' => $cropX, 'y' => $cropY, 'width' => $cropW, 'height' => $cropH]);
        if ($crop) {
            // Solo ampliar si el recorte es pequeño; ampliar una imagen grande solo gasta memoria
            $factor = ($cropW < 900) ? 2 : 1;
            if ($factor > 1) {
                $ampliada = @imagescale($crop, $cropW * $factor, $cropH * $factor, IMG_BICUBIC);
                if ($ampliada) {
                    $crop = $ampliada;
                } else {
                    $factor = 1;
                }
            }

            $b64Crop = jpegBase64BajoLimite($crop, OCR_MAX_BYTES);
            unset($crop);

            $resCrop = ejecutarLlamadaOCRSpace($b64Crop, $llaves, $deadline);
            if ($resCrop['texto'] !== '' && normalizarPlaca($resCrop['texto']) !== '') {
                $texto .= "\n" . $resCrop['texto'];
                if ($resCrop['plateTop'] !== null) {
                    $plateTop = $cropY + ($resCrop['plateTop'] / $factor);
                }
            }
        }
    }

    return ['texto' => $texto, 'plateTop' => $plateTop];
}

/* ---------------- Placas ---------------- */

function normalizarPlaca($texto): string
{
    if (empty($texto)) return '';
    $texto = strtoupper((string)$texto);

    // Limpiar URLs, dominios y correos de agencias
    $texto = preg_replace('/https?:\/\/\S+|www\.\S+|\S+@\S+/i', ' ', $texto);
    $texto = preg_replace('/[a-z0-9_\-\.]+\.(com|mx|org|net|gob|edu)\S*/i', ' ', $texto);
    // Limpiar teléfonos (ej: 3000-6400, 55-1234-5678)
    $texto = preg_replace('/\b[0-9]{3,4}[-\s]?[0-9]{4}\b/', ' ', $texto);

    $ignorar = ['CDMX', 'MEXICO', 'EDOMEX', 'PARTICULAR', 'TRANSPORTE', 'GOBIERNO', 'FRONTERA', 'DELANTERA', 'TRASERA'];

    foreach (preg_split('/[\r\n]+/', $texto) as $linea) {
        $l = trim(preg_replace('/[^A-Z0-9\-\s]/', '', $linea));
        if ($l === '') continue;
        foreach ($ignorar as $ig) {
            $l = trim(preg_replace('/\b' . $ig . '\b/', '', $l));
        }

        $placa = buscarPatronPlaca($l, true);
        if ($placa !== '') return $placa;
    }

    // Si no se encontró por línea, probar en el bloque completo
    $limpio = preg_replace('/[^A-Z0-9\-\s]/', ' ', $texto);
    foreach ($ignorar as $ig) {
        $limpio = trim(preg_replace('/\b' . $ig . '\b/', ' ', $limpio));
    }

    return buscarPatronPlaca($limpio, false);
}

/**
 * Aplica los patrones de placas mexicanas en orden de prioridad.
 * $porLinea = true incluye los patrones CDMX anterior y NOM-001 anterior,
 * que en el bloque completo dan demasiados falsos positivos.
 */
function buscarPatronPlaca(string $s, bool $porLinea): string
{
    // 1. 3 letras + 3 dígitos + 2 dígitos (CVL-657-18)
    if (preg_match('/\b([A-Z]{3})[-\s]*([0-9]{3})[-\s]*([0-9]{2})\b/', $s, $m)) {
        return "{$m[1]}-{$m[2]}-{$m[3]}";
    }
    // 2. Comercial / pickup: 2 letras + 2 dígitos + 3 dígitos (SR-25-920)
    if (preg_match('/\b([A-Z]{2})[-\s]*([0-9]{2})[-\s]*([0-9]{3})\b/', $s, $m)) {
        $prefijo = ($m[1] === 'SH') ? 'SR' : $m[1];
        return "$prefijo-{$m[2]}-{$m[3]}";
    }
    // 3. DF anterior: 1 dígito + 2 letras + 2 dígitos (7-YR-38)
    if (preg_match('/\b([0-9])[-\s]*([A-Z]{2})[-\s]*([0-9]{2})\b/', $s, $m)) {
        return "{$m[1]}-{$m[2]}-{$m[3]}";
    }
    // 4. CDMX actual: 1 letra + 2 dígitos + 3 letras (L41-BBD)
    if (preg_match('/\b([A-Z])([0-9]{2})[-\s]*([A-Z]{3})\b/', $s, $m)) {
        return "{$m[1]}{$m[2]}-{$m[3]}";
    }
    // 5. CDMX anterior: 3 dígitos + 3 letras (123-ABC)
    if ($porLinea && preg_match('/\b([0-9]{3})[-\s]*([A-Z]{3})\b/', $s, $m)) {
        return "{$m[1]}-{$m[2]}";
    }
    // 6. NOM-001 actual: 3 letras + 3 dígitos + 1 letra (LSV-530-A)
    if (preg_match('/\b([A-Z]{3})[-\s]*([0-9]{3})[-\s]*(11|4|[A-Z])\b/', $s, $m)) {
        $letra = ($m[3] === '11' || $m[3] === '4') ? 'A' : $m[3];
        return "{$m[1]}-{$m[2]}-$letra";
    }
    // 7. NOM-001 anterior: 3 letras + 2 dígitos + 2 dígitos (PZA-45-89)
    if ($porLinea && preg_match('/\b([A-Z]{3})[-\s]*([0-9]{2})[-\s]*([0-9]{2})\b/', $s, $m)) {
        return "{$m[1]}-{$m[2]}-{$m[3]}";
    }
    // 8. 3 letras + 4 dígitos (ABC-1234 -> ABC-12-34)
    if (preg_match('/\b([A-Z]{3})[-\s]*([0-9]{4})\b/', $s, $m)) {
        return $m[1] . '-' . substr($m[2], 0, 2) . '-' . substr($m[2], 2, 2);
    }
    // 9. Carga / camión: 2 letras + 4 o 5 dígitos (LE-12345)
    if (preg_match('/\b([A-Z]{2})[-\s]*([0-9]{4,5})\b/', $s, $m)) {
        return "{$m[1]}-{$m[2]}";
    }
    return '';
}

/* ---------------- Clasificación de vehículo ---------------- */

function detectarChevroletBowtie($im): bool
{
    if (!$im) return false;
    $w = imagesx($im);
    $h = imagesy($im);
    $paso = max(2, (int)round(max($w, $h) / 800));
    $goldCount = 0;

    for ($x = (int)($w * 0.44); $x <= (int)($w * 0.56); $x += $paso) {
        for ($y = (int)($h * 0.35); $y <= (int)($h * 0.65); $y += $paso) {
            $rgb = imagecolorat($im, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            if ($r > 180 && $g > 130 && $b < 80 && ($r - $b) > 90) {
                $goldCount++;
            }
        }
    }
    return $goldCount >= 10;
}

function clasificarVehiculoCompleto($textoOCR, $im = null, $plateTop = null, $placa = ''): array
{
    $t = strtoupper((string)$textoOCR);
    $tipo = 'sedan';
    $marca = '';
    $modelo = '';

    $marcasDict = [
        'FORD'       => 'Ford',
        'CHEVROLET'  => 'Chevrolet',
        'CHEVY'      => 'Chevrolet',
        'NISSAN'     => 'Nissan',
        'TOYOTA'     => 'Toyota',
        'HONDA'      => 'Honda',
        'VOLKSWAGEN' => 'VW',
        'VW'         => 'VW',
        'DODGE'      => 'Dodge',
        'RAM'        => 'RAM',
        'JEEP'       => 'Jeep',
        'HYUNDAI'    => 'Hyundai',
        'KIA'        => 'Kia',
        'MAZDA'      => 'Mazda',
        'MITSUBISHI' => 'Mitsubishi',
        'BMW'        => 'BMW',
        'MERCEDES'   => 'Mercedes-Benz',
        'AUDI'       => 'Audi',
        'RENAULT'    => 'Renault',
        'PEUGEOT'    => 'Peugeot',
        'SEAT'       => 'SEAT',
        'SUZUKI'     => 'Suzuki'
    ];

    foreach ($marcasDict as $k => $v) {
        if (preg_match('/\b' . $k . '\b/', $t)) {
            $marca = $v;
            break;
        }
    }

    // Corbatín dorado de Chevrolet en la parrilla
    if ($marca === '' && $im && detectarChevroletBowtie($im)) {
        $marca = 'Chevrolet';
    }

    // Placas de carga / camioneta (serie S o 2 letras + 4-5 dígitos)
    $esPlacaPickup = $placa !== '' && (
        preg_match('/^S[A-Z]-[0-9]{2}-[0-9]{3}$/', $placa) ||
        preg_match('/^[A-Z]{2}-[0-9]{4,5}$/', $placa)
    );

    $esPickup = preg_match('/(BFGOODRICH|GOODRICH|DRICH|RAPTOR|RANGER|LOBO|F-?150|SILVERADO|CHEYENNE|\bRAM\b|TACOMA|HILUX|FRONTIER|NP300|COLORADO|CANYON|AMAROK|SAVEIRO|STRADA|TORNADO|TITAN|TUNDRA|SIERRA|4X4|OFFROAD|ALLTERRAIN|FX4|Z71|TRD|PRO-4X|BATEA|PICK-?UP|LARIAT)/', $t);
    $esVan    = preg_match('/(URVAN|HIACE|TRANSIT|PARTNER|KANGOO|EXPRESS|SAVANA|SUBURBAN|TAHOE|EXPEDITION|EXPLORER|TRAVERSE|PATHFINDER|ARMADA|SIENNA|ODYSSEY|PACIFICA|CARAVAN|VOYAGER|PROMASTER|SPRINTER|CRAFTER|\bVAN\b|MINIVAN|\bSUV\b|CROSSOVER|CR-?V|RAV4|TIGUAN|SPORTAGE|TUCSON|ESCAPE|DUSTER|TRACKER|SELTOS|CRETA|HR-?V|KICKS|SOUL|MURANO|X-TRAIL|ROGUE)/', $t);
    $esSedan  = preg_match('/(VERSA|SENTRA|AVEO|VENTO|JETTA|CIVIC|COROLLA|\bRIO\b|MARCH|\bGOL\b|TSURU|\bBEAT\b|SPARK|ONIX|YARIS|FIGO|FIESTA|FOCUS|ACCORD|CAMRY|ALTIMA|OPTIMA|FORTE|ELANTRA|SEDAN|CRUZE)/', $t);

    if ($esPlacaPickup || $esPickup) {
        $tipo = 'pickup';
    } elseif ($esVan) {
        $tipo = 'van';
    } elseif ($esSedan) {
        $tipo = 'sedan';
    }

    // Modelo: solo si aparece escrito en el texto OCR
    $modelos = [
        'VERSA' => 'Versa', 'SENTRA' => 'Sentra', 'MARCH' => 'March', 'TSURU' => 'Tsuru', 'KICKS' => 'Kicks',
        'NP300' => 'NP300', 'FRONTIER' => 'Frontier', 'MURANO' => 'Murano', 'X-TRAIL' => 'X-Trail', 'URVAN' => 'Urvan',
        'AVEO' => 'Aveo', 'ONIX' => 'Onix', 'CRUZE' => 'Cruze', 'SPARK' => 'Spark', 'BEAT' => 'Beat',
        'SILVERADO' => 'Silverado', 'CHEYENNE' => 'Cheyenne', 'TORNADO' => 'Tornado', 'TRACKER' => 'Tracker', 'SUBURBAN' => 'Suburban', 'TAHOE' => 'Tahoe',
        'JETTA' => 'Jetta', 'VENTO' => 'Vento', 'GOL' => 'Gol', 'TIGUAN' => 'Tiguan', 'SAVEIRO' => 'Saveiro', 'AMAROK' => 'Amarok',
        'COROLLA' => 'Corolla', 'YARIS' => 'Yaris', 'CAMRY' => 'Camry', 'HILUX' => 'Hilux', 'TACOMA' => 'Tacoma', 'RAV4' => 'RAV4', 'HIACE' => 'Hiace',
        'CIVIC' => 'Civic', 'ACCORD' => 'Accord', 'CR-V' => 'CR-V', 'HR-V' => 'HR-V',
        'RANGER' => 'Ranger', 'LOBO' => 'Lobo', 'F-150' => 'F-150', 'F150' => 'F-150', 'FIGO' => 'Figo', 'FIESTA' => 'Fiesta', 'ESCAPE' => 'Escape', 'EXPLORER' => 'Explorer', 'TRANSIT' => 'Transit',
        'RIO' => 'Rio', 'FORTE' => 'Forte', 'SPORTAGE' => 'Sportage', 'SELTOS' => 'Seltos', 'SOUL' => 'Soul',
        'ELANTRA' => 'Elantra', 'TUCSON' => 'Tucson', 'CRETA' => 'Creta', 'DUSTER' => 'Duster'
    ];
    foreach ($modelos as $k => $v) {
        if (preg_match('/\b' . preg_quote($k, '/') . '\b/', $t)) {
            $modelo = $v;
            break;
        }
    }

    return [
        'tipo'   => $tipo,
        'marca'  => $marca,
        'modelo' => $modelo
    ];
}

/* ---------------- Gemini (opcional) ---------------- */

function analizarConGemini(string $b64, string $apiKey): ?array
{
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODELO . ':generateContent?key=' . urlencode($apiKey);

    $prompt = 'Analiza esta imagen tomada en la entrada de un estacionamiento.
1. Determina si en la imagen aparece un vehículo.
Si NO aparece un vehículo reconocible, responde en JSON estricto:
{"es_vehiculo": false, "mensaje": "No se detectó un vehículo en la foto"}
2. Si SÍ aparece un vehículo, extrae los siguientes datos en JSON estricto:
{
  "es_vehiculo": true,
  "placa": "El número de placa visible (o vacío si no se ve)",
  "color": "Uno de estos: Blanco, Negro, Plata/Gris, Rojo, Azul, Vino, Verde, Amarillo, Café, Naranja",
  "tipo": "Uno de estos 3: sedan (auto común), pickup (camioneta con caja/batea), van (camioneta cerrada, SUV, minivan)",
  "marca": "Marca del auto (ej: Nissan, Chevrolet, VW, Ford, Toyota, Honda) o vacío si no es legible",
  "modelo": "Modelo si es identificable (ej: Versa, Sentra, Aveo, Silverado) o vacío"
}
Devuelve ÚNICAMENTE el JSON, sin texto adicional ni bloques markdown.';

    $payload = [
        'contents' => [[
            'parts' => [
                ['text' => $prompt],
                ['inline_data' => ['mime_type' => 'image/jpeg', 'data' => $b64]]
            ]
        ]],
        'generationConfig' => [
            'temperature'        => 0.1,
            'response_mime_type' => 'application/json'
        ]
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        error_log("[subir.php] Gemini HTTP $httpCode");
        return null;
    }

    $jsonResp = json_decode($response, true);
    $texto = $jsonResp['candidates'][0]['content']['parts'][0]['text'] ?? '';
    $texto = trim(str_replace(['```json', '```'], '', $texto));
    $datos = json_decode($texto, true);

    if (!is_array($datos)) {
        return null;
    }

    if (isset($datos['es_vehiculo']) && $datos['es_vehiculo'] === false) {
        return [
            'es_vehiculo' => false,
            'mensaje'     => $datos['mensaje'] ?? 'No se detectó un vehículo en la foto'
        ];
    }

    $colores = ['Blanco', 'Negro', 'Plata/Gris', 'Rojo', 'Azul', 'Vino', 'Verde', 'Amarillo', 'Café', 'Naranja'];
    $placaCruda = strtoupper((string)($datos['placa'] ?? ''));
    $placa = normalizarPlaca($placaCruda);
    if ($placa === '') {
        $placa = preg_replace('/[^A-Z0-9]/', '', $placaCruda);
    }

    return [
        'es_vehiculo' => true,
        'placa'       => $placa,
        'color'       => in_array($datos['color'] ?? '', $colores, true) ? $datos['color'] : 'Blanco',
        'tipo'        => in_array($datos['tipo'] ?? '', ['sedan', 'pickup', 'van'], true) ? $datos['tipo'] : 'sedan',
        'marca'       => (string)($datos['marca'] ?? ''),
        'modelo'      => (string)($datos['modelo'] ?? '')
    ];
}
