<div class="tarifas-content" id="areaTarifas">

  <!-- Page Header -->
  <div class="page-header d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h1 class="page-title"><i class="bi bi-tag-fill text-warning me-2"></i>Tarifas y Datos del Estacionamiento</h1>
      <p class="page-subtitle mb-0">Configura los datos del negocio, políticas de ticket y las tarifas que adoptarán las tablets automáticamente.</p>
    </div>
    <div>
      <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" id="bRecargarTarifas">
        <i class="bi bi-arrow-clockwise"></i>
        <span>Recargar</span>
      </button>
    </div>
  </div>

  <!-- Banner Informativo -->
  <div class="alert alert-info border-0 shadow-sm rounded-4 d-flex align-items-center gap-3 mb-4 p-3" style="background-color: #E0F2FE; color: #0369A1;">
    <div class="fs-3"><i class="bi bi-broadcast"></i></div>
    <div class="small">
      <strong>Sincronización Automática con Tablets:</strong> Cada vez que vincules una nueva tablet (por código o escaneo QR) o actualices esta sección, los dispositivos conectados descargarán tu nombre, dirección, teléfono, leyenda de tickets y tarifas vigentes.
    </div>
  </div>

  <form id="formTarifas" novalidate>
    <div class="row g-4">
      
      <!-- Columna Izquierda: Datos del Negocio y Tickets -->
      <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
            <h5 class="fw-bold mb-1"><i class="bi bi-shop text-primary me-2"></i>Datos del Estacionamiento</h5>
            <p class="text-muted small mb-0">Información visible en el encabezado y pie de los tickets de entrada y cobro</p>
          </div>
          <div class="card-body px-4 py-3">

            <!-- Nombre -->
            <div class="mb-3">
              <label for="txtNombreNegocio" class="form-label small fw-bold text-secondary">Nombre del Estacionamiento <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-building"></i></span>
                <input type="text" class="form-control border-start-0" id="txtNombreNegocio" name="nombre" placeholder="Ej: ESTACIONAMIENTO CENTRAL" required>
              </div>
              <div class="form-text small">Es el nombre principal que se imprime en letras grandes en los tickets y aparece en la tablet.</div>
            </div>

            <!-- Domicilio -->
            <div class="mb-3">
              <label for="txtDomicilio" class="form-label small fw-bold text-secondary">Domicilio / Dirección</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-geo-alt"></i></span>
                <input type="text" class="form-control border-start-0" id="txtDomicilio" name="domicilio" placeholder="Ej: Av. Hidalgo #120, Col. Centro">
              </div>
            </div>

            <!-- Teléfono -->
            <div class="mb-3">
              <label for="txtTelefono" class="form-label small fw-bold text-secondary">Teléfono(s) de Contacto</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone"></i></span>
                <input type="text" class="form-control border-start-0" id="txtTelefono" name="telefono" placeholder="Ej: (348) 784-5211">
              </div>
            </div>

            <!-- Leyenda de Ticket -->
            <div class="mb-3">
              <label for="txtLeyenda" class="form-label small fw-bold text-secondary">Leyenda del Ticket (Deslinde de Responsabilidad)</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-check"></i></span>
                <textarea class="form-control border-start-0" id="txtLeyenda" name="leyenda" rows="3" placeholder="* No nos hacemos responsables de objetos de valor olvidados dentro del vehículo ni daños por terceros *"></textarea>
              </div>
              <div class="form-text small">Texto de políticas que se imprime al final de cada boleto para seguridad jurídica.</div>
            </div>

          </div>
        </div>
      </div>

      <!-- Columna Derecha: Tarifas de Cobro y Simulador -->
      <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100">
          <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
            <h5 class="fw-bold mb-1"><i class="bi bi-cash-stack text-success me-2"></i>Tarifas de Cobro ($ MXN)</h5>
            <p class="text-muted small mb-0">Precios por tiempo de estancia aplicados en la app</p>
          </div>
          <div class="card-body px-4 py-3">

            <!-- 1ra Media Hora -->
            <div class="mb-3">
              <label for="txtMedia" class="form-label small fw-bold text-secondary">1ra Media Hora ($)</label>
              <div class="input-group input-group-lg">
                <span class="input-group-text bg-light fw-bold text-success border-end-0">$</span>
                <input type="number" step="0.50" min="0" class="form-control border-start-0 fw-bold fs-5 text-dark" id="txtMedia" name="media" placeholder="10.00" required>
                <span class="input-group-text bg-light small text-muted">MXN</span>
              </div>
              <div class="form-text small">Si el vehículo permanece hasta 30 minutos.</div>
            </div>

            <!-- Hora Completa -->
            <div class="mb-3">
              <label for="txtPrecio" class="form-label small fw-bold text-secondary">Hora Completa ($)</label>
              <div class="input-group input-group-lg">
                <span class="input-group-text bg-light fw-bold text-primary border-end-0">$</span>
                <input type="number" step="0.50" min="0" class="form-control border-start-0 fw-bold fs-5 text-dark" id="txtPrecio" name="precio" placeholder="20.00" required>
                <span class="input-group-text bg-light small text-muted">MXN</span>
              </div>
              <div class="form-text small">Costo base por cada hora cumplida.</div>
            </div>

            <!-- Fracción Extra -->
            <div class="mb-4">
              <label for="txtExtra" class="form-label small fw-bold text-secondary">Fracción Extra ($)</label>
              <div class="input-group input-group-lg">
                <span class="input-group-text bg-light fw-bold text-warning border-end-0">$</span>
                <input type="number" step="0.50" min="0" class="form-control border-start-0 fw-bold fs-5 text-dark" id="txtExtra" name="extra" placeholder="10.00" required>
                <span class="input-group-text bg-light small text-muted">MXN</span>
              </div>
              <div class="form-text small">Cobro por fracciones menores a 30 min tras la 1ra hora.</div>
            </div>

            <!-- Previsualización / Ejemplo de Cobro en Vivo -->
            <div class="p-3 bg-light rounded-3 border">
              <span class="d-block small fw-bold text-dark mb-2"><i class="bi bi-calculator me-1"></i>Simulador de Cobro con estos valores:</span>
              <ul class="list-unstyled mb-0 small text-secondary">
                <li class="d-flex justify-content-between py-1 border-bottom">
                  <span>🚗 20 minutos (estancia corta):</span>
                  <strong class="text-dark" id="simMedia">$10.00</strong>
                </li>
                <li class="d-flex justify-content-between py-1 border-bottom">
                  <span>🚗 1 hora 15 minutos (hora + extra):</span>
                  <strong class="text-dark" id="simHoraExtra">$30.00</strong>
                </li>
                <li class="d-flex justify-content-between py-1">
                  <span>🚗 2 horas completas:</span>
                  <strong class="text-dark" id="simDosHoras">$40.00</strong>
                </li>
              </ul>
            </div>

          </div>
        </div>
      </div>

      <!-- Barra de Botón Guardar -->
      <div class="col-12 text-end mt-2">
        <button type="submit" class="btn text-white btn-lg px-5 py-3 rounded-3 shadow-sm d-inline-flex align-items-center gap-2" id="bGuardarTarifas" style="background-color: #072F1F;">
          <i class="bi bi-floppy-fill fs-5"></i>
          <span class="fw-bold">Guardar Tarifas y Datos</span>
        </button>
      </div>

    </div>
  </form>

</div>

<script>
(function() {
  function cargarTarifas() {
    $.ajax({
      url: 'index.php',
      method: 'POST',
      dataType: 'json',
      data: {
        metodo: 'consultar',
        accion: 'tarifas'
      },
      success: function(res) {
        if (res && res.status === 'success' && res.data) {
          const d = res.data;
          $('#txtNombreNegocio').val(d.nombre || '');
          $('#txtDomicilio').val(d.domicilio || '');
          $('#txtTelefono').val(d.telefono || '');
          $('#txtLeyenda').val(d.leyenda || '');
          $('#txtMedia').val(parseFloat(d.media || 10).toFixed(2));
          $('#txtPrecio').val(parseFloat(d.precio || 20).toFixed(2));
          $('#txtExtra').val(parseFloat(d.extra || 10).toFixed(2));
          actualizarSimulador();
        }
      },
      error: function() {
        console.error('No se pudieron consultar las tarifas');
      }
    });
  }

  function actualizarSimulador() {
    const media = parseFloat($('#txtMedia').val()) || 0;
    const precio = parseFloat($('#txtPrecio').val()) || 0;
    const extra = parseFloat($('#txtExtra').val()) || 0;

    $('#simMedia').text('$' + media.toFixed(2));
    $('#simHoraExtra').text('$' + (precio + extra).toFixed(2));
    $('#simDosHoras').text('$' + (precio * 2).toFixed(2));
  }

  // Eventos de inputs de tarifas para actualizar simulador en vivo
  $(document).off('input', '#txtMedia, #txtPrecio, #txtExtra').on('input', '#txtMedia, #txtPrecio, #txtExtra', function() {
    actualizarSimulador();
  });

  // Recargar
  $('#bRecargarTarifas').off('click').on('click', function() {
    cargarTarifas();
  });

  // Guardar formulario
  $('#formTarifas').off('submit').on('submit', function(e) {
    e.preventDefault();

    const nombre = $('#txtNombreNegocio').val().trim();
    if (!nombre) {
      Swal.fire({
        icon: 'warning',
        title: 'Campo obligatorio',
        text: 'Por favor escribe el nombre del estacionamiento.'
      });
      return;
    }

    const btn = $('#bGuardarTarifas');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Guardando...');

    const payload = {
      metodo: 'modificar',
      accion: 'tarifas',
      nombre: nombre,
      domicilio: $('#txtDomicilio').val().trim(),
      telefono: $('#txtTelefono').val().trim(),
      leyenda: $('#txtLeyenda').val().trim(),
      media: $('#txtMedia').val(),
      precio: $('#txtPrecio').val(),
      extra: $('#txtExtra').val()
    };

    $.ajax({
      url: 'index.php',
      method: 'POST',
      dataType: 'json',
      data: payload,
      success: function(res) {
        btn.prop('disabled', false).html('<i class="bi bi-floppy-fill fs-5"></i><span class="fw-bold">Guardar Tarifas y Datos</span>');
        if (res && res.status === 'success') {
          Swal.fire({
            icon: 'success',
            title: '¡Guardado!',
            text: res.message || 'Tarifas y datos guardados correctamente. Las tablets conectadas adoptarán estos datos al vincularse.',
            timer: 2500,
            showConfirmButton: false
          });
          cargarTarifas();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: res?.message || 'No se pudieron guardar los cambios.'
          });
        }
      },
      error: function(xhr, status, err) {
        btn.prop('disabled', false).html('<i class="bi bi-floppy-fill fs-5"></i><span class="fw-bold">Guardar Tarifas y Datos</span>');
        Swal.fire({
          icon: 'error',
          title: 'Error de servidor',
          text: 'Ocurrió un error al procesar la solicitud: ' + err
        });
      }
    });
  });

  cargarTarifas();
})();
</script>
