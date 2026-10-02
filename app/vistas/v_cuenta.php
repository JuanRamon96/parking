<div class="cuenta-content" id="areaCuenta">
  
  <!-- Page Header Banner (Spark Admin Template) -->
  <div class="page-header d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h1 class="page-title">Mi Cuenta</h1>
      <p class="page-subtitle">Administra tus datos de contacto y credenciales de acceso al sistema.</p>
    </div>
    <div>
      <button type="button" class="btn btn-outline-success btn-sm d-flex align-items-center gap-1 cargarVista" carga="v_cuenta" titulo="Mi Cuenta" id="bRecargarCuenta">
        <i class="bi bi-arrow-clockwise"></i>
        <span>Actualizar</span>
      </button>
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">

          <!-- User Header Chip -->
          <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom flex-wrap">
            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 64px; height: 64px; min-width: 64px; background: #072F1F; color: #B4F105; border: 2px solid #B4F105;">
              <span id="perfilAvatarIniciales">AD</span>
            </div>
            <div>
              <h5 class="fw-bold mb-0 text-dark" id="perfilNombreHeader">Administrador</h5>
              <span class="text-muted small d-block" id="perfilCorreoHeader">admin@estacionamiento.com</span>
              <span class="badge text-dark mt-1 fw-semibold" style="background-color: #B4F105;">Administrador</span>
            </div>
          </div>

          <!-- Formulario de Perfil con jQuery Validation -->
          <form id="formPerfil" novalidate>

            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-person-vcard text-success me-2"></i>Datos Generales</h6>

            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label for="nombreAdmin" class="form-label small fw-semibold text-secondary">Nombre Completo</label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                  <input type="text" class="form-control" id="nombreAdmin" name="nombreAdmin" placeholder="Tu nombre" required>
                </div>
              </div>

              <div class="col-md-6">
                <label for="correoAdmin" class="form-label small fw-semibold text-secondary">Correo Electrónico</label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                  <input type="email" class="form-control" id="correoAdmin" name="correoAdmin" placeholder="correo@ejemplo.com" required>
                </div>
              </div>
            </div>

            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-key text-warning me-2"></i>Seguridad y Contraseña</h6>
            <p class="text-muted small mb-3">Deja estos campos vacíos si solo deseas cambiar tu nombre o correo.</p>

            <div class="bg-light p-3 p-md-4 rounded-3 mb-4 border">
              <div class="mb-3">
                <label for="contrasenaActual" class="form-label small fw-semibold text-secondary">Contraseña Actual</label>
                <div class="input-group">
                  <span class="input-group-text bg-white"><i class="bi bi-shield-lock text-muted"></i></span>
                  <input type="password" class="form-control bg-white" id="contrasenaActual" name="contrasenaActual" placeholder="Ingresa tu contraseña actual">
                </div>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label for="nuevaContrasena" class="form-label small fw-semibold text-secondary">Nueva Contraseña</label>
                  <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" class="form-control bg-white" id="nuevaContrasena" name="nuevaContrasena" placeholder="Mínimo 6 caracteres">
                  </div>
                </div>

                <div class="col-md-6">
                  <label for="confirmarContrasena" class="form-label small fw-semibold text-secondary">Confirmar Nueva Contraseña</label>
                  <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-check-circle text-muted"></i></span>
                    <input type="password" class="form-control bg-white" id="confirmarContrasena" name="confirmarContrasena" placeholder="Repite la contraseña">
                  </div>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end w-100">
              <button type="submit" class="btn text-white px-4 py-2 d-flex align-items-center justify-content-center gap-2 w-100 w-sm-auto" id="bGuardarPerfil" style="background-color: #072F1F;">
                <i class="bi bi-floppy"></i>
                <span>Guardar Cambios</span>
              </button>
            </div>

          </form>

        </div>
      </div>
    </div>
  </div>

</div>
