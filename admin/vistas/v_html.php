<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ParkingPro | Panel Master Admin</title>

  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="vistas/assets/images/favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="vistas/assets/images/favicon.png">
  <link rel="apple-touch-icon" href="vistas/assets/images/favicon.png">

  <!-- Local Third-Party Libraries (Spark Admin Template) -->
  <link rel="stylesheet" href="vistas/assets/libs/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="vistas/assets/libs/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

  <!-- Main Design System & Custom Stylesheet (Same as /app) -->
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
    html, body {
      overflow-x: hidden;
      max-width: 100vw;
    }
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
    }
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
    }
    .dropdown-menu-profile .dropdown-item:hover {
      background-color: #F1F5F3 !important;
      color: #072F1F !important;
    }
    .dropdown-menu-profile .dropdown-item.text-danger {
      color: #EF4444 !important;
    }
    .dropdown-menu-profile .dropdown-item.text-danger i {
      color: #EF4444 !important;
    }
    .dropdown-menu-profile .dropdown-item.text-danger:hover {
      background-color: #FEE2E2 !important;
      color: #DC2626 !important;
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

  <!-- Sidebar Component (Spark Admin Design - ParkingPro Theme) -->
  <div class="sidebar-wrapper" id="sidebar">
    <!-- Brand Logo / Identity -->
    <a href="javascript:void(0)" class="sidebar-brand cargarVista" carga="v_dashboard" titulo="Dashboard">
      <img src="vistas/assets/images/favicon.png" width="30" height="30" class="rounded-2 me-2 shadow-sm" alt="ParkingPro">
      <span>ParkingAdmin</span>
    </a>

    <!-- Navigation Menu (Spark Admin) -->
    <div class="flex-grow-1 overflow-y-auto">
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
        <div class="sidebar-menu-title">Gestión SaaS</div>
        <ul class="sidebar-menu-list">
          <li class="sidebar-menu-item">
            <a href="javascript:void(0)" class="sidebar-menu-link cargarVista" carga="v_clientes" titulo="Clientes y Negocios" id="bMenuClientes">
              <i class="bi bi-people-fill"></i>
              <span>Estacionamientos</span>
            </a>
          </li>
        </ul>
      </div>

      <div class="sidebar-menu-section">
        <div class="sidebar-menu-title">Finanzas</div>
        <ul class="sidebar-menu-list">
          <li class="sidebar-menu-item">
            <a href="javascript:void(0)" class="sidebar-menu-link cargarVista" carga="v_reportes" titulo="Reportes y Pagos" id="bMenuReportes">
              <i class="bi bi-credit-card-2-front-fill"></i>
              <span>Pagos e Ingresos</span>
            </a>
          </li>
        </ul>
      </div>
      
    </div>

    <!-- Sidebar Profile Card (Dynamic Footer) -->
    <div class="sidebar-profile cargarVista" carga="v_cuenta" titulo="Mi Perfil" style="cursor: pointer;" title="Editar Mi Perfil">
      <div class="sidebar-profile-avatar">
        #palabras#
      </div>
      <div class="sidebar-profile-info">
        <div class="sidebar-profile-name">#nombre#</div>
        <div class="sidebar-profile-email">#correo#</div>
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
          <span class="fw-bold text-dark fs-6" id="topbarTitulo">Dashboard</span>
          <span class="text-muted" style="font-size: 0.75rem;" id="topbarSubtitulo">Administración General SaaS / Estacionamientos</span>
        </div>
      </div>

      <!-- Acciones de barra superior -->
      <div class="navbar-actions">
        <!-- Badge Super Admin -->
        <span class="badge border px-2 py-1 small fw-bold me-2" style="background: rgba(180, 241, 5, 0.2); color: #072F1F; border-color: rgba(7, 47, 31, 0.2) !important;">
          <i class="bi bi-shield-lock-fill me-1" style="color: #072F1F;"></i>Super Admin
        </span>

        <!-- Botón Ver Panel Cliente -->
        <a href="../app/" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3 d-none d-sm-inline-flex align-items-center gap-1 me-2" style="border-color: #072F1F; color: #072F1F;">
          <i class="bi bi-box-arrow-up-right"></i> Panel Cliente
        </a>

        <!-- Botón Pantalla Completa -->
        <button class="navbar-action-btn me-1" aria-label="Pantalla Completa" id="btn-fullscreen">
          <i class="bi bi-arrows-fullscreen"></i>
        </button>

        <!-- Dropdown de Perfil -->
        <div class="dropdown ms-2">
          <button class="navbar-profile-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="profile-dropdown">
            <div class="sidebar-profile-avatar me-2" style="width: 34px; height: 34px; min-width: 34px; font-size: 12px;">
              #palabras#
            </div>
            <span class="navbar-profile-name d-none d-md-inline">#nombre#</span>
            <i class="bi bi-chevron-down navbar-profile-caret"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-profile shadow-sm border-0" aria-labelledby="profile-dropdown">
            <li class="dropdown-header">
              <span class="profile-header-name">#nombre#</span>
              <small class="profile-header-email">#correo#</small>
            </li>
            <li>
              <a class="dropdown-item cargarVista" href="javascript:void(0)" carga="v_cuenta" titulo="Mi Perfil">
                <i class="bi bi-person-gear me-2"></i> Mi Perfil
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
      window.$ = window.jQuery;
      if (typeof jQuery.trim !== 'function') {
        jQuery.trim = function (text) {
          return text == null ? '' : String(text).replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
        };
      }
      if (typeof jQuery.isFunction === 'undefined') {
        jQuery.isFunction = function (obj) {
          return typeof obj === 'function';
        };
      }
    }
  </script>

  <!-- Librerías Bootstrap, Plugins y Gráficas -->
  <script src="vistas/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="vistas/assets/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
  <script src="vistas/assets/plugins/myDataTable/js/myDataTable.js?v=#v#"></script>

  <!-- Scripts principales del Admin -->
  <script src="vistas/assets/js/main.js?v=#v#"></script>
  <script src="vistas/assets/js/dashboard.js?v=#v#"></script>
  <script src="vistas/assets/js/clientes.js?v=#v#"></script>
  <script src="vistas/assets/js/reportes_admin.js?v=#v#"></script>
  <script src="vistas/assets/js/cuenta.js?v=#v#"></script>

</body>

</html>
