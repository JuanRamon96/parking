function v_clientes() {
    tablaClientes();
}

function tablaClientes() {
    if (typeof ajaxMyDatatable !== 'function') return;
    var $t = $('#tablaClientes');
    if (!$t || !$t.length) return;

    ajaxMyDatatable({
        table: $t,
        colums: [
            'Fecha',
            'Negocio',
            'Codigo',
            'BD',
            'Plan',
            'Estatus',
            'Vence',
            'Acciones'
        ],
        sort: [0, 'desc'],
        url: 'index.php',
        params: {
            metodo: 'consultar',
            accion: 'clientes'
        }
    });
}

function recargarTablaClientes() {
    if (window.arregloDataTable && window.arregloDataTable['tablaClientes']) {
        ajaxMyDatatable(window.arregloDataTable['tablaClientes']);
    } else {
        tablaClientes();
    }
}

// Cambiar / Extender plan de cliente
$(document).off('click', '.bEditarPlan').on('click', '.bEditarPlan', function() {
    var id = $(this).attr('attrID');
    var nombre = $(this).attr('attrNombre');
    var planActual = $(this).attr('attrPlan');

    Swal.fire({
        title: 'Modificar Plan',
        html: 
            '<p class="small text-muted mb-3">Estacionamiento: <strong>' + nombre + '</strong></p>' +
            '<div class="mb-3 text-start">' +
            '<label class="form-label small fw-bold">Seleccionar Plan:</label>' +
            '<select id="swalSelectPlan" class="form-select">' +
            '<option value="Mensual"' + (planActual === 'Mensual' ? ' selected' : '') + '>Mensual (+30 días)</option>' +
            '<option value="Anual"' + (planActual === 'Anual' ? ' selected' : '') + '>Anual (+365 días)</option>' +
            '<option value="Ilimitado"' + (planActual === 'Ilimitado' ? ' selected' : '') + '>Ilimitado (Vitalicio permanente)</option>' +
            '</select>' +
            '</div>',
        showCancelButton: true,
        confirmButtonText: 'Guardar Cambios',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#072F1F',
        preConfirm: function() {
            return {
                plan: $('#swalSelectPlan').val(),
                diasExtra: $('#swalSelectPlan').val() === 'Anual' ? 365 : 30
            };
        }
    }).then(function(result) {
        if (result.isConfirmed) {
            $.ajax({
                url: 'index.php',
                method: 'POST',
                data: {
                    metodo: 'modificar',
                    accion: 'clientes',
                    id: id,
                    plan: result.value.plan,
                    diasExtra: result.value.diasExtra
                },
                beforeSend: function() {
                    $('#carga').css('display', 'flex').show();
                },
                success: function(res) {
                    var resp = typeof res === 'string' ? res.trim() : String(res).trim();
                    if (resp === 'Correcto') {
                        Swal.fire('¡Actualizado!', 'El plan del cliente se actualizó con éxito.', 'success');
                        recargarTablaClientes();
                    } else {
                        Swal.fire('Error', resp, 'error');
                    }
                },
                complete: function() {
                    $('#carga').hide();
                }
            });
        }
    });
});

// Eliminar cliente
$(document).off('click', '.bEliminarCliente').on('click', '.bEliminarCliente', function() {
    var id = $(this).attr('attrID');
    var nombre = $(this).attr('attrNombre');

    Swal.fire({
        icon: 'warning',
        title: '¿Eliminar cliente?',
        html: '¿Estás seguro de eliminar a <strong>' + nombre + '</strong>?<br><small class="text-danger">Esta acción eliminará su registro de suscripción y su base de datos de manera permanente.</small>',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444'
    }).then(function(result) {
        if (result.isConfirmed) {
            $.ajax({
                url: 'index.php',
                method: 'POST',
                data: {
                    metodo: 'eliminar',
                    accion: 'clientes',
                    id: id
                },
                beforeSend: function() {
                    $('#carga').css('display', 'flex').show();
                },
                success: function(res) {
                    var resp = typeof res === 'string' ? res.trim() : String(res).trim();
                    if (resp === 'Correcto') {
                        Swal.fire('Eliminado', 'El cliente ha sido eliminado.', 'success');
                        recargarTablaClientes();
                    } else {
                        Swal.fire('Error', resp, 'error');
                    }
                },
                complete: function() {
                    $('#carga').hide();
                }
            });
        }
    });
});
