<div class="cuenta-content" id="areaCuenta">
  
  <!-- Page Header Banner (Spark Admin Template) -->
  <div class="page-header d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h1 class="page-title">Mi Perfil</h1>
      <p class="page-subtitle">Administra tus datos de contacto y credenciales de acceso como Super Administrador.</p>
    </div>
    <div>
      <button type="button" class="btn btn-outline-success btn-sm d-flex align-items-center gap-1 cargarVista" carga="v_cuenta" titulo="Mi Perfil" id="bRecargarCuenta" style="border-color: #072F1F; color: #072F1F;">
        <i class="bi bi-arrow-clockwise"></i>
        <span>Actualizar</span>
      </button>
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm" style="border-radius: 14px;">
        <div class="card-body p-4 p-md-5">

          <!-- User Header Chip -->
          <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom flex-wrap">
            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4 shadow-sm" style="width: 64px; height: 64px; min-width: 64px; background: #072F1F; color: #B4F105; border: 2px solid #B4F105;">
              <span id="perfilAvatarIniciales">AD</span>
            </div>
            <div>
              <h5 class="fw-bold mb-0 text-dark" id="perfilNombreHeader">Administrador</h5>
              <span class="text-muted small d-block" id="perfilCorreoHeader">admin@estacionamiento.com</span>
              <span class="badge text-dark mt-1 fw-bold px-2 py-1" style="background-color: #B4F105; font-size: 0.75rem;">
                <i class="bi bi-shield-lock-fill me-1" style="color: #072F1F;"></i>Super Administrador
              </span>
            </div>
          </div>

          <!-- Formulario de Perfil con jQuery Validation -->
          <form id="formPerfil" novalidate>

            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-person-vcard text-success me-2"></i>Datos Generales</h6>

            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label for="nombreAdmin" class="form-label small fw-semibold text-secondary">Nombre Completo</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                  <input type="text" class="form-control border-start-0" id="nombreAdmin" name="nombreAdmin" placeholder="Tu nombre" required autocomplete="name">
                </div>
              </div>

              <div class="col-md-6">
                <label for="correoAdmin" class="form-label small fw-semibold text-secondary">Correo Electrónico</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                  <input type="email" class="form-control border-start-0" id="correoAdmin" name="correoAdmin" placeholder="correo@ejemplo.com" required autocomplete="email">
                </div>
              </div>
            </div>

            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-key text-warning me-2"></i>Seguridad y Contraseña</h6>
            <p class="text-muted small mb-3">Deja estos campos vacíos si solo deseas actualizar tu nombre o correo.</p>

            <div class="bg-light p-3 p-md-4 rounded-3 mb-4 border">
              <div class="mb-3">
                <label for="contrasenaActual" class="form-label small fw-semibold text-secondary">Contraseña Actual</label>
                <div class="input-group">
                  <span class="input-group-text bg-white border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
                  <input type="password" class="form-control bg-white border-start-0 border-end-0" id="contrasenaActual" name="contrasenaActual" placeholder="Ingresa tu contraseña actual" autocomplete="current-password">
                  <button class="btn btn-outline-secondary bg-white border-start-0 toggle-pass" type="button" data-target="#contrasenaActual" tabindex="-1">
                    <i class="bi bi-eye text-muted"></i>
                  </button>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label for="nuevaContrasena" class="form-label small fw-semibold text-secondary">Nueva Contraseña</label>
                  <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" class="form-control bg-white border-start-0 border-end-0" id="nuevaContrasena" name="nuevaContrasena" placeholder="Mínimo 6 caracteres" autocomplete="new-password">
                    <button class="btn btn-outline-secondary bg-white border-start-0 toggle-pass" type="button" data-target="#nuevaContrasena" tabindex="-1">
                      <i class="bi bi-eye text-muted"></i>
                    </button>
                  </div>
                </div>

                <div class="col-md-6">
                  <label for="confirmarContrasena" class="form-label small fw-semibold text-secondary">Confirmar Nueva Contraseña</label>
                  <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-check-circle text-muted"></i></span>
                    <input type="password" class="form-control bg-white border-start-0 border-end-0" id="confirmarContrasena" name="confirmarContrasena" placeholder="Repite la contraseña" autocomplete="new-password">
                    <button class="btn btn-outline-secondary bg-white border-start-0 toggle-pass" type="button" data-target="#confirmarContrasena" tabindex="-1">
                      <i class="bi bi-eye text-muted"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end w-100">
              <button type="submit" class="btn text-white px-4 py-2 d-flex align-items-center justify-content-center gap-2 w-100 w-sm-auto shadow-sm" id="bGuardarPerfil" style="background-color: #072F1F; border-radius: 8px;">
                <i class="bi bi-floppy"></i>
                <span class="fw-semibold">Guardar Cambios</span>
              </button>
            </div>

          </form>

        </div>
      </div>
    </div>
  </div>

</div>
