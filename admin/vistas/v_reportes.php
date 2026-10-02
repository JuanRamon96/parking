<div class="reportes-admin-content" id="areaReportesAdmin">

  <!-- Page Header Banner (Spark Admin Template) -->
  <div class="page-header d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h1 class="page-title">Reportes y Pagos</h1>
      <p class="page-subtitle">Auditoría financiera de ingresos por suscripción, métricas históricas y transacciones PayPal.</p>
    </div>
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-outline-success btn-sm d-flex align-items-center gap-1 cargarVista" carga="v_reportes" titulo="Reportes y Pagos" id="bRecargarReportes" style="border-color: #072F1F; color: #072F1F;">
        <i class="bi bi-arrow-clockwise"></i>
        <span>Actualizar</span>
      </button>
    </div>
  </div>

  <!-- Tarjetas KPI -->
  <div class="row g-3 mb-4">
    <div class="col-12 col-md-6">
      <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small fw-bold text-uppercase">Total Recaudado</span>
          <div class="p-2 bg-success bg-opacity-10 text-success rounded-3">
            <i class="fa-solid fa-money-bill-wave fs-5"></i>
          </div>
        </div>
        <div class="display-6 fw-bold text-success mb-1 dinero" id="repTotalIngresos">$0.00</div>
        <small class="text-muted">Monto total histórico de suscripciones cobradas</small>
      </div>
    </div>

    <div class="col-12 col-md-6">
      <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-muted small fw-bold text-uppercase">Total de Transacciones</span>
          <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3">
            <i class="fa-brands fa-paypal fs-5"></i>
          </div>
        </div>
        <div class="display-6 fw-bold text-dark mb-1 cantidad" id="repTotalTransacciones">0</div>
        <small class="text-muted">Pagos exitosos completados en la plataforma</small>
      </div>
    </div>
  </div>

  <!-- Gráficas de Análisis -->
  <div class="row g-3 mb-4">
    <div class="col-12 col-lg-8">
      <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
        <h6 class="fw-bold mb-3"><i class="fa-solid fa-chart-column text-primary me-2"></i>Facturación por Mes (MXN)</h6>
        <div style="height: 260px; position: relative;">
          <canvas id="graficaIngresosMes"></canvas>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-4">
      <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
        <h6 class="fw-bold mb-3"><i class="fa-solid fa-chart-pie text-warning me-2"></i>Distribución por Plan</h6>
        <div style="height: 260px; position: relative;">
          <canvas id="graficaDistribucionPlanes"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabla Historial Detallado de Pagos con myDataTable -->
  <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
    <div class="mb-3">
      <h5 class="fw-bold mb-1"><i class="bi bi-receipt-cutoff text-success me-2"></i>Historial Detallado de Pagos</h5>
      <p class="text-muted small mb-0">Auditoría completa de transacciones y suscripciones cobradas vía PayPal</p>
    </div>
    
    <div class="table-container">
      <table id="tablaHistorialPagosAdmin" class="myDataTable table table-hover table-striped table-bordered text-center align-middle" width="100%" style="font-size: 13px;">
        <thead class="table-light">
          <tr>
            <th orden="ID_PayPal">ID PayPal / Ref</th>
            <th orden="Negocio" class="text-start">Estacionamiento / Correo</th>
            <th orden="Plan">Plan</th>
            <th orden="Monto">Monto</th>
            <th orden="Metodo">Método</th>
            <th orden="Fecha">Fecha de Pago</th>
          </tr>
        </thead>
        <tbody>
          <tr><td colspan="6">Cargando pagos...</td></tr>
        </tbody>
      </table>
    </div>
  </div>

</div>
