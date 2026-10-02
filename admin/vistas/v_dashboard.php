<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h3 class="fw-bold mb-1">Métricas Generales</h3>
    <p class="text-muted small mb-0">Resumen operativo y financiero de la plataforma de estacionamientos</p>
  </div>
  <button class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="cargarDashboard()" title="Actualizar Datos">
    <i class="fa-solid fa-arrows-rotate me-1"></i> Actualizar
  </button>
</div>

<!-- Tarjetas KPI -->
<div class="row g-3 mb-4">
  <div class="col-12 col-md-4">
    <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="text-muted small fw-bold text-uppercase">Total Estacionamientos</span>
        <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3">
          <i class="fa-solid fa-square-parking fs-5"></i>
        </div>
      </div>
      <div class="display-6 fw-bold text-dark mb-1" id="statTotalClientes">0</div>
      <small class="text-muted"><i class="fa-solid fa-circle-check text-success me-1"></i>Negocios registrados</small>
    </div>
  </div>

  <div class="col-12 col-md-4">
    <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="text-muted small fw-bold text-uppercase">Cuentas Activas</span>
        <div class="p-2 bg-success bg-opacity-10 text-success rounded-3">
          <i class="fa-solid fa-bolt fs-5"></i>
        </div>
      </div>
      <div class="display-6 fw-bold text-success mb-1" id="statClientesActivos">0</div>
      <small class="text-muted"><i class="fa-solid fa-clock text-info me-1"></i>En periodo activo o prueba</small>
    </div>
  </div>

  <div class="col-12 col-md-4">
    <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="text-muted small fw-bold text-uppercase">Ingresos Totales (SaaS)</span>
        <div class="p-2 bg-warning bg-opacity-10 text-warning-emphasis rounded-3">
          <i class="fa-solid fa-dollar-sign fs-5"></i>
        </div>
      </div>
      <div class="display-6 fw-bold text-dark mb-1" id="statIngresos">$0.00</div>
      <small class="text-muted"><i class="fa-brands fa-paypal text-primary me-1"></i>Cobros procesados vía PayPal</small>
    </div>
  </div>
</div>

<!-- Gráficas -->
<div class="row g-3 mb-4">
  <div class="col-12 col-lg-7">
    <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
      <h6 class="fw-bold mb-3"><i class="fa-solid fa-chart-line text-primary me-2"></i>Ingresos por Mes (MXN)</h6>
      <div style="height: 260px; position: relative;">
        <canvas id="graficaIngresos"></canvas>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-5">
    <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
      <h6 class="fw-bold mb-3"><i class="fa-solid fa-chart-bar text-success me-2"></i>Nuevos Registros por Mes</h6>
      <div style="height: 260px; position: relative;">
        <canvas id="graficaRegistros"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Tablas de Actividad Reciente -->
<div class="row g-3">
  <!-- Últimos Clientes -->
  <div class="col-12 col-lg-6">
    <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="fw-bold mb-0"><i class="fa-solid fa-users text-secondary me-2"></i>Últimos Clientes Registrados</h6>
        <a href="javascript:void(0)" class="small text-primary text-decoration-none fw-semibold cargarVista" carga="v_clientes" titulo="Clientes">Ver todos <i class="fa-solid fa-arrow-right"></i></a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle text-center small mb-0">
          <thead class="table-light">
            <tr>
              <th class="text-start">Negocio</th>
              <th>Correo</th>
              <th>Código</th>
              <th>Plan</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody id="tablaUltimosClientes">
            <tr><td colspan="5" class="text-muted py-3">Cargando...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Últimos Pagos -->
  <div class="col-12 col-lg-6">
    <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="fw-bold mb-0"><i class="fa-solid fa-credit-card text-success me-2"></i>Últimos Pagos PayPal</h6>
        <a href="javascript:void(0)" class="small text-primary text-decoration-none fw-semibold cargarVista" carga="v_reportes" titulo="Reportes">Ver reportes <i class="fa-solid fa-arrow-right"></i></a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle text-center small mb-0">
          <thead class="table-light">
            <tr>
              <th class="text-start">Estacionamiento</th>
              <th>Plan</th>
              <th>Monto</th>
              <th>Fecha</th>
            </tr>
          </thead>
          <tbody id="tablaUltimosPagos">
            <tr><td colspan="4" class="text-muted py-3">Cargando...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
