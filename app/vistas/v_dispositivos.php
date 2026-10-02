<div class="row g-3">
  <!-- Tarjeta Principal de Vinculación -->
  <div class="col-12 col-lg-7">
    <div class="card border-0 shadow-sm rounded-4 h-100">
      <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <h5 class="fw-bold mb-1"><i class="bi bi-tablet-fill text-primary me-2"></i>Vincular Nueva Tablet</h5>
            <p class="text-muted small mb-0">Enlaza cualquier tablet Android / iOS a tu estacionamiento en 1 segundo</p>
          </div>
          <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-bold">
            <i class="bi bi-infinity me-1"></i> Tablets Ilimitadas
          </span>
        </div>
      </div>
      <div class="card-body px-4 py-3">
        
        <!-- Contenedor del Código y QR -->
        <div class="p-4 bg-light rounded-4 border text-center mb-4">
          <span class="text-uppercase text-muted fw-bold small tracking-wide d-block mb-1">Tu Código de Vinculación</span>
          <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
            <span class="display-4 fw-black font-monospace text-primary tracking-wider" id="txtCodigoCorto">PK-....</span>
            <button class="btn btn-outline-secondary btn-sm rounded-3" onclick="copiarCodigo()" title="Copiar Código">
              <i class="bi bi-clipboard" id="iconoCopiar"></i>
            </button>
          </div>

          <div class="d-flex justify-content-center my-3">
            <div id="contenedorQR" class="p-3 bg-white rounded-3 shadow-sm border d-inline-block">
              <img id="imgQR" src="" alt="Código QR de Vinculación" style="width: 180px; height: 180px; object-fit: contain; display: none;">
              <div id="qrLoading" class="spinner-border text-primary my-5" role="status">
                <span class="visually-hidden">Generando QR...</span>
              </div>
            </div>
          </div>
          <p class="small text-muted mb-1">Escanea este QR directamente con la cámara de la tablet</p>
          <div class="small text-secondary" style="font-size: 0.78rem;">
            Enlace: <code id="txtServidorUrlBadge" class="user-select-all text-primary fw-bold">...</code>
          </div>
        </div>

        <!-- Pasos sencillos -->
        <h6 class="fw-bold mb-3"><i class="bi bi-list-check text-primary me-2"></i>¿Cómo enlazar tu tablet?</h6>
        <div class="d-flex flex-column gap-2">
          <div class="d-flex align-items-start gap-3 p-2 rounded-3 bg-white border">
            <span class="badge bg-primary rounded-circle px-2 py-1 mt-1">1</span>
            <div>
              <strong class="d-block text-dark small">Abre la App en tu Tablet</strong>
              <span class="text-muted" style="font-size: 0.8rem;">Entra a la aplicación móvil de Estacionamiento y ve a <strong>Ajustes</strong>.</span>
            </div>
          </div>
          <div class="d-flex align-items-start gap-3 p-2 rounded-3 bg-white border">
            <span class="badge bg-primary rounded-circle px-2 py-1 mt-1">2</span>
            <div>
              <strong class="d-block text-dark small">Toca "Vincular con mi Estacionamiento"</strong>
              <span class="text-muted" style="font-size: 0.8rem;">Pulsa el botón de escanear o escribe el código de 6 caracteres que ves arriba.</span>
            </div>
          </div>
          <div class="d-flex align-items-start gap-3 p-2 rounded-3 bg-white border">
            <span class="badge bg-primary rounded-circle px-2 py-1 mt-1">3</span>
            <div>
              <strong class="d-block text-dark small">¡Listo para operar!</strong>
              <span class="text-muted" style="font-size: 0.8rem;">La tablet cargará el nombre de tu negocio, sincronizará tarifas y guardará todo en tu base de datos automáticamente.</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Lista de Tablets Vinculadas -->
  <div class="col-12 col-lg-5">
    <div class="card border-0 shadow-sm rounded-4 h-100">
      <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
        <h5 class="fw-bold mb-1"><i class="bi bi-devices text-success me-2"></i>Tablets Conectadas</h5>
        <p class="text-muted small mb-0">Dispositivos que han enviado registros a tu sistema</p>
      </div>
      <div class="card-body px-4 py-2">
        <div id="listaTablets" class="d-flex flex-column gap-2 mt-2">
          <div class="text-center py-5 text-muted">
            <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
            <p class="small mb-0">Cargando dispositivos vinculados...</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  window.codigoActual = window.codigoActual || '';
  window.tokenActual = window.tokenActual || '';
  window.urlServidorActual = window.urlServidorActual || '';

  window.cargarDatosDispositivos = function() {
    $.ajax({
      url: 'index.php',
      method: 'POST',
      dataType: 'json',
      data: {
        metodo: 'consultar',
        accion: 'dispositivos'
      },
      success: function(res) {
        if (res.status === 'success') {
          // Si navegamos bajo HTTPS o el dominio es parkingpro.top, garantizar https://
          if (res.servidorUrl && (window.location.protocol === 'https:' || res.servidorUrl.indexOf('parkingpro.top') !== -1)) {
            res.servidorUrl = res.servidorUrl.replace(/^http:\/\//i, 'https://');
          }

          window.codigoActual = res.codigo;
          window.tokenActual = res.token;
          window.urlServidorActual = res.servidorUrl;

          $('#txtCodigoCorto').text(res.codigo);
          $('#txtServidorUrlBadge').text(res.servidorUrl);

          // Generar QR con el payload de enlace
          var qrPayload = JSON.stringify({
            url: res.servidorUrl,
            codigo: res.codigo,
            token: res.token,
            nombre: res.nombre
          });

          var qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' + encodeURIComponent(qrPayload);
          $('#imgQR').attr('src', qrUrl).off('load').on('load', function() {
            $('#qrLoading').hide();
            $(this).show();
          });

          // Renderizar lista de tablets activas
          var contenedor = $('#listaTablets');
          contenedor.empty();

          if (!res.tablets || res.tablets.length === 0) {
            contenedor.html(`
              <div class="text-center py-5 text-muted">
                <i class="bi bi-tablet text-secondary display-6 d-block mb-2"></i>
                <p class="small mb-1 fw-bold">Aún no hay tablets conectadas</p>
                <p class="text-muted" style="font-size: 0.78rem;">Sigue los pasos a la izquierda para vincular tu primera tablet.</p>
              </div>
            `);
          } else {
            res.tablets.forEach(function(tab) {
              contenedor.append(`
                <div class="p-3 bg-white border rounded-3 d-flex align-items-center justify-content-between">
                  <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-subtle text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                      <i class="bi bi-tablet-screen-button fs-5"></i>
                    </div>
                    <div>
                      <strong class="d-block text-dark small">${tab.Dispositivo || 'Tablet 1'}</strong>
                      <span class="text-muted" style="font-size: 0.75rem;">
                        <i class="bi bi-clock me-1"></i>Última actividad: ${tab.Ultima_Actividad || 'Hoy'}
                      </span>
                    </div>
                  </div>
                  <span class="badge bg-light text-dark border small fw-bold">
                    ${tab.Total_Vehiculos} autos
                  </span>
                </div>
              `);
            });
          }
        }
      },
      error: function() {
        $('#txtCodigoCorto').text('Error');
      }
    });
  };

  window.copiarCodigo = function() {
    if (!window.codigoActual) return;
    navigator.clipboard.writeText(window.codigoActual).then(function() {
      $('#iconoCopiar').removeClass('bi-clipboard').addClass('bi-check-lg text-success');
      setTimeout(function() {
        $('#iconoCopiar').removeClass('bi-check-lg text-success').addClass('bi-clipboard');
      }, 2000);
    });
  };

  window.cargarDatosDispositivos();
})();
</script>
