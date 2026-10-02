<?php
include_once dirname(__DIR__) . '/controladores/c_perfil.php';
$perfilController = new c_perfil();

$idUsuario = $_SESSION['user_estacionamiento']['ID_Usuario'] ?? 1;
$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_perfil'])) {
    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $passActual = $_POST['pass_actual'] ?? '';
    $passNueva = $_POST['pass_nueva'] ?? '';
    $passConfirmar = $_POST['pass_confirmar'] ?? '';

    $res = $perfilController->actualizarPerfil($idUsuario, $nombre, $correo, $passActual, $passNueva, $passConfirmar);
    $mensaje = $res['message'];
    $tipoMensaje = $res['status'] === 'success' ? 'success' : 'danger';
}

$user = $_SESSION['user_estacionamiento'] ?? [];
?>

<div class="content-wrapper p-3 p-md-4">
  <!-- START: Header Banner -->
  <div class="page-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4">
    <div>
      <h1 class="page-title fs-3 fw-bold text-dark mb-1">Mi Perfil de Usuario</h1>
      <p class="page-subtitle text-muted mb-0">Administra tus datos de acceso, correo electrónico y contraseña.</p>
    </div>
  </div>
  <!-- END: Header Banner -->

  <?php if (!empty($mensaje)): ?>
    <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm" role="alert">
      <i class="bi bi-<?= $tipoMensaje === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?> fs-4 me-2"></i>
      <div><strong><?= $tipoMensaje === 'success' ? '¡Excelente!' : 'Atención:' ?></strong> <?= htmlspecialchars($mensaje) ?></div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <div class="row g-4 justify-content-center">
    <!-- Card de Información del Usuario -->
    <div class="col-lg-4 col-xl-3">
      <div class="card shadow-sm border-0 text-center p-4 h-100">
        <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2.2rem; font-weight: bold;">
          <?= strtoupper(substr($user['Nombre'] ?? 'A', 0, 1)) ?>
        </div>
        <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars($user['Nombre'] ?? 'Administrador') ?></h4>
        <span class="badge bg-success bg-opacity-10 text-success fw-bold py-2 mb-3">
          <i class="bi bi-shield-check me-1"></i> Administrador
        </span>
        <p class="small text-muted mb-0">
          <i class="bi bi-envelope me-1"></i> <?= htmlspecialchars($user['Correo'] ?? 'admin@estacionamiento.com') ?>
        </p>
        <div class="mt-4 pt-3 border-top text-start small text-muted">
          <div><i class="bi bi-check2 text-success me-1"></i> Acceso total al panel web</div>
          <div class="mt-1"><i class="bi bi-check2 text-success me-1"></i> Consulta de reportes y cortes</div>
          <div class="mt-1"><i class="bi bi-check2 text-success me-1"></i> Sincronización con la app móvil</div>
        </div>
      </div>
    </div>

    <!-- Formulario de Edición de Perfil -->
    <div class="col-lg-8 col-xl-7">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
          <h5 class="card-title fw-bold text-dark mb-1">Actualizar Información de Acceso</h5>
          <p class="text-muted small mb-0">Modifica tu correo y/o actualiza tu contraseña de acceso.</p>
        </div>

        <div class="card-body p-4">
          <form method="POST" action="?p=perfil" id="formPerfil">
            <input type="hidden" name="guardar_perfil" value="1">

            <!-- Sección 1: Datos Generales -->
            <div class="mb-4">
              <h6 class="text-uppercase text-muted fw-bold small mb-3">1. Datos Personales y Correo</h6>

              <div class="mb-3">
                <label for="nombre" class="form-label fw-bold small text-dark">Nombre Completo:</label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                  <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($user['Nombre'] ?? '') ?>" required>
                </div>
              </div>

              <div class="mb-3">
                <label for="correo" class="form-label fw-bold small text-dark">Correo Electrónico (Usuario para iniciar sesión):</label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                  <input type="email" class="form-control" id="correo" name="correo" value="<?= htmlspecialchars($user['Correo'] ?? '') ?>" required>
                </div>
                <div class="form-text">Este correo será el que uses para iniciar sesión en el panel web.</div>
              </div>
            </div>

            <hr class="my-4">

            <!-- Sección 2: Cambio de Contraseña -->
            <div class="mb-4">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-uppercase text-muted fw-bold small mb-0">2. Cambiar Contraseña (Opcional)</h6>
                <span class="small text-muted">Deja en blanco si no deseas cambiarla</span>
              </div>

              <div class="mb-3">
                <label for="pass_actual" class="form-label fw-bold small text-dark">Contraseña Actual:</label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                  <input type="password" class="form-control" id="pass_actual" name="pass_actual" placeholder="Ingresa tu contraseña actual solo si vas a cambiarla">
                </div>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label for="pass_nueva" class="form-label fw-bold small text-dark">Nueva Contraseña:</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-shield-lock"></i></span>
                    <input type="password" class="form-control" id="pass_nueva" name="pass_nueva" placeholder="Mínimo 6 caracteres">
                  </div>
                </div>

                <div class="col-md-6">
                  <label for="pass_confirmar" class="form-label fw-bold small text-dark">Confirmar Nueva Contraseña:</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-shield-check"></i></span>
                    <input type="password" class="form-control" id="pass_confirmar" name="pass_confirmar" placeholder="Repite la nueva contraseña">
                  </div>
                </div>
              </div>
            </div>

            <!-- Botón Guardar -->
            <div class="pt-3 border-top d-flex justify-content-end">
              <button type="submit" class="btn btn-success px-4 py-2 fw-bold d-inline-flex align-items-center">
                <i class="bi bi-floppy-fill me-2"></i> Guardar Cambios
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
