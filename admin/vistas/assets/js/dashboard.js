var chartIngresosInstance = null;
var chartRegistrosInstance = null;

function v_dashboard() {
    cargarDashboard();
}

function cargarDashboard() {
    $.ajax({
        url: 'index.php',
        method: 'POST',
        data: {
            metodo: 'consultar',
            accion: 'dashboard'
        },
        dataType: 'json',
        success: function(res) {
            $('#statTotalClientes').text(res.totalClientes || 0);
            $('#statClientesActivos').text(res.totalActivos || 0);
            $('#statIngresos').text('$' + parseFloat(res.totalIngresos || 0).toLocaleString('es-MX', {minimumFractionDigits: 2}));

            // Gráfica de ingresos mensuales
            var ctxI = document.getElementById('graficaIngresos');
            if (ctxI) {
                if (chartIngresosInstance) { chartIngresosInstance.destroy(); }
                var labelsI = (res.ingresosPorMes || []).map(function(r) { return r.Mes; });
                var dataI = (res.ingresosPorMes || []).map(function(r) { return parseFloat(r.Total); });
                chartIngresosInstance = new Chart(ctxI, {
                    type: 'line',
                    data: {
                        labels: labelsI.length ? labelsI : ['Sin pagos'],
                        datasets: [{
                            label: 'Ingresos ($ MXN)',
                            data: dataI.length ? dataI : [0],
                            borderColor: '#072F1F',
                            backgroundColor: 'rgba(180, 241, 5, 0.25)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 3,
                            pointBackgroundColor: '#B4F105',
                            pointBorderColor: '#072F1F',
                            pointRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { callback: function(val) { return '$' + val; } }
                            }
                        }
                    }
                });
            }

            // Gráfica de registros
            var ctxR = document.getElementById('graficaRegistros');
            if (ctxR) {
                if (chartRegistrosInstance) { chartRegistrosInstance.destroy(); }
                var labelsR = (res.registrosPorMes || []).map(function(r) { return r.Mes; });
                var dataR = (res.registrosPorMes || []).map(function(r) { return parseInt(r.Total); });
                chartRegistrosInstance = new Chart(ctxR, {
                    type: 'bar',
                    data: {
                        labels: labelsR.length ? labelsR : ['Sin registros'],
                        datasets: [{
                            label: 'Nuevos Negocios',
                            data: dataR.length ? dataR : [0],
                            backgroundColor: '#072F1F',
                            hoverBackgroundColor: '#B4F105',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                    }
                });
            }

            // Tabla de últimos clientes
            var tbC = $('#tablaUltimosClientes');
            tbC.empty();
            if (!res.ultimosClientes || res.ultimosClientes.length === 0) {
                tbC.html('<tr><td colspan="5" class="text-center text-muted py-3">No hay clientes aún</td></tr>');
            } else {
                res.ultimosClientes.forEach(function(c) {
                    var planBadge = '<span class="badge bg-secondary">' + c.Plan + '</span>';
                    if (c.Plan === 'Ilimitado') planBadge = '<span class="badge bg-warning text-dark fw-bold">∞ Ilimitado</span>';
                    else if (c.Plan === 'Anual') planBadge = '<span class="badge bg-primary">Anual</span>';
                    else if (c.Plan === 'Prueba') planBadge = '<span class="badge bg-info text-dark">Prueba</span>';

                    tbC.append(
                        '<tr>' +
                        '<td class="text-start fw-bold">' + c.Nombre_Negocio + '</td>' +
                        '<td>' + c.Correo + '</td>' +
                        '<td><span class="font-monospace fw-bold">' + c.Codigo_Corto + '</span></td>' +
                        '<td>' + planBadge + '</td>' +
                        '<td><span class="badge bg-success-subtle text-success">' + c.Estatus + '</span></td>' +
                        '</tr>'
                    );
                });
            }

            // Tabla de últimos pagos
            var tbP = $('#tablaUltimosPagos');
            tbP.empty();
            if (!res.ultimosPagos || res.ultimosPagos.length === 0) {
                tbP.html('<tr><td colspan="4" class="text-center text-muted py-3">No hay pagos registrados aún</td></tr>');
            } else {
                res.ultimosPagos.forEach(function(p) {
                    tbP.append(
                        '<tr>' +
                        '<td class="text-start fw-bold">' + p.Nombre_Negocio + '</td>' +
                        '<td>' + p.Plan + '</td>' +
                        '<td class="fw-bold text-success">$' + parseFloat(p.Monto).toFixed(2) + ' MXN</td>' +
                        '<td class="small text-muted">' + p.Fecha_Pago + '</td>' +
                        '</tr>'
                    );
                });
            }
        }
    });
}
