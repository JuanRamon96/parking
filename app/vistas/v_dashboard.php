<div class="dashboard-content" id="areaDashboard">
  
  <!-- Page Header Banner (Spark Admin Template) -->
  <div class="page-header d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h1 class="page-title">Dashboard</h1>
      <p class="page-subtitle">Control y métricas en tiempo real de entradas, salidas y caja.</p>
    </div>
    <div>
      <button class="btn btn-outline-success btn-sm d-flex align-items-center gap-1 cargarVista" carga="v_dashboard" titulo="Dashboard" id="bRecargarDash">
        <i class="bi bi-arrow-clockwise"></i>
        <span>Actualizar Datos</span>
      </button>
    </div>
  </div>

  <!-- Quick Info Stat Cards Row (Spark Admin Design) -->
  <div class="row g-4 mb-4">
    <!-- Stat Card 1: Green Alert Banner -->
    <div class="col-lg-4 col-md-6">
      <div class="card alert-green-card h-100">
        <div class="position-relative z-index-2">
          <span class="alert-green-badge">En Línea</span>
          <div class="alert-green-date">Operación Activa</div>
          <div class="alert-green-text">Estacionamiento sincronizado con cobro en tiempo real</div>
        </div>
        <a href="javascript:void(0)" class="alert-green-link z-index-2 cargarVista" carga="v_reportes" titulo="Reportes y Cortes">
          <span>Ver Reportes Detallados</span>
          <i class="bi bi-arrow-right"></i>
        </a>

        <!-- Inline SVG geometric decoration -->
        <svg class="alert-green-bg-shape" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
          <g transform="translate(50,50)">
            <rect x="-6" y="-45" width="12" height="90" rx="6" ry="6" fill="#B4F105" />
            <rect x="-6" y="-45" width="12" height="90" rx="6" ry="6" fill="#B4F105" transform="rotate(60)" />
            <rect x="-6" y="-45" width="12" height="90" rx="6" ry="6" fill="#B4F105" transform="rotate(120)" />
          </g>
        </svg>
      </div>
    </div>

    <!-- Stat Card 2: Ingresos de Hoy -->
    <div class="col-lg-4 col-md-6">
      <div class="card card-stat d-flex flex-column justify-content-between h-100">
        <div>
          <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
            <span class="stat-label">Ingresos de Hoy</span>
            <i class="bi bi-wallet2 text-success fs-4"></i>
          </div>
          <div class="stat-value dinero" id="dashIngresosHoy">$0.00</div>
        </div>
        <div class="stat-footer mt-2">
          <span class="stat-change stat-change-up">
            <i class="bi bi-check-circle-fill"></i> Total cobrado en caja
          </span>
        </div>
      </div>
    </div>

    <!-- Stat Card 3: Vehículos Hoy -->
    <div class="col-lg-4 col-md-12">
      <div class="card card-stat d-flex flex-column justify-content-between h-100">
        <div>
          <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
            <span class="stat-label">Vehículos Registrados Hoy</span>
            <i class="bi bi-car-front-fill text-primary fs-4"></i>
          </div>
          <div class="stat-value cantidad" id="dashVehiculosHoy">0</div>
        </div>
        <div class="stat-footer mt-2">
          <span class="stat-change stat-change-up">
            <i class="bi bi-arrow-down-left"></i> Entradas acumuladas
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Second Row Stats (En Patio, Cortes, Acumulado) -->
  <div class="row g-4 mb-4">
    <div class="col-md-4">
      <div class="card card-stat h-100">
        <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
          <span class="stat-label">En Estacionamiento</span>
          <i class="bi bi-p-square text-warning fs-4"></i>
        </div>
        <div class="stat-value cantidad" id="dashActivosAhora">0</div>
        <div class="stat-footer mt-2">
          <span class="stat-change text-secondary">
            <i class="bi bi-clock-history"></i> Vehículos dentro actualmente
          </span>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card card-stat h-100">
        <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
          <span class="stat-label">Cortes de Caja Hoy</span>
          <i class="bi bi-receipt text-info fs-4"></i>
        </div>
        <div class="stat-value cantidad" id="dashCortesHoy">0</div>
        <div class="stat-footer mt-2">
          <span class="stat-change text-secondary">
            <i class="bi bi-cash-coin"></i> Cierres de turno realizados
          </span>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card card-stat h-100">
        <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
          <span class="stat-label">Total Histórico Cobrado</span>
          <i class="bi bi-graph-up-arrow text-success fs-4"></i>
        </div>
        <div class="stat-value dinero text-success" id="dashTotalHistorico">$0.00</div>
        <div class="stat-footer mt-2">
          <span class="stat-change stat-change-up">
            <i class="bi bi-shield-check"></i> Acumulado general del sistema
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Layout Grid: Chart + System Status -->
  <div class="row g-4 mb-4">
    <!-- Left Area: Revenue Chart (ApexCharts) -->
    <div class="col-lg-8">
      <div class="card card-chart h-100">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <h5 class="card-title mb-1">Evolución de Ingresos</h5>
            <p class="card-subtitle text-muted mb-0">Total cobrado por día en los últimos 14 días</p>
          </div>
          <span class="badge bg-light text-dark border px-2 py-1">Últimos 14 días</span>
        </div>
        <div class="card-body pt-2 pb-3">
          <div id="revenue-chart" style="min-height: 280px;"></div>
        </div>
      </div>
    </div>

    <!-- Right Area: Status Panel -->
    <div class="col-lg-4">
      <div class="card h-100 d-flex flex-column justify-content-between p-4">
        <div>
          <h5 class="card-title fw-bold text-dark mb-1">Estado de la Plataforma</h5>
          <p class="text-muted small mb-4">Servicios vinculados y operativos</p>

          <div class="d-flex flex-column gap-3">
            <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-phone text-success fs-5"></i>
                <span class="fw-semibold small">App Móvil</span>
              </div>
              <span class="badge bg-success text-white">Sincronizada</span>
            </div>

            <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-camera-fill text-warning fs-5"></i>
                <span class="fw-semibold small">Detector de Placas</span>
              </div>
              <span class="badge text-dark" style="background-color: #B4F105;">IA Activa</span>
            </div>

            <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-database-check text-info fs-5"></i>
                <span class="fw-semibold small">Base de Datos</span>
              </div>
              <span class="badge bg-primary text-white">Conectada</span>
            </div>
          </div>
        </div>

        <div class="mt-4 pt-3 border-top">
          <a href="javascript:void(0)" class="btn w-100 text-white d-flex align-items-center justify-content-center gap-2 cargarVista" carga="v_reportes" titulo="Reportes y Cortes" style="background-color: #072F1F;">
            <i class="bi bi-bar-chart-line"></i>
            <span>Ir a Reportes y Cortes</span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Full Width: Recent Vehicles Table with myDataTable -->
  <div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom flex-wrap gap-2">
      <div>
        <h5 class="card-title mb-0 fw-bold">Últimos Vehículos Registrados</h5>
        <small class="text-muted">Actividad reciente en el estacionamiento</small>
      </div>
      <a href="javascript:void(0)" class="btn btn-sm btn-outline-success cargarVista" carga="v_reportes" titulo="Reportes y Cortes">
        Ver reporte completo <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>
    <div class="card-body p-3 p-md-4">
      <div class="table-container">
        <table id="tablaDashboardRegistros" class="myDataTable table table-hover table-striped table-bordered text-center align-middle" width="100%" style="font-size: 13px;">
          <thead class="table-light">
            <tr>
              <th orden="ID_Registro">Folio</th>
              <th orden="Dispositivo">Dispositivo</th>
              <th orden="Placas">Placas</th>
              <th orden="No">Vehículo</th>
              <th orden="Entrada">Entrada</th>
              <th orden="Salida">Salida</th>
              <th orden="No">Horas</th>
              <th orden="Total">Total</th>
              <th orden="Estatus">Estatus</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td colspan="9">Cargando registros...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>
