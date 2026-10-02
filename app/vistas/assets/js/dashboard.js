var revenueChartInstance = null;

function v_dashboard() {
  $.ajax({
    url: 'index.php',
    type: 'POST',
    data: 'metodo=consultar&accion=dashboard',
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
    $('#dashIngresosHoy').text(kpis.ingresosHoy != null ? kpis.ingresosHoy : 0);
    $('#dashVehiculosHoy').text(kpis.vehiculosHoy != null ? kpis.vehiculosHoy : 0);
    $('#dashActivosAhora').text(kpis.activosAhora != null ? kpis.activosAhora : 0);
    $('#dashCortesHoy').text(kpis.cortesHoy != null ? kpis.cortesHoy : 0);
    $('#dashTotalHistorico').text(kpis.totalHistorico != null ? kpis.totalHistorico : 0);

    // Pintar gráfica ApexCharts (estilo Spark Admin)
    if (data.grafica) {
      pintarGraficaDashboard(data.grafica);
    }

    if (typeof moneda === 'function') {
      moneda();
    }
  })
  .fail(function () {
    console.error("Error AJAX en dashboard");
  })
  .always(function () {
    $('#carga').hide();
  });

  // Inicializar tabla interactiva con myDataTable
  tablaDashboardRegistros();
}

function pintarGraficaDashboard(grafica) {
  var chartEl = document.querySelector("#revenue-chart");
  if (!chartEl || typeof ApexCharts === 'undefined') return;

  if (revenueChartInstance) {
    revenueChartInstance.destroy();
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
      height: 280,
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
      colors: ['#072F1F', '#B4F105'],
      strokeWidth: 2
    },
    xaxis: {
      categories: grafica.dias || [],
      labels: {
        style: { colors: '#64748B', fontSize: '12px' }
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
            height: 250
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

  revenueChartInstance = new ApexCharts(chartEl, options);
  revenueChartInstance.render();
}

function tablaDashboardRegistros() {
  if (typeof ajaxMyDatatable !== 'function') return;
  var $t = $('#tablaDashboardRegistros');
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
      accion: 'dashboard',
      tipo: 'tabla'
    }
  });
}
