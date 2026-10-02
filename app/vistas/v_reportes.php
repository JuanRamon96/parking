<div class="reportes-content" id="areaReportes">
  
  <!-- Page Header Banner (Spark Admin Template) -->
  <div class="page-header d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h1 class="page-title">Reportes y Cortes</h1>
      <p class="page-subtitle">Auditoría detallada de ingresos por fecha, vehículos registrados y cierres de caja.</p>
    </div>
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-outline-success btn-sm d-flex align-items-center gap-1 cargarVista" carga="v_reportes" titulo="Reportes y Cortes" id="bRecargarReportes">
        <i class="bi bi-arrow-clockwise"></i>
        <span>Actualizar</span>
      </button>
      <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" id="bImprimirReporte">
        <i class="bi bi-printer"></i>
        <span>Imprimir</span>
      </button>
    </div>
  </div>

  <!-- Filtro por Rango de Fechas -->
  <div class="card mb-4 border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-sm-6 col-lg-2">
          <label class="form-label small fw-semibold text-secondary mb-1">Fecha Inicial</label>
          <input type="date" class="form-control" id="fechaInicioReporte">
        </div>
        <div class="col-12 col-sm-6 col-lg-2">
          <label class="form-label small fw-semibold text-secondary mb-1">Fecha Final</label>
          <input type="date" class="form-control" id="fechaFinReporte">
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
          <label class="form-label small fw-semibold text-secondary mb-1">Tablet</label>
          <select class="form-select" id="filtroTabletReporte">
            <option value="">Todas las tablets</option>
          </select>
        </div>
        <div class="col-12 col-sm-6 col-lg-2">
          <button type="button" class="btn text-white w-100 d-flex align-items-center justify-content-center gap-2 py-2" id="bConsultarReportes" style="background-color: #072F1F;">
            <i class="bi bi-search"></i>
            <span>Consultar</span>
          </button>
        </div>
        <div class="col-12 col-sm-6 col-lg-3 text-lg-end">
          <label class="form-label small text-muted mb-1 d-block">Accesos Rápidos</label>
          <div class="btn-group btn-group-sm w-100" role="group">
            <button type="button" class="btn btn-outline-secondary btnRangoRapido" dias="0">Hoy</button>
            <button type="button" class="btn btn-outline-secondary btnRangoRapido" dias="7">7 Días</button>
            <button type="button" class="btn btn-outline-secondary btnRangoRapido" dias="30">30 Días</button>
            <button type="button" class="btn btn-outline-secondary btnRangoRapido" dias="mes">Este Mes</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Resumen de Métricas del Periodo (Spark Admin Stat Cards) -->
  <div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="card card-stat h-100">
        <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
          <span class="stat-label">Ingresos en el Periodo</span>
          <i class="bi bi-cash-stack text-success fs-4"></i>
        </div>
        <div class="stat-value dinero" id="repTotalIngresos">$0.00</div>
        <div class="stat-footer mt-2">
          <span class="stat-change stat-change-up">
            <i class="bi bi-check-circle-fill"></i> Total cobrado efectivo
          </span>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card card-stat h-100">
        <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
          <span class="stat-label">Vehículos Atendidos</span>
          <i class="bi bi-car-front-fill text-primary fs-4"></i>
        </div>
        <div class="stat-value cantidad" id="repTotalVehiculos">0</div>
        <div class="stat-footer mt-2">
          <span class="stat-change stat-change-up">
            <i class="bi bi-arrow-down-left"></i> Entradas registradas
          </span>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card card-stat h-100">
        <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
          <span class="stat-label">Cortes de Caja</span>
          <i class="bi bi-receipt text-info fs-4"></i>
        </div>
        <div class="stat-value cantidad" id="repTotalCortes">0</div>
        <div class="stat-footer mt-2">
          <span class="stat-change text-secondary">
            <i class="bi bi-cash-coin"></i> Cierres de turno
          </span>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="card card-stat h-100">
        <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
          <span class="stat-label">Activos en Patio</span>
          <i class="bi bi-clock-history text-warning fs-4"></i>
        </div>
        <div class="stat-value cantidad" id="repActivosAhora">0</div>
        <div class="stat-footer mt-2">
          <span class="stat-change text-secondary">
            <i class="bi bi-p-square"></i> Pendientes de salida
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Resumen por Tablet -->
  <div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <h5 class="card-title mb-1">Resumen por Tablet</h5>
        <p class="card-subtitle text-muted mb-0">Vehículos, cobros y cortes de cada tablet en el periodo</p>
      </div>
      <span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-tablet me-1"></i>Por dispositivo</span>
    </div>
    <div class="card-body p-3 p-md-4">
      <div class="table-responsive">
        <table class="table table-hover table-bordered text-center align-middle mb-0" style="font-size: 13px;">
          <thead class="table-light">
            <tr>
              <th>Tablet</th>
              <th>Vehículos Registrados</th>
              <th>Salidas Cobradas</th>
              <th>Ingresos</th>
              <th>Cortes</th>
              <th>Diferencia en Cortes</th>
            </tr>
          </thead>
          <tbody id="tbodyResumenTablets">
            <tr><td colspan="6" class="text-muted">Cargando...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Gráfica de Ingresos Diarios en el Periodo (ApexCharts) -->
  <div class="card card-chart mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <h5 class="card-title mb-1">Ventas por Día en el Periodo</h5>
        <p class="card-subtitle text-muted mb-0">Total recaudado día con día en las fechas seleccionadas</p>
      </div>
      <span class="badge bg-light text-dark border px-2 py-1">Ventas diarias</span>
    </div>
    <div class="card-body pt-2 pb-3">
      <div id="chartReportesApex" style="min-height: 260px;"></div>
    </div>
  </div>

  <!-- Pestañas de Tablas con myDataTable -->
  <div class="card">
    <div class="card-header bg-white border-bottom pt-2 px-3 px-md-4 pb-0">
      <ul class="nav nav-tabs card-header-tabs border-0 flex-nowrap overflow-x-auto" id="tabsReportes" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active fw-bold text-dark py-2 py-md-3 px-3 px-md-4 border-0 border-bottom border-success border-3 text-nowrap" id="tabVehiculosBtn" data-bs-toggle="tab" data-bs-target="#panelVehiculos" type="button" role="tab">
            <i class="bi bi-car-front me-2 text-success"></i> Vehículos y Registros
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link fw-bold text-secondary py-2 py-md-3 px-3 px-md-4 border-0 text-nowrap" id="tabCortesBtn" data-bs-toggle="tab" data-bs-target="#panelCortes" type="button" role="tab">
            <i class="bi bi-receipt-cutoff me-2 text-info"></i> Cortes de Caja
          </button>
        </li>
      </ul>
    </div>

    <div class="card-body p-3 p-md-4">
      <div class="tab-content" id="tabContentReportes">

        <!-- Panel 1: Registros de Vehículos -->
        <div class="tab-pane fade show active" id="panelVehiculos" role="tabpanel">
          <div class="table-container">
            <table id="tablaReporteRegistros" class="myDataTable table table-hover table-striped table-bordered text-center align-middle" width="100%" style="font-size: 13px;">
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

        <!-- Panel 2: Cortes de Caja -->
        <div class="tab-pane fade" id="panelCortes" role="tabpanel">
          <div class="table-container">
            <table id="tablaReporteCortes" class="myDataTable table table-hover table-striped table-bordered text-center align-middle" width="100%" style="font-size: 13px;">
              <thead class="table-light">
                <tr>
                  <th orden="ID_Detalle_Caja">Corte</th>
                  <th orden="Caja">Caja</th>
                  <th orden="Fecha_Apertura">Apertura</th>
                  <th orden="Fecha_Cierre">Cierre</th>
                  <th orden="Monto_Apertura">Fondo Apertura</th>
                  <th orden="Ingresos">Ingresos Cobrados</th>
                  <th orden="Monto_Cierre">Monto Cierre</th>
                  <th orden="Diferencia">Diferencia</th>
                  <th orden="No">Detalle</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="9">Cargando cortes...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Modal: Detalle de Corte -->
  <div class="modal fade" id="modalDetalleCorte" tabindex="-1" aria-labelledby="tituloDetalleCorte" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header border-bottom">
          <div>
            <h5 class="modal-title fw-bold" id="tituloDetalleCorte">Detalle del Corte</h5>
            <small class="text-muted" id="subtituloDetalleCorte"></small>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body p-3 p-md-4">
          <div class="row g-3 mb-3" id="resumenDetalleCorte"></div>
          <div id="avisoCuadreCorte" class="mb-3"></div>
          <div class="table-responsive">
            <table class="table table-hover table-bordered text-center align-middle mb-0" style="font-size: 13px;">
              <thead class="table-light">
                <tr>
                  <th>Folio</th>
                  <th>Placas</th>
                  <th>Vehículo</th>
                  <th>Entrada</th>
                  <th>Salida</th>
                  <th>Horas</th>
                  <th>Total</th>
                  <th>Estatus</th>
                </tr>
              </thead>
              <tbody id="tbodyDetalleCorte"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
