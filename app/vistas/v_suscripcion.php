<div class="container-fluid px-0 pb-5" style="margin-bottom: 80px;">
  
  <!-- Banner de Estado de Suscripción Actual -->
  <div class="row g-4 mb-4">
    <div class="col-12">
      <div class="card border-0 shadow rounded-4 overflow-hidden" id="bannerEstado" style="background: linear-gradient(135deg, #0F172A 0%, #1E293B 60%, #0369A1 100%);">
        <div class="card-body p-4 p-md-5">
          <div class="row align-items-center g-4">
            
            <div class="col-12 col-md-8">
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill" id="badgePlanActual" style="font-size: 0.8rem;">
                  <i class="bi bi-clock-history me-1"></i> Cargando Plan...
                </span>
                <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-25 px-3 py-1 rounded-pill small">
                  <i class="bi bi-infinity me-1"></i> Tablets Ilimitadas
                </span>
              </div>
              <h2 class="fw-bold mb-1" id="tituloEstadoSub" style="color: #FFFFFF !important;">Verificando tu suscripción...</h2>
              <p class="mb-0 small" id="subtituloEstadoSub" style="color: #CBD5E1 !important; font-size: 0.95rem;">
                Cargando los detalles de tu membresía de estacionamiento en la nube...
              </p>
            </div>

            <div class="col-12 col-md-4 text-md-end">
              <div class="p-3 rounded-4 d-inline-block text-center border shadow-sm" style="background: rgba(255, 255, 255, 0.08); border-color: rgba(255, 255, 255, 0.18) !important; min-width: 140px;">
                <span class="d-block small text-uppercase fw-bold tracking-wide" style="font-size: 0.72rem; color: #94A3B8 !important;">Vigencia</span>
                <strong class="fs-4 d-block mt-1" id="txtDiasRestantes" style="color: #38BDF8 !important;">--</strong>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Encabezado de Planes -->
  <div class="text-center mt-4 mb-4">
    <h3 class="fw-bold mb-1 text-dark">Elige o Renueva tu Plan</h3>
    <p class="text-muted small mb-0">Todos los planes incluyen tablets ilimitadas, soporte técnico y sincronización en la nube.</p>
  </div>

  <!-- Catálogo de 3 Tarjetas de Planes -->
  <div class="row g-4 justify-content-center align-items-stretch pt-3 pb-3">

    <!-- Tarjeta 1: Plan Mensual ($250 MXN) -->
    <div class="col-12 col-md-4 pt-3">
      <div class="card border-0 shadow-sm rounded-4 h-100 p-4 text-center d-flex flex-column justify-content-between" style="background: #FFFFFF; margin-top: 14px;">
        <div>
          <span class="badge bg-secondary-subtle text-secondary px-3 py-1 rounded-pill fw-bold small mb-3">FLEXIBLE</span>
          <h4 class="fw-bold mb-1 text-dark">Plan Mensual</h4>
          <p class="text-muted small mb-3">Ideal para iniciar mes a mes</p>
          <div class="display-5 fw-bold text-dark mb-1">$250<span class="fs-6 fw-normal text-muted">/mes</span></div>
          <small class="text-muted d-block mb-4">Pesos Mexicanos (MXN)</small>

          <ul class="list-unstyled text-start small mb-4 d-flex flex-column gap-2 text-secondary">
            <li><i class="bi bi-check-circle-fill text-success me-2 fs-6"></i><strong>Tablets ilimitadas</strong></li>
            <li><i class="bi bi-check-circle-fill text-success me-2 fs-6"></i>Ingresos y salidas en tiempo real</li>
            <li><i class="bi bi-check-circle-fill text-success me-2 fs-6"></i>Cortes de caja y tickets térmicos</li>
            <li><i class="bi bi-check-circle-fill text-success me-2 fs-6"></i>Reportes y gráficas en la nube</li>
          </ul>
        </div>
        <div class="pt-3">
          <button class="btn btn-outline-dark fw-bold py-2.5 rounded-pill w-100 shadow-sm" onclick="iniciarPago('Mensual', 250)">
            Pagar $250 MXN / Mes
          </button>
        </div>
      </div>
    </div>

    <!-- Tarjeta 2: Plan Anual ($2,500 MXN) - DESTACADO CON SUFICIENTE MARGIN -->
    <div class="col-12 col-md-4 pt-3" style="overflow: visible !important;">
      <div class="card border-2 border-primary shadow rounded-4 h-100 p-4 text-center position-relative d-flex flex-column justify-content-between" style="background: #FFFFFF; overflow: visible !important; margin-top: 14px;">
        <div style="position: absolute; top: -16px; left: 0; right: 0; text-align: center; z-index: 50; overflow: visible !important; pointer-events: none;">
          <span class="badge bg-primary text-white px-3 py-2 rounded-pill fw-bold text-uppercase shadow" style="font-size: 0.75rem; letter-spacing: 0.5px; display: inline-block; pointer-events: auto;">
            ⭐ MÁS POPULAR · AHORRA 2 MESES
          </span>
        </div>
        <div class="pt-2">
          <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-bold small mb-3">ANUAL RECOMENDADO</span>
          <h4 class="fw-bold mb-1 text-primary">Plan Anual</h4>
          <p class="text-muted small mb-3">365 días de servicio ininterrumpido</p>
          <div class="display-5 fw-bold text-primary mb-1">$2,500<span class="fs-6 fw-normal text-muted">/año</span></div>
          <small class="text-muted d-block mb-4">Equivale a solo $208/mes (Ahorras $500)</small>

          <ul class="list-unstyled text-start small mb-4 d-flex flex-column gap-2 text-secondary">
            <li><i class="bi bi-check-circle-fill text-primary me-2 fs-6"></i><strong>Tablets ilimitadas</strong></li>
            <li><i class="bi bi-check-circle-fill text-primary me-2 fs-6"></i><strong>Soporte prioritario</strong></li>
            <li><i class="bi bi-check-circle-fill text-primary me-2 fs-6"></i>Actualizaciones de por vida</li>
            <li><i class="bi bi-check-circle-fill text-primary me-2 fs-6"></i>Respaldos automáticos de datos</li>
          </ul>
        </div>
        <div class="pt-3">
          <button class="btn btn-primary fw-bold py-2.5 rounded-pill w-100 shadow" onclick="iniciarPago('Anual', 2500)">
            Pagar $2,500 MXN / Año
          </button>
        </div>
      </div>
    </div>

    <!-- Tarjeta 3: Plan Ilimitado ($4,999 MXN) -->
    <div class="col-12 col-md-4 pt-3">
      <div class="card border-0 shadow-sm rounded-4 h-100 p-4 text-center d-flex flex-column justify-content-between" style="background: linear-gradient(180deg, #FFFFFF 0%, #F8FAFC 100%); margin-top: 14px;">
        <div>
          <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-1 rounded-pill fw-bold small mb-3">PAGO ÚNICO</span>
          <h4 class="fw-bold mb-1 text-dark">Plan Ilimitado</h4>
          <p class="text-muted small mb-3">Sin mensualidades nunca más</p>
          <div class="display-5 fw-bold text-dark mb-1">$4,999<span class="fs-6 fw-normal text-muted">/vitalicio</span></div>
          <small class="text-muted d-block mb-4">Un solo pago de por vida</small>

          <ul class="list-unstyled text-start small mb-4 d-flex flex-column gap-2 text-secondary">
            <li><i class="bi bi-infinity text-warning fs-6 me-2"></i><strong>Acceso de por vida sin vencimiento</strong></li>
            <li><i class="bi bi-check-circle-fill text-success me-2 fs-6"></i><strong>Tablets ilimitadas</strong></li>
            <li><i class="bi bi-check-circle-fill text-success me-2 fs-6"></i>Cero cobros recurrentes</li>
            <li><i class="bi bi-check-circle-fill text-success me-2 fs-6"></i>Todas las funciones incluidas</li>
          </ul>
        </div>
        <div class="pt-3">
          <button class="btn btn-dark fw-bold py-2.5 rounded-pill w-100 shadow-sm" onclick="iniciarPago('Ilimitado', 4999)">
            Adquirir Plan Ilimitado
          </button>
        </div>
      </div>
    </div>

  </div>

  <!-- Historial de Pagos -->
  <div class="row mt-4">
    <div class="col-12">
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
          <h5 class="fw-bold mb-1"><i class="bi bi-receipt text-primary me-2"></i>Historial de Pagos y Facturación</h5>
        </div>
        <div class="card-body px-4 py-2">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tablaPagosHistorial" style="font-size: 0.85rem;">
              <thead class="table-light">
                <tr>
                  <th>ID Transacción</th>
                  <th>Plan Adquirido</th>
                  <th>Monto</th>
                  <th>Método</th>
                  <th>Fecha de Pago</th>
                  <th>Estatus</th>
                </tr>
              </thead>
              <tbody>
                <tr><td colspan="6" class="text-center py-4 text-muted">Cargando pagos...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Modal para Pago con PayPal -->
<div class="modal fade" id="modalPagoPayPal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-0 pb-0 px-4 pt-4">
        <div>
          <h5 class="modal-title fw-bold">Completar Pago Seguro</h5>
          <p class="text-muted small mb-0" id="modalSubInfo">Plan seleccionado</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body px-4 py-3">
        <div class="p-3 bg-light rounded-3 text-center mb-3">
          <span class="text-muted small d-block">Total a pagar:</span>
          <strong class="fs-3 text-dark" id="modalMontoTexto">$0.00 MXN</strong>
        </div>

        <div id="paypal-button-container" class="mt-3"></div>
      </div>
    </div>
  </div>
</div>

<!-- PayPal SDK -->
<script src="https://www.paypal.com/sdk/js?client-id=ARSS_P46LzNOJiIj32nIVdBiCqiWZJiwtlA8AU3q9e6nZbVDEI4R1C8mciFIxsM1xsgcQz6rNPiElJsC&currency=MXN"></script>

<script>
  var planSeleccionado = '';
  var montoSeleccionado = 0;
  var modalPagoInstance = null;

  // Función SPA invocada automáticamente por main.js al cargar esta vista
  function v_suscripcion() {
    cargarEstadoSuscripcion();
  }

  // Ejecución inmediata garantizada
  cargarEstadoSuscripcion();

  $(document).ready(function() {
    var modalEl = document.getElementById('modalPagoPayPal');
    if (modalEl && typeof bootstrap !== 'undefined') {
      modalPagoInstance = new bootstrap.Modal(modalEl);
    }
  });

  function cargarEstadoSuscripcion() {
    $.ajax({
      url: 'index.php',
      method: 'POST',
      dataType: 'json',
      data: {
        metodo: 'consultar',
        accion: 'suscripcion'
      },
      success: function(res) {
        if (res && res.status === 'success') {
          // Renderizar Banner
          if (res.esIlimitado) {
            $('#badgePlanActual')
              .attr('class', 'badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill')
              .html('<i class="bi bi-infinity me-1"></i> PLAN ILIMITADO VITALICIO');
            $('#tituloEstadoSub').text('Membresía Activa de por Vida');
            $('#subtituloEstadoSub').text('Tu estacionamiento cuenta con acceso ilimitado permanente sin cobros mensuales.');
            $('#txtDiasRestantes').html('<i class="bi bi-infinity text-warning"></i>');
          } else if (res.estatus === 'Vencido' || res.diasRestantes <= 0) {
            $('#badgePlanActual')
              .attr('class', 'badge bg-danger text-white fw-bold px-3 py-1 rounded-pill')
              .html('<i class="bi bi-exclamation-triangle-fill me-1"></i> SUSCRIPCIÓN VENCIDA');
            $('#tituloEstadoSub').text('Tu servicio ha finalizado');
            $('#subtituloEstadoSub').text('Renueva tu plan ahora para seguir operando tus tablets y sincronizar registros.');
            $('#txtDiasRestantes').text('Vencido').attr('style', 'color: #EF4444 !important;');
          } else if (res.plan === 'Prueba') {
            $('#badgePlanActual')
              .attr('class', 'badge bg-info text-dark fw-bold px-3 py-1 rounded-pill')
              .html('<i class="bi bi-gift-fill me-1"></i> PERIODO DE PRUEBA GRATIS');
            $('#tituloEstadoSub').text('Prueba Gratuita en Curso');
            $('#subtituloEstadoSub').text('Aprovecha todos los beneficios de tu cuenta sin costo adicional.');
            $('#txtDiasRestantes').text(res.diasRestantes + ' días');
          } else {
            $('#badgePlanActual')
              .attr('class', 'badge bg-success text-white fw-bold px-3 py-1 rounded-pill')
              .html('<i class="bi bi-check-circle-fill me-1"></i> PLAN ' + String(res.plan).toUpperCase() + ' ACTIVO');
            $('#tituloEstadoSub').text('Membresía ' + res.plan + ' Vigente');
            $('#subtituloEstadoSub').text('Vence el ' + res.fechaVence);
            $('#txtDiasRestantes').text(res.diasRestantes + ' días');
          }

          // Renderizar Historial de Pagos
          var tbody = $('#tablaPagosHistorial tbody');
          tbody.empty();

          if (!res.pagos || res.pagos.length === 0) {
            tbody.html('<tr><td colspan="6" class="text-center py-4 text-muted">Aún no se han registrado pagos en esta cuenta.</td></tr>');
          } else {
            res.pagos.forEach(function(p) {
              tbody.append(
                '<tr>' +
                '<td><code>' + (p.ID_Transaccion_PayPal || p.ID_Pago) + '</code></td>' +
                '<td><strong>' + p.Plan + '</strong></td>' +
                '<td class="fw-bold text-success">$' + parseFloat(p.Monto).toFixed(2) + ' MXN</td>' +
                '<td><span class="badge bg-light text-dark border">' + p.Metodo_Pago + '</span></td>' +
                '<td>' + p.Fecha_Pago + '</td>' +
                '<td><span class="badge bg-success">Completado</span></td>' +
                '</tr>'
              );
            });
          }
        }
      },
      error: function(xhr, status, error) {
        console.error('Error al consultar suscripcion:', error);
      }
    });
  }

  function iniciarPago(plan, monto) {
    planSeleccionado = plan;
    montoSeleccionado = monto;

    $('#modalSubInfo').text('Plan ' + plan + ' de Estacionamiento');
    $('#modalMontoTexto').text('$' + monto.toLocaleString('es-MX') + '.00 MXN');

    // Limpiar contenedor previo de PayPal
    $('#paypal-button-container').empty();

    if (typeof paypal !== 'undefined') {
      paypal.Buttons({
        createOrder: function(data, actions) {
          return actions.order.create({
            purchase_units: [{
              description: 'Suscripción ' + planSeleccionado + ' - Sistema de Estacionamiento',
              amount: {
                currency_code: 'MXN',
                value: montoSeleccionado.toString()
              }
            }]
          });
        },
        onApprove: function(data, actions) {
          return actions.order.capture().then(function(detalles) {
            // Guardar pago en el servidor
            $.ajax({
              url: 'index.php',
              method: 'POST',
              dataType: 'json',
              data: {
                metodo: 'insertar',
                accion: 'suscripcion',
                plan: planSeleccionado,
                orderId: data.orderID,
                detalles: detalles
              },
              success: function(resp) {
                if (modalPagoInstance) modalPagoInstance.hide();
                if (resp && resp.status === 'success') {
                  Swal.fire({
                    icon: 'success',
                    title: '¡Pago completado!',
                    text: 'Tu plan ' + planSeleccionado + ' ha sido activado.'
                  });
                  cargarEstadoSuscripcion();
                } else {
                  Swal.fire({
                    icon: 'warning',
                    title: 'No se pudo activar el plan',
                    html: (resp && resp.message ? resp.message : 'El pago no pudo verificarse.') +
                          '<br><br>Número de orden de PayPal: <b>' + data.orderID + '</b><br>Guárdalo y contáctanos para ayudarte.'
                  });
                }
              },
              error: function() {
                if (modalPagoInstance) modalPagoInstance.hide();
                Swal.fire({
                  icon: 'error',
                  title: 'Error de conexión',
                  html: 'Tu pago se realizó en PayPal pero no pudimos confirmarlo en el sistema.<br><br>Número de orden: <b>' + data.orderID + '</b><br>Contáctanos para activarlo.'
                });
              }
            });
          });
        },
        onError: function(err) {
          Swal.fire({
            icon: 'error',
            title: 'Error con PayPal',
            text: 'Ocurrió un error al procesar el pago. No se realizó ningún cargo.'
          });
        }
      }).render('#paypal-button-container');
    }

    if (!modalPagoInstance) {
      var modalEl = document.getElementById('modalPagoPayPal');
      if (modalEl && typeof bootstrap !== 'undefined') {
        modalPagoInstance = new bootstrap.Modal(modalEl);
      }
    }
    if (modalPagoInstance) {
      modalPagoInstance.show();
    }
  }
</script>
