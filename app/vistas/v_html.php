<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ParkingPro | Panel Administrativo</title>

  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="vistas/assets/images/favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="vistas/assets/images/favicon.png">
  <link rel="apple-touch-icon" href="vistas/assets/images/favicon.png">

  <!-- Local Third-Party Libraries (Spark Admin Template) -->
  <link rel="stylesheet" href="vistas/assets/libs/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="vistas/assets/libs/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="vistas/assets/libs/apexcharts/apexcharts.css">
  <link rel="stylesheet" href="vistas/assets/libs/flatpickr/flatpickr.min.css">

  <!-- Main Design System & Custom Stylesheet -->
  <link rel="stylesheet" href="vistas/assets/css/main.css?v=#v#">

  <!-- myDataTable Plugin -->
  <link rel="stylesheet" href="vistas/assets/plugins/myDataTable/css/myDataTable.css?v=#v#">

  <style>
    /* Spinner de carga adaptado a la paleta Spark Admin */
    .carga {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(7, 47, 31, 0.45);
      backdrop-filter: blur(4px);
      z-index: 9999;
      display: none;
      align-items: center;
      justify-content: center;
    }
    .carga-inner {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 12px;
      color: #B4F105;
    }
    .carga .spinner-border {
      width: 4rem;
      height: 4rem;
      color: #B4F105 !important;
      border-width: 0.3em;
    }
    /* myDataTable ajustes visuales para combinar con Spark Admin */
    .numRowsMyDataTable {
      border-radius: 8px;
      border-color: #E2E8F0;
    }
    .buscadorMyDataTable {
      border-radius: 8px;
      border-color: #E2E8F0;
    }
    .sidebar-profile-avatar {
      width: 40px;
      height: 40px;
      min-width: 40px;
      border-radius: 50%;
      background: #11442B;
      color: #B4F105;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      border: 1px solid rgba(180, 241, 5, 0.3);
    }
    /* Empujar dropdown y acciones de barra superior a la orilla derecha */
    .navbar-custom {
      display: flex !important;
      justify-content: space-between !important;
      align-items: center !important;
      grid-template-columns: unset !important;
    }
    .navbar-actions {
      margin-left: auto !important;
      display: flex !important;
      align-items: center !important;
    }

    /* Prevención de scroll horizontal en pantalla completa */
    html, body {
      overflow-x: hidden;
      max-width: 100vw;
    }

    /* Adaptación full responsive / mobile friendly */
    @media (max-width: 768px) {
      .main-wrapper {
        padding: 0.75rem 0.75rem 1.5rem 0.75rem !important;
      }
      .navbar-custom {
        margin-top: -0.75rem !important;
        margin-left: -0.75rem !important;
        margin-right: -0.75rem !important;
        padding: 0.75rem 0.75rem !important;
        margin-bottom: 1rem !important;
      }
      #verVista {
        padding: 0 !important;
      }
      .page-header {
        margin-bottom: 1rem !important;
      }
      .page-title {
        font-size: 1.4rem !important;
      }
      .card-stat {
        padding: 1.25rem 1rem !important;
      }
      .card-stat .stat-value {
        font-size: 1.5rem !important;
      }
      .card-header {
        padding: 0.85rem 1rem !important;
      }
      .card-body {
        padding: 1rem 0.85rem !important;
      }
    }

    /* Scroll horizontal limpio y único para myDataTable */
    .table-responsive {
      -webkit-overflow-scrolling: touch;
      overflow-x: auto !important;
      overflow-y: hidden;
      width: 100%;
    }
    .table-responsive::-webkit-scrollbar {
      height: 5px;
    }
    .table-responsive::-webkit-scrollbar-thumb {
      background-color: #CBD5E1;
      border-radius: 4px;
    }
    .table-responsive::-webkit-scrollbar-thumb:hover {
      background-color: #94A3B8;
    }
    .myDataTable th, .myDataTable td {
      white-space: nowrap;
    }

    /* Dropdown de Perfil - Diseño Spark Admin, Alto Contraste y Ajuste Móvil */
    .dropdown-menu-profile {
      min-width: 220px !important;
      max-width: 280px !important;
      padding: 0 !important;
      border-radius: 14px !important;
      border: 1px solid #E2E8F0 !important;
      box-shadow: 0 12px 28px rgba(5, 28, 18, 0.12) !important;
      overflow: hidden;
      margin-top: 10px !important;
      background-color: #FFFFFF !important;
    }
    .dropdown-menu-profile .dropdown-header {
      background-color: #F8FAFC !important;
      padding: 0.85rem 1rem !important;
      border-bottom: 1px solid #E2E8F0 !important;
      text-transform: none !important;
      margin: 0 !important;
      letter-spacing: normal !important;
    }
    .dropdown-menu-profile .dropdown-header span {
      font-size: 0.875rem !important;
      font-weight: 700 !important;
      color: #0F172A !important;
      display: block;
      line-height: 1.3;
    }
    .dropdown-menu-profile .dropdown-header small {
      font-size: 0.75rem !important;
      font-weight: 500 !important;
      color: #64748B !important;
      display: block;
      text-transform: lowercase !important;
      word-break: break-all;
      white-space: normal;
      line-height: 1.3;
      margin-top: 3px;
    }
    .dropdown-menu-profile .dropdown-item {
      padding: 0.7rem 1rem !important;
      font-size: 0.85rem !important;
      font-weight: 600 !important;
      color: #1E293B !important;
      display: flex !important;
      align-items: center !important;
      gap: 0.65rem !important;
      transition: all 0.18s ease-in-out !important;
      background-color: transparent !important;
      text-decoration: none !important;
    }
    .dropdown-menu-profile .dropdown-item i {
      font-size: 1.1rem !important;
      color: #072F1F !important;
      transition: transform 0.18s ease, color 0.18s ease;
    }
    .dropdown-menu-profile .dropdown-item:hover,
    .dropdown-menu-profile .dropdown-item:focus {
      background-color: #F1F5F3 !important;
      color: #072F1F !important;
    }
    .dropdown-menu-profile .dropdown-item:hover i,
    .dropdown-menu-profile .dropdown-item:focus i {
      color: #072F1F !important;
      transform: translateX(2px);
    }
    .dropdown-menu-profile .dropdown-item.active,
    .dropdown-menu-profile .dropdown-item:active {
      background-color: #072F1F !important;
      color: #FFFFFF !important;
    }
    .dropdown-menu-profile .dropdown-item.active i,
    .dropdown-menu-profile .dropdown-item:active i {
      color: #B4F105 !important;
    }
    /* Botón Cerrar Sesión con buen contraste y alerta */
    .dropdown-menu-profile .dropdown-item.text-danger {
      color: #EF4444 !important;
    }
    .dropdown-menu-profile .dropdown-item.text-danger i {
      color: #EF4444 !important;
    }
    .dropdown-menu-profile .dropdown-item.text-danger:hover,
    .dropdown-menu-profile .dropdown-item.text-danger:focus {
      background-color: #FEE2E2 !important;
      color: #DC2626 !important;
    }
    .dropdown-menu-profile .dropdown-item.text-danger:hover i,
    .dropdown-menu-profile .dropdown-item.text-danger:focus i {
      color: #DC2626 !important;
      transform: translateX(2px);
    }
    .dropdown-menu-profile .dropdown-divider {
      margin: 0 !important;
      border-color: #E2E8F0 !important;
    }
    @media (max-width: 768px) {
      .dropdown-menu-profile {
        right: 0 !important;
        left: auto !important;
        min-width: 210px !important;
        max-width: calc(100vw - 20px) !important;
      }
    }
  </style>
</head>
<body>

  <!-- Spinner Global de Carga -->
  <div class="carga" id="carga">
    <div class="carga-inner">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <span class="fw-semibold text-white small">Cargando información...</span>
    </div>
  </div>

  <!-- Sidebar Component (Spark Admin Design) -->
  <div class="sidebar-wrapper" id="sidebar">
    <!-- Brand Logo / Identity -->
    <a href="javascript:void(0)" class="sidebar-brand cargarVista" carga="v_dashboard" titulo="Dashboard">
      <img src="vistas/assets/images/favicon.png" width="30" height="30" class="rounded-2 me-2 shadow-sm" alt="ParkingPro">
      <span>Estacionamiento</span>
    </a>

    <!-- Navigation Menu (Generado dinámicamente) -->
    <div class="flex-grow-1 overflow-y-auto">
      #menuEstacionamiento#
    </div>

    <!-- Sidebar Profile Card (Dynamic Footer) -->
    <div class="sidebar-profile cargarVista" carga="v_cuenta" titulo="Mi Cuenta" style="cursor: pointer;" title="Ver mi perfil y configuración">
      <div class="sidebar-profile-avatar">
        #inicialesUsuario#
      </div>
      <div class="sidebar-profile-info">
        <div class="sidebar-profile-name">#nombreUsuario#</div>
        <div class="sidebar-profile-email">#correoUsuario#</div>
      </div>
    </div>
  </div>

  <!-- Main Content Area -->
  <div class="main-wrapper">

    <!-- Top Navbar Component -->
    <header class="navbar-custom">
      <div class="navbar-left">
        <!-- Desktop sidebar toggle -->
        <button class="btn-desktop-toggle d-none d-xl-flex align-items-center justify-content-center me-3" id="desktop-sidebar-toggle" aria-label="Minimizar Sidebar">
          <i class="bi bi-chevron-bar-left"></i>
        </button>
        <!-- Mobile sidebar toggle -->
        <button class="sidebar-toggle-btn me-2" id="sidebar-toggle" aria-label="Abrir Menú">
          <i class="bi bi-list"></i>
        </button>

        <!-- Título de sección activa -->
        <div class="d-none d-sm-flex flex-column ms-2">
          <span class="fw-bold text-dark fs-6" id="topbarTitulo">#tituloInicial#</span>
          <span class="text-muted" style="font-size: 0.75rem;" id="topbarSubtitulo">#crumbInicial#</span>
        </div>
      </div>

      <!-- Acciones de barra superior -->
      <div class="navbar-actions">
        <!-- Badge de Suscripción -->
        #badgeSuscripcion#

        <!-- Botón Pantalla Completa -->
        <button class="navbar-action-btn me-1" aria-label="Pantalla Completa" id="btn-fullscreen">
          <i class="bi bi-arrows-fullscreen"></i>
        </button>

        <!-- Dropdown de Perfil -->
        <div class="dropdown ms-2">
          <button class="navbar-profile-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="profile-dropdown">
            <div class="sidebar-profile-avatar me-2" style="width: 34px; height: 34px; min-width: 34px; font-size: 12px;">
              #inicialesUsuario#
            </div>
            <span class="navbar-profile-name d-none d-md-inline">#nombreUsuario#</span>
            <i class="bi bi-chevron-down navbar-profile-caret"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-profile shadow-sm border-0" aria-labelledby="profile-dropdown">
            <li class="dropdown-header">
              <span class="profile-header-name">#nombreUsuario#</span>
              <small class="profile-header-email">#correoUsuario#</small>
            </li>
            <li>
              <a class="dropdown-item cargarVista" href="javascript:void(0)" carga="v_cuenta" titulo="Mi Perfil">
                <i class="bi bi-person me-2"></i> Mi Perfil
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item text-danger bCerrarSe" href="javascript:void(0)" id="bCerrarSe">
                <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
              </a>
            </li>
          </ul>
        </div>
      </div>
    </header>

    <!-- Contenedor dinámico SPA -->
    <div class="p-3 p-md-4" id="verVista"></div>

  </div>

  <!-- jQuery & Soporte -->
  <script src="vistas/assets/plugins/jquery-4.0.0.min.js"></script>
  <script>
    if (window.jQuery) {
      if (typeof jQuery.trim !== 'function') {
        jQuery.trim = function (text) {
          return text == null ? '' : String(text).replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
        };
      }
      if (typeof jQuery.isArray !== 'function') {
        jQuery.isArray = Array.isArray;
      }
      if (typeof jQuery.isFunction !== 'function') {
        jQuery.isFunction = function (obj) {
          return typeof obj === 'function';
        };
      }
    }
    function parseRespuesta(res) {
      if (typeof res === 'object' && res !== null) {
        if (res.session_expired) {
          window.location.href = './';
        }
        return res;
      }
      var txt = String(res == null ? '' : res).replace(/[\uFEFF]/g, '').trim();
      if (txt === '') {
        return {};
      }
      if (txt.indexOf('<!DOCTYPE') !== -1 || txt.indexOf('<html') !== -1 || txt.indexOf('id="formLogin"') !== -1 || txt.indexOf('session_expired') !== -1) {
        if (txt.indexOf('formLogin') !== -1 || txt.indexOf('session_expired') !== -1) {
          window.location.href = './';
          return { session_expired: true };
        }
      }
      var inicio = txt.search(/[\[{]/);
      if (inicio > 0) {
        txt = txt.substring(inicio);
      }
      try {
        var data = JSON.parse(txt);
        if (data && data.session_expired) {
          window.location.href = './';
        }
        return data;
      } catch (err) {
        console.error("Error parseando respuesta JSON:", err);
        return {};
      }
    }
  </script>

  <!-- Librerías de plantilla Spark Admin & Plugins -->
  <script src="vistas/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="vistas/assets/libs/apexcharts/apexcharts.min.js"></script>
  <script src="vistas/assets/libs/flatpickr/flatpickr.min.js"></script>
  <script src="vistas/assets/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
  <script src="vistas/assets/plugins/sweetalert/dist/sweetalert2.all.min.js"></script>
  <script src="vistas/assets/plugins/myDataTable/js/myDataTable.js?v=#v#"></script>

  <!-- Scripts principales SPA -->
  <script src="vistas/assets/js/main.js?v=#v#"></script>
  <script src="vistas/assets/js/dashboard.js?v=#v#"></script>
  <script src="vistas/assets/js/reportes.js?v=#v#"></script>
  <script src="vistas/assets/js/cuenta.js?v=#v#"></script>

</body>
</html>
