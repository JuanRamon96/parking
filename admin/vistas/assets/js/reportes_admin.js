var chartMesReportesInstance = null;
var chartPlanesReportesInstance = null;

function v_reportes() {
    cargarReportesAdmin();
    tablaHistorialPagosAdmin();
}

function cargarReportesAdmin() {
    $.ajax({
        url: 'index.php',
        method: 'POST',
        data: {
            metodo: 'consultar',
            accion: 'reportes_admin',
            tipo: 'resumen'
        },
        dataType: 'json',
        success: function(res) {
            if (!res || typeof res !== 'object') return;

            var total = (res.resumen && res.resumen.Total != null) ? parseFloat(res.resumen.Total) : 0;
            var num = (res.resumen && res.resumen.Num != null) ? parseInt(res.resumen.Num) : 0;

            $('#repTotalIngresos').text('$' + total.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#repTotalTransacciones').text(num.toLocaleString('es-MX'));

            // Gráfica de ingresos mensuales
            var ctxM = document.getElementById('graficaIngresosMes');
            if (ctxM) {
                if (chartMesReportesInstance) { 
                    chartMesReportesInstance.destroy(); 
                }
                var labelsM = (res.ingresosMes || []).map(function(r) { return r.Mes; });
                var dataM = (res.ingresosMes || []).map(function(r) { return parseFloat(r.Total); });
                chartMesReportesInstance = new Chart(ctxM, {
                    type: 'bar',
                    data: {
                        labels: labelsM.length ? labelsM : ['Sin datos'],
                        datasets: [{
                            label: 'Ingresos Mensuales ($ MXN)',
                            data: dataM.length ? dataM : [0],
                            backgroundColor: '#072F1F',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { 
                                    callback: function(val) { return '$' + Number(val).toLocaleString('es-MX'); } 
                                }
                            }
                        }
                    }
                });
            }

            // Gráfica de distribución por plan
            var ctxP = document.getElementById('graficaDistribucionPlanes');
            if (ctxP) {
                if (chartPlanesReportesInstance) { 
                    chartPlanesReportesInstance.destroy(); 
                }
                var labelsP = (res.porPlan || []).map(function(r) { return r.Plan; });
                var dataP = (res.porPlan || []).map(function(r) { return parseInt(r.Total); });
                chartPlanesReportesInstance = new Chart(ctxP, {
                    type: 'doughnut',
                    data: {
                        labels: labelsP.length ? labelsP : ['Sin planes'],
                        datasets: [{
                            data: dataP.length ? dataP : [1],
                            backgroundColor: ['#B4F105', '#072F1F', '#11442B', '#16A34A', '#E2E8F0']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            }
        }
    });
}

function tablaHistorialPagosAdmin() {
    if (typeof ajaxMyDatatable !== 'function') return;
    var $t = $('#tablaHistorialPagosAdmin');
    if (!$t || !$t.length) return;

    ajaxMyDatatable({
        table: $t,
        colums: [
            'ID_PayPal',
            'Negocio',
            'Plan',
            'Monto',
            'Metodo',
            'Fecha'
        ],
        sort: [5, 'desc'],
        url: 'index.php',
        params: {
            metodo: 'consultar',
            accion: 'reportes_admin',
            tipo: 'pagos'
        }
    });
}

function recargarTablaPagosAdmin() {
    if (window.arregloDataTable && window.arregloDataTable['tablaHistorialPagosAdmin']) {
        ajaxMyDatatable(window.arregloDataTable['tablaHistorialPagosAdmin']);
    } else {
        tablaHistorialPagosAdmin();
    }
}
