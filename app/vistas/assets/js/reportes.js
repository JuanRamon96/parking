var chartReportesApexInstance = null;

function v_reportes() {
  var hoy = new Date();
  var anio = hoy.getFullYear();
  var mes = String(hoy.getMonth() + 1).padStart(2, '0');
  var dia = String(hoy.getDate()).padStart(2, '0');

  if (!$('#fechaInicioReporte').val()) {
    $('#fechaInicioReporte').val(anio + '-' + mes + '-01');
  }
  if (!$('#fechaFinReporte').val()) {
    $('#fechaFinReporte').val(anio + '-' + mes + '-' + dia);
  }

  cargarTabletsReporte(function () {
    recargarReportes();
  });
}

/* ------------------------------------------------------------
 * Filtros y utilidades
 * ------------------------------------------------------------ */
function getFiltrosReporte() {
  return {
    fechaInicio: $('#fechaInicioReporte').val(),
    fechaFin: $('#fechaFinReporte').val(),
    dispositivo: $('#filtroTabletReporte').val() || ''
  };
}

function recargarReportes() {
  consultarResumenReportes();
  tablaRegistrosReporte();
  tablaCortesReporte();
}

function escaparHtml(texto) {
  return String(texto == null ? '' : texto)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

// Llena el selector de tablets conservando la opción elegida
function cargarTabletsReporte(callback) {
  var $sel = $('#filtroTabletReporte');
  var actual = $sel.val() || '';

  $.ajax({
    url: 'index.php',
    type: 'POST',
    data: { metodo: 'consultar', accion: 'reportes', tipo: 'tablets' }
  })
  .done(function (res) {
    var data = parseRespuesta(res);
    if (!data || data.session_expired) return;

    var html = '<option value="">Todas las tablets</option>';
    // Cada tablet llega como { id, nombre }: el id es su identificador único
    (data.tablets || []).forEach(function (t) {
      var id = (typeof t === 'object') ? t.id : t;
      var nombre = (typeof t === 'object') ? t.nombre : t;
      html += '<option value="' + escaparHtml(id) + '">' + escaparHtml(nombre) + '</option>';
    });
    $sel.html(html);
    if (actual && $sel.find('option').filter(function () { return this.value === actual; }).length) {
      $sel.val(actual);
    }
  })
  .always(function () {
    if (typeof callback === 'function') callback();
  });
}

/* ------------------------------------------------------------
 * Resumen, KPIs y gráfica
 * ------------------------------------------------------------ */
function consultarResumenReportes() {
  var f = getFiltrosReporte();

  $.ajax({
    url: 'index.php',
    type: 'POST',
    data: {
      metodo: 'consultar',
      accion: 'reportes',
      tipo: 'resumen',
      fechaInicio: f.fechaInicio,
      fechaFin: f.fechaFin,
      dispositivo: f.dispositivo
    },
    beforeSend: function () {
      $('#carga').css('display', 'flex').show();
    }
  })
  .done(function (res) {
    var data = parseRespuesta(res);
    if (!data || data.session_expired) {
      return;
    }

    var kpis = data.kpis || {};
    $('#repTotalIngresos').text(kpis.totalIngresos != null ? kpis.totalIngresos : 0);
    $('#repTotalVehiculos').text(kpis.totalVehiculos != null ? kpis.totalVehiculos : 0);
    $('#repTotalCortes').text(kpis.totalCortes != null ? kpis.totalCortes : 0);
    $('#repActivosAhora').text(kpis.activosAhora != null ? kpis.activosAhora : 0);

    if (data.grafica) {
      pintarGraficaReportesApex(data.grafica);
    }

    pintarResumenTablets(data.porTablet || []);

    if (typeof moneda === 'function') {
      moneda();
    }
  })
  .fail(function () {
    console.error("Error AJAX en resumen de reportes");
  })
  .always(function () {
    $('#carga').hide();
  });
}

function pintarGraficaReportesApex(grafica) {
  var chartEl = document.querySelector("#chartReportesApex");
  if (!chartEl || typeof ApexCharts === 'undefined') return;

  if (chartReportesApexInstance) {
    chartReportesApexInstance.destroy();
  }

  var options = {
    series: [
      {
        name: 'Ingresos ($)',
        type: 'area',
        data: grafica.ingresos || []
      },
      {
        name: 'Vehículos',
        type: 'line',
        data: grafica.vehiculos || []
      }
    ],
    chart: {
      height: 260,
      type: 'line',
      toolbar: { show: false },
      zoom: { enabled: false },
      fontFamily: 'Plus Jakarta Sans, sans-serif'
    },
    colors: ['#072F1F', '#B4F105'],
    stroke: {
      curve: 'smooth',
      width: [3, 2],
      dashArray: [0, 4]
    },
    fill: {
      type: ['gradient', 'solid'],
      gradient: {
        shade: 'light',
        type: 'vertical',
        shadeIntensity: 0.3,
        opacityFrom: 0.45,
        opacityTo: 0.05
      }
    },
    markers: {
      size: [4, 3],
      colors: ['#072F1F', '#B4F105']
    },
    xaxis: {
      categories: grafica.fechas || [],
      labels: {
        style: { colors: '#64748B', fontSize: '11px' }
      },
      axisBorder: { show: false },
      axisTicks: { show: false }
    },
    yaxis: [
      {
        title: { text: 'Ingresos ($)', style: { color: '#072F1F', fontWeight: 600 } },
        labels: {
          formatter: function (val) {
            return '$' + Number(val).toFixed(2);
          },
          style: { colors: '#64748B' }
        }
      },
      {
        opposite: true,
        title: { text: 'Vehículos', style: { color: '#B4F105', fontWeight: 600 } },
        labels: {
          formatter: function (val) {
            return parseInt(val);
          },
          style: { colors: '#64748B' }
        }
      }
    ],
    grid: {
      borderColor: '#E9EFEF',
      strokeDashArray: 4
    },
    legend: {
      position: 'top',
      horizontalAlign: 'right'
    },
    tooltip: {
      y: {
        formatter: function (val, opts) {
          if (opts.seriesIndex === 0) {
            return '$' + Number(val).toLocaleString('es-MX', { minimumFractionDigits: 2 });
          }
          return val + ' vehículos';
        }
      }
    },
    responsive: [
      {
        breakpoint: 768,
        options: {
          chart: {
            height: 240
          },
          legend: {
            position: 'bottom',
            horizontalAlign: 'center',
            offsetY: 2
          },
          yaxis: [
            {
              show: true,
              title: { text: undefined },
              labels: {
                formatter: function (val) {
                  return '$' + Math.round(Number(val));
                },
                style: { colors: '#64748B', fontSize: '10px' }
              }
            },
            {
              show: false
            }
          ],
          xaxis: {
            tickAmount: 5,
            labels: {
              rotate: -30,
              rotateAlways: false,
              style: { colors: '#64748B', fontSize: '10px' }
            }
          }
        }
      }
    ]
  };

  chartReportesApexInstance = new ApexCharts(chartEl, options);
  chartReportesApexInstance.render();
}

function tablaRegistrosReporte() {
  if (typeof ajaxMyDatatable !== 'function') return;
  var $t = $('#tablaReporteRegistros');
  if (!$t || !$t.length) return;

  ajaxMyDatatable({
    table: $t,
    colums: [
      'ID_Registro',
      'Dispositivo',
      'Placas',
      'Tipo',
      'Entrada',
      'Salida',
      'Horas',
      'Total',
      'Estatus'
    ],
    sort: [4, 'desc'],
    url: 'index.php',
    params: {
      metodo: 'consultar',
      accion: 'reportes',
      tipo: 'registros',
      fechaInicio: getFiltrosReporte().fechaInicio,
      fechaFin: getFiltrosReporte().fechaFin,
      dispositivo: getFiltrosReporte().dispositivo
    }
  });
}

function tablaCortesReporte() {
  if (typeof ajaxMyDatatable !== 'function') return;
  var $t = $('#tablaReporteCortes');
  if (!$t || !$t.length) return;

  ajaxMyDatatable({
    table: $t,
    colums: [
      'ID_Detalle_Caja',
      'Caja',
      'Fecha_Apertura',
      'Fecha_Cierre',
      'Monto_Apertura',
      'Ingresos',
      'Monto_Cierre',
      'Diferencia',
      'Acciones'
    ],
    sort: [2, 'desc'],
    url: 'index.php',
    params: {
      metodo: 'consultar',
      accion: 'reportes',
      tipo: 'cortes',
      fechaInicio: getFiltrosReporte().fechaInicio,
      fechaFin: getFiltrosReporte().fechaFin,
      dispositivo: getFiltrosReporte().dispositivo
    }
  });
}

/* ------------------------------------------------------------
 * Resumen por tablet
 * ------------------------------------------------------------ */
function pintarResumenTablets(lista) {
  var $tb = $('#tbodyResumenTablets');
  if (!$tb.length) return;

  if (!lista.length) {
    $tb.html('<tr><td colspan="6" class="text-muted">Sin movimientos en el periodo.</td></tr>');
    return;
  }

  var seleccionada = getFiltrosReporte().dispositivo;
  var tot = { vehiculos: 0, cobrados: 0, ingresos: 0, cortes: 0, diferencia: 0 };
  var html = '';

  lista.forEach(function (t) {
    tot.vehiculos += t.vehiculos;
    tot.cobrados += t.cobrados;
    tot.ingresos += t.ingresos;
    tot.cortes += t.cortes;
    tot.diferencia += t.diferencia;

    var resaltar = (seleccionada && seleccionada === t.id) ? ' class="table-success"' : '';
    var cobradosTxt = t.cobrados + (t.deOtras ? '<br><small class="text-muted">' + t.deOtras + ' entraron por otra tablet</small>' : '');
    html += '<tr' + resaltar + '>' +
      '<td><span class="badge bg-light text-dark border px-2 py-1 text-nowrap"><i class="fa-solid fa-tablet-screen-button me-1 text-primary"></i>' + escaparHtml(t.dispositivo) + '</span></td>' +
      '<td>' + t.vehiculos + '</td>' +
      '<td>' + cobradosTxt + '</td>' +
      '<td><span class="dinero fw-bold text-success">' + Number(t.ingresos).toFixed(2) + '</span></td>' +
      '<td>' + t.cortes + '</td>' +
      '<td>' + badgeDiferenciaJs(t.diferencia) + '</td>' +
      '</tr>';
  });

  if (lista.length > 1) {
    html += '<tr class="table-light fw-bold">' +
      '<td>Total</td>' +
      '<td>' + tot.vehiculos + '</td>' +
      '<td>' + tot.cobrados + '</td>' +
      '<td><span class="dinero">' + tot.ingresos.toFixed(2) + '</span></td>' +
      '<td>' + tot.cortes + '</td>' +
      '<td>' + badgeDiferenciaJs(tot.diferencia) + '</td>' +
      '</tr>';
  }

  $tb.html(html);
  if (typeof moneda === 'function') moneda();
}

function badgeDiferenciaJs(dif) {
  dif = Number(dif) || 0;
  if (dif > 0.004) return '<span class="badge bg-success">+' + formatoMoneda(dif) + '</span>';
  if (dif < -0.004) return '<span class="badge bg-danger">' + formatoMoneda(dif) + '</span>';
  return '<span class="badge bg-secondary">$0.00</span>';
}

/* ------------------------------------------------------------
 * Detalle de un corte
 * ------------------------------------------------------------ */
function verDetalleCorte(idCorte) {
  $.ajax({
    url: 'index.php',
    type: 'POST',
    data: { metodo: 'consultar', accion: 'reportes', tipo: 'detalle_corte', idCorte: idCorte },
    beforeSend: function () {
      $('#carga').css('display', 'flex').show();
    }
  })
  .done(function (res) {
    var data = parseRespuesta(res);
    if (!data || data.session_expired) return;

    if (data.status !== 'success') {
      Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudo consultar el corte.' });
      return;
    }

    var c = data.corte;
    $('#tituloDetalleCorte').text('Corte #' + c.id + ' · ' + c.dispositivo);
    $('#subtituloDetalleCorte').text('Apertura: ' + c.apertura + '  ·  Cierre: ' + (c.cierre || 'Abierto'));

    var tarjeta = function (etiqueta, valor, clase) {
      return '<div class="col-6 col-md-4 col-xl-2">' +
        '<div class="p-2 border rounded-3 bg-light h-100">' +
        '<small class="text-muted d-block">' + etiqueta + '</small>' +
        '<span class="fw-bold ' + (clase || '') + '">' + valor + '</span>' +
        '</div></div>';
    };

    $('#resumenDetalleCorte').html(
      tarjeta('Fondo de apertura', formatoMoneda(c.montoApertura)) +
      tarjeta('Ingresos reportados', formatoMoneda(c.ingresos), 'text-success') +
      tarjeta('Balance esperado', formatoMoneda(c.balance)) +
      tarjeta('Efectivo contado', formatoMoneda(c.montoCierre)) +
      tarjeta('Diferencia de caja', badgeDiferenciaJs(c.diferencia)) +
      tarjeta('Vehículos', data.numCobrados + ' cobrados' +
        (data.numDeOtras ? ' (' + data.numDeOtras + ' de otra tablet)' : '') +
        (data.numPendientes ? ' · ' + data.numPendientes + ' pendientes' : ''))
    );

    // Cuadre: ingresos que reportó la tablet vs. suma de los registros
    var aviso = '';
    var origen = (data.metodo === 'turno')
      ? 'los cobros de esta tablet durante el turno'
      : 'los registros enlazados a este corte';

    if (!data.registros.length) {
      aviso = '<div class="alert alert-secondary mb-0 small"><i class="bi bi-info-circle me-1"></i>No se encontraron registros de vehículos para este corte.</div>';
    } else if (Math.abs(data.diferenciaRegistros) < 0.01) {
      aviso = '<div class="alert alert-success mb-0 small"><i class="bi bi-check-circle-fill me-1"></i>Cuadra: la suma de ' + origen + ' (' + formatoMoneda(data.sumaCobrados) + ') coincide con los ingresos reportados.</div>';
    } else {
      aviso = '<div class="alert alert-warning mb-0 small"><i class="bi bi-exclamation-triangle-fill me-1"></i>No cuadra: la suma de ' + origen + ' es ' + formatoMoneda(data.sumaCobrados) +
        ' y la tablet reportó ' + formatoMoneda(c.ingresos) + ' (diferencia de ' + formatoMoneda(data.diferenciaRegistros) + ').</div>';
    }
    $('#avisoCuadreCorte').html(aviso);

    var filas = '';
    data.registros.forEach(function (r) {
      var vehiculo = escaparHtml(r.tipo) + (r.descripcion ? '<br><small class="text-muted">' + escaparHtml(r.descripcion) + '</small>' : '') +
        (r.origen ? '<br><small class="text-primary"><i class="bi bi-arrow-left-right me-1"></i>Entró por ' + escaparHtml(r.origen) + '</small>' : '');
      filas += '<tr>' +
        '<td><strong>#' + escaparHtml(r.folio) + '</strong></td>' +
        '<td><span class="badge bg-light text-dark border font-monospace">' + escaparHtml(r.placas || 'S/P') + '</span></td>' +
        '<td>' + vehiculo + '</td>' +
        '<td>' + escaparHtml(r.entrada) + '</td>' +
        '<td>' + (r.salida ? escaparHtml(r.salida) : '<span class="badge bg-warning text-dark">En patio</span>') + '</td>' +
        '<td>' + escaparHtml(r.horas || '-') + '</td>' +
        '<td class="fw-bold">' + formatoMoneda(r.total) + '</td>' +
        '<td>' + (r.cobrado ? '<span class="badge bg-success">Completado</span>' : '<span class="badge bg-warning text-dark">Pendiente</span>') + '</td>' +
        '</tr>';
    });
    $('#tbodyDetalleCorte').html(filas || '<tr><td colspan="8" class="text-muted">Sin registros.</td></tr>');

    var modalEl = document.getElementById('modalDetalleCorte');
    if (modalEl && typeof bootstrap !== 'undefined') {
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
  })
  .fail(function () {
    Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo consultar el detalle del corte.' });
  })
  .always(function () {
    $('#carga').hide();
  });
}

jQuery(document).ready(function ($) {
  $(document).on('click', '#bConsultarReportes', function () {
    recargarReportes();
  });

  $(document).on('change', '#filtroTabletReporte', function () {
    recargarReportes();
  });

  $(document).on('click', '.bVerCorte', function (e) {
    e.preventDefault();
    e.stopPropagation();
    verDetalleCorte($(this).attr('attrid'));
  });

  $(document).on('click', '.btnRangoRapido', function () {
    var dias = $(this).attr('dias');
    var hoy = new Date();
    var anio = hoy.getFullYear();
    var mes = String(hoy.getMonth() + 1).padStart(2, '0');
    var dia = String(hoy.getDate()).padStart(2, '0');

    var fechaFin = anio + '-' + mes + '-' + dia;
    var fechaInicio = fechaFin;

    if (dias === '0') {
      fechaInicio = fechaFin;
    } else if (dias === 'mes') {
      fechaInicio = anio + '-' + mes + '-01';
    } else {
      var d = parseInt(dias) || 7;
      var f = new Date();
      f.setDate(f.getDate() - (d - 1));
      fechaInicio = f.toISOString().split('T')[0];
    }

    $('#fechaInicioReporte').val(fechaInicio);
    $('#fechaFinReporte').val(fechaFin);

    recargarReportes();
  });

  $(document).on('click', '#bImprimirReporte', function () {
    window.print();
  });

  $(document).on('shown.bs.tab', '#tabVehiculosBtn', function () {
    $(this).addClass('border-success border-3 text-dark').removeClass('text-secondary');
    $('#tabCortesBtn').removeClass('border-success border-3 text-dark').addClass('text-secondary');
  });

  $(document).on('shown.bs.tab', '#tabCortesBtn', function () {
    $(this).addClass('border-success border-3 text-dark').removeClass('text-secondary');
    $('#tabVehiculosBtn').removeClass('border-success border-3 text-dark').addClass('text-secondary');
  });
});
