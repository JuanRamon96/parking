<?php
date_default_timezone_set('America/Mexico_City');
include_once __DIR__ . '/../modelo/m_modelo.php';
include_once __DIR__ . '/c_login.php';
include_once __DIR__ . '/c_dashboard.php';
include_once __DIR__ . '/c_reportes.php';
include_once __DIR__ . '/c_cuenta.php';
include_once __DIR__ . '/c_dispositivos.php';
include_once __DIR__ . '/c_suscripcion.php';
include_once __DIR__ . '/c_perfil.php';
include_once __DIR__ . '/c_registro.php';
include_once __DIR__ . '/c_tarifas.php';

class controller
{
    public function _layouts()
    {
        $pagina = file_get_contents(__DIR__ . '/../vistas/v_html.php');
        $pagina = $this->remplazar($pagina, 'v_html');
        return $pagina;
    }

    public function _contenido($vista)
    {
        $vista = basename(str_replace('\\', '/', $vista));
        $file = __DIR__ . "/../vistas/$vista.php";

        if (!file_exists($file)) {
            return '<div class="alert alert-danger m-3"><i class="bi bi-exclamation-triangle-fill me-2"></i>La vista solicitada no existe.</div>';
        }

        $pagina = file_get_contents($file);
        $pagina = $this->remplazar($pagina, $vista);
        return $pagina;
    }

    public function _consultar($metodo)
    {
        if (class_exists($metodo)) {
            $objeto = new $metodo();
            $objeto->_consultar();
        } else {
            echo json_encode(array('error' => "Controlador $metodo no encontrado"));
        }
    }

    public function _insertar($metodo)
    {
        if (class_exists($metodo)) {
            $objeto = new $metodo();
            $objeto->_insertar();
        } else {
            echo "Error: Controlador no existe";
        }
    }

    public function _modificar($metodo)
    {
        if (class_exists($metodo)) {
            $objeto = new $metodo();
            $objeto->_modificar();
        } else {
            echo "Error: Controlador no existe";
        }
    }

    public function _eliminar($metodo)
    {
        if (class_exists($metodo)) {
            $objeto = new $metodo();
            $objeto->_eliminar();
        } else {
            echo "Error: Controlador no existe";
        }
    }

    public function _detalles($metodo)
    {
        if (class_exists($metodo)) {
            $objeto = new $metodo();
            $objeto->_detalles();
        } else {
            echo "Error: Controlador no existe";
        }
    }

    public function remplazar($pagina, $nombre)
    {
        $nombreUsuario = isset($_SESSION['user_estacionamiento']['Nombre']) ? $_SESSION['user_estacionamiento']['Nombre'] : 'Administrador';
        $correoUsuario = isset($_SESSION['user_estacionamiento']['Correo']) ? $_SESSION['user_estacionamiento']['Correo'] : 'admin@estacionamiento.com';
        $negocioUsuario = isset($_SESSION['user_estacionamiento']['Negocio']) ? $_SESSION['user_estacionamiento']['Negocio'] : 'Estacionamiento';
        
        $partes = explode(' ', trim($nombreUsuario));
        $iniciales = '';
        if (count($partes) >= 2) {
            $iniciales = strtoupper(mb_substr($partes[0], 0, 1) . mb_substr($partes[1], 0, 1));
        } else {
            $iniciales = strtoupper(mb_substr($nombreUsuario, 0, 2));
        }

        // Estructura de menú con diseño Spark Admin
        $menuHtml = '
        <div class="sidebar-menu-section">
          <div class="sidebar-menu-title">Panel Principal</div>
          <ul class="sidebar-menu-list">
            <li class="sidebar-menu-item">
              <a href="javascript:void(0)" class="sidebar-menu-link cargarVista active" carga="v_dashboard" titulo="Dashboard" id="bMenuDashboard">
                <i class="bi bi-grid-fill"></i>
                <span>Dashboard</span>
              </a>
            </li>
          </ul>
        </div>

        <div class="sidebar-menu-section">
          <div class="sidebar-menu-title">Gestión y Caja</div>
          <ul class="sidebar-menu-list">
            <li class="sidebar-menu-item">
              <a href="javascript:void(0)" class="sidebar-menu-link cargarVista" carga="v_reportes" titulo="Reportes y Cortes" id="bMenuReportes">
                <i class="bi bi-bar-chart-line-fill"></i>
                <span>Reportes y Cortes</span>
              </a>
            </li>
          </ul>
        </div>

        <div class="sidebar-menu-section">
          <div class="sidebar-menu-title">Configuración</div>
          <ul class="sidebar-menu-list">
            <li class="sidebar-menu-item">
              <a href="javascript:void(0)" class="sidebar-menu-link cargarVista" carga="v_tarifas" titulo="Tarifas y Datos" id="bMenuTarifas">
                <i class="bi bi-tag-fill"></i>
                <span>Tarifas y Datos</span>
              </a>
            </li>
          </ul>
        </div>

        <div class="sidebar-menu-section">
          <div class="sidebar-menu-title">Tablets y Planes</div>
          <ul class="sidebar-menu-list">
            <li class="sidebar-menu-item">
              <a href="javascript:void(0)" class="sidebar-menu-link cargarVista" carga="v_dispositivos" titulo="Tablets y Dispositivos" id="bMenuDispositivos">
                <i class="bi bi-tablet-fill"></i>
                <span>Tablets / Dispositivos</span>
              </a>
            </li>
            <li class="sidebar-menu-item">
              <a href="javascript:void(0)" class="sidebar-menu-link cargarVista" carga="v_suscripcion" titulo="Mi Suscripción y Planes" id="bMenuSuscripcion">
                <i class="bi bi-credit-card-2-front-fill"></i>
                <span>Mi Suscripción</span>
              </a>
            </li>
          </ul>
        </div>';

        // Consultar estado de suscripción
        $idSub = (int)($_SESSION['user_estacionamiento']['ID_Usuario'] ?? 0);
        $rowSub = null;
        if ($idSub > 0) {
            $mSubs = new m_modelo('parking_subs');
            $rowSub = $mSubs->_consultar("SELECT Plan, Estatus, Fecha_Vence, Ilimitado FROM suscripciones WHERE ID_Suscripcion = $idSub LIMIT 1");
        }

        $badgeSub = '';
        if (is_array($rowSub) && count($rowSub) > 0) {
            $s = $rowSub[0];
            if (intval($s['Ilimitado']) === 1) {
                $badgeSub = '<span class="badge bg-warning text-dark border px-2 py-1 small fw-bold me-2 cargarVista" carga="v_suscripcion" titulo="Mi Suscripción" style="cursor: pointer;"><i class="bi bi-infinity me-1"></i>Plan Ilimitado</span>';
            } else {
                $dias = ceil((strtotime($s['Fecha_Vence']) - time()) / 86400);
                if ($dias <= 0) {
                    $badgeSub = '<span class="badge bg-danger text-white border px-2 py-1 small fw-bold me-2 cargarVista" carga="v_suscripcion" titulo="Mi Suscripción" style="cursor: pointer;"><i class="bi bi-exclamation-triangle-fill me-1"></i>Suscripción Vencida</span>';
                } else if ($s['Plan'] === 'Prueba') {
                    $badgeSub = '<span class="badge bg-info text-dark border px-2 py-1 small fw-bold me-2 cargarVista" carga="v_suscripcion" titulo="Mi Suscripción" style="cursor: pointer;"><i class="bi bi-gift-fill me-1"></i>Prueba: ' . $dias . ' d</span>';
                } else {
                    $badgeSub = '<span class="badge bg-success text-white border px-2 py-1 small fw-bold me-2 cargarVista" carga="v_suscripcion" titulo="Mi Suscripción" style="cursor: pointer;"><i class="bi bi-check-circle-fill me-1"></i>Plan ' . htmlspecialchars($s['Plan']) . ': ' . $dias . ' d</span>';
                }
            }
        }

        $pagina = str_replace('#menuEstacionamiento#', $menuHtml, $pagina);
        $pagina = str_replace('#nombreUsuario#', htmlspecialchars($nombreUsuario), $pagina);
        $pagina = str_replace('#nombreNegocio#', htmlspecialchars($negocioUsuario), $pagina);
        $pagina = str_replace('#correoUsuario#', htmlspecialchars($correoUsuario), $pagina);
        $pagina = str_replace('#inicialesUsuario#', htmlspecialchars($iniciales), $pagina);
        $pagina = str_replace('#badgeSuscripcion#', $badgeSub, $pagina);
        $pagina = str_replace('#tituloInicial#', 'Dashboard', $pagina);
        $pagina = str_replace('#crumbInicial#', 'Control y métricas en tiempo real', $pagina);
        $pagina = str_replace('#v#', time(), $pagina);

        return $pagina;
    }
}
?>
