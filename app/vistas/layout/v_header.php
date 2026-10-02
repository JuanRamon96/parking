<?php
$paginaActual = $_GET['p'] ?? 'dashboard';
$usuarioActual = $_SESSION['user_estacionamiento'] ?? ['Nombre' => 'Administrador', 'Correo' => 'admin@estacionamiento.com'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($tituloPagina ?? 'Panel de Estacionamiento') ?> - ParkingPro</title>

  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="vistas/assets/images/favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="vistas/assets/images/favicon.png">
  <link rel="apple-touch-icon" href="vistas/assets/images/favicon.png">

  <!-- Local Third-Party Libraries (100% Offline Compatible) -->
  <link rel="stylesheet" href="vistas/assets/libs/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="vistas/assets/libs/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="vistas/assets/libs/apexcharts/apexcharts.css">
  <link rel="stylesheet" href="vistas/assets/libs/flatpickr/flatpickr.min.css">

  <!-- Main Design System & Custom Stylesheet -->
  <link rel="stylesheet" href="vistas/assets/css/main.css">
  
  <style>
    .sidebar-brand i { color: #B4F105; font-size: 1.5rem; margin-right: 6px; }
    .badge-vehiculo { font-weight: 700; font-size: 0.75rem; padding: 4px 8px; border-radius: 6px; text-transform: uppercase; }
    .badge-sedan { background-color: #e0f2fe; color: #0369a1; }
    .badge-pickup { background-color: #fef3c7; color: #b45309; }
    .badge-van { background-color: #f3e8ff; color: #7e22ce; }
    .badge-estatus-completado { background-color: #dcfce7; color: #15803d; }
    .badge-estatus-pendiente { background-color: #ffedd5; color: #c2410c; }
    .stat-label-icon { width: 36px; height: 36px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; }
    @media print {
      .sidebar-wrapper, .navbar-custom, .btn-print-hide, .btn, .dropdown { display: none !important; }
      .main-wrapper { margin-left: 0 !important; padding: 0 !important; width: 100% !important; }
      .card { border: 1px solid #ccc !important; box-shadow: none !important; break-inside: avoid; }
    }
  </style>
</head>

<body>

  <!-- ==========================================
       START: Sidebar Component
       ========================================== -->
  <div class="sidebar-wrapper" id="sidebar">
    <!-- Brand Logo / Identity -->
    <a href="?p=dashboard" class="sidebar-brand">
      <img src="vistas/assets/images/favicon.png" width="30" height="30" class="rounded-2 me-2 shadow-sm" alt="ParkingPro">
      <span>Estacionamiento</span>
    </a>

    <!-- Navigation Menu -->
    <div class="flex-grow-1 overflow-y-auto">
      <div class="sidebar-menu-section">
        <div class="sidebar-menu-title">MENÚ PRINCIPAL</div>
        <ul class="sidebar-menu-list">
          <li class="sidebar-menu-item">
            <a href="?p=dashboard" class="sidebar-menu-link <?= $paginaActual === 'dashboard' ? 'active' : '' ?>" title="Panel Principal">
              <i class="bi bi-grid-fill"></i>
              <span>Dashboard</span>
            </a>
          </li>
          <li class="sidebar-menu-item">
            <a href="?p=reportes" class="sidebar-menu-link <?= $paginaActual === 'reportes' ? 'active' : '' ?>" title="Reportes y Ventas">
              <i class="bi bi-graph-up-arrow"></i>
              <span>Reportes de Ventas</span>
            </a>
          </li>
        </ul>
      </div>

      <div class="sidebar-menu-section">
        <div class="sidebar-menu-title">CUENTA Y SISTEMA</div>
        <ul class="sidebar-menu-list">
          <li class="sidebar-menu-item">
            <a href="?p=perfil" class="sidebar-menu-link <?= $paginaActual === 'perfil' ? 'active' : '' ?>" title="Mi Perfil">
              <i class="bi bi-person-fill-gear"></i>
              <span>Mi Perfil</span>
            </a>
          </li>
          <li class="sidebar-menu-item">
            <a href="javascript:void(0)" class="sidebar-menu-link text-danger bCerrarSe" title="Cerrar Sesión">
              <i class="bi bi-box-arrow-right"></i>
              <span>Cerrar Sesión</span>
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Sidebar Profile Card (Footer) -->
    <div class="sidebar-profile">
      <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold; font-size: 1.1rem;">
        <?= strtoupper(substr($usuarioActual['Nombre'] ?? 'A', 0, 1)) ?>
      </div>
      <div class="sidebar-profile-info text-truncate">
        <div class="sidebar-profile-name"><?= htmlspecialchars($usuarioActual['Nombre'] ?? 'Administrador') ?></div>
        <div class="sidebar-profile-email text-truncate"><?= htmlspecialchars($usuarioActual['Correo'] ?? 'admin@estacionamiento.com') ?></div>
      </div>
    </div>
  </div>
  <!-- ==========================================
       END: Sidebar Component
       ========================================== -->

  <!-- ==========================================
       START: Main Content Area
       ========================================== -->
  <div class="main-wrapper">

    <!-- START: Top Navbar Component -->
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

        <span class="fs-5 fw-bold d-none d-sm-inline text-dark">
          <?= htmlspecialchars($tituloPagina ?? 'Panel de Estacionamiento') ?>
        </span>
      </div>

      <div class="navbar-right d-flex align-items-center">
        <!-- Enlace rápido a Reportes -->
        <a href="?p=reportes" class="btn btn-sm btn-outline-success me-3 d-none d-md-inline-flex align-items-center">
          <i class="bi bi-calendar-range me-1"></i> Ver Reportes
        </a>

        <!-- Profile Dropdown -->
        <div class="dropdown ms-2">
          <button class="navbar-profile-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="profile-dropdown">
            <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center me-2" style="width: 34px; height: 34px; font-weight: bold;">
              <?= strtoupper(substr($usuarioActual['Nombre'] ?? 'A', 0, 1)) ?>
            </div>
            <span class="navbar-profile-name d-none d-md-inline"><?= htmlspecialchars($usuarioActual['Nombre'] ?? 'Administrador') ?></span>
            <i class="bi bi-chevron-down navbar-profile-caret"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-profile" aria-labelledby="profile-dropdown">
            <li class="dropdown-header">¡Hola, <?= htmlspecialchars($usuarioActual['Nombre'] ?? 'Admin') ?>!</li>
            <li><a class="dropdown-item cargarVista" href="javascript:void(0)" carga="v_cuenta" titulo="Mi Perfil"><i class="bi bi-person me-2"></i> Mi Perfil</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger bCerrarSe" href="javascript:void(0)" id="bCerrarSe"><i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión</a></li>
          </ul>
        </div>
      </div>
    </header>
    <!-- END: Top Navbar Component -->
