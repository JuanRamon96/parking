<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | ParkingPro</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="vistas/assets/images/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="vistas/assets/images/favicon.png">
    <link rel="apple-touch-icon" href="vistas/assets/images/favicon.png">
    
    <!-- Local Third-Party Libraries (Spark Admin) -->
    <link rel="stylesheet" href="vistas/assets/libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="vistas/assets/libs/bootstrap-icons/bootstrap-icons.css">
    
    <!-- Main Design System & Custom Stylesheet -->
    <link rel="stylesheet" href="vistas/assets/css/main.css">

    <style>
      .login-input-group.is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.15) !important;
      }
      span.is-invalid {
        display: block !important;
        width: 100% !important;
        font-size: 0.78rem;
        color: #dc3545;
        margin-top: 5px;
        padding-left: 4px;
        font-weight: 500;
        text-align: left;
      }
    </style>
</head>
<body>

    <!-- Authentication Container & Login Card (Spark Admin Template) -->
    <div class="login-wrapper">
        <!-- Glowing background shapes -->
        <div class="login-bg-shape login-bg-shape-1"></div>
        <div class="login-bg-shape login-bg-shape-2"></div>
        
        <!-- Main centered login card -->
        <div class="login-card">
            
            <!-- Brand Identity -->
            <a href="javascript:void(0)" class="login-brand text-decoration-none mb-2 d-flex align-items-center justify-content-center">
                <img src="vistas/assets/images/favicon.png" width="42" height="42" class="rounded-3 shadow-sm me-2" alt="ParkingPro">
                <span>Estacionamiento</span>
            </a>
            
            <p class="login-subtitle">Ingresa tus credenciales para acceder al panel de control</p>

            <div id="mensaAV"></div>
            
            <!-- Login Form -->
            <form id="formLogin" novalidate>
                
                <!-- Email Input Group -->
                <div class="login-form-group mb-3">
                    <label for="email" class="login-form-label">Correo Electrónico</label>
                    <div class="login-input-group">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" id="email" name="email" class="login-input" placeholder="Correo" required>
                    </div>
                </div>
                
                <!-- Password Input Group -->
                <div class="login-form-group mb-4">
                    <label for="pass" class="login-form-label">Contraseña</label>
                    <div class="login-input-group">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <input type="password" id="pass" name="pass" class="login-input login-input-password" placeholder="Contraseña" required>
                        <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility()" aria-label="Mostrar contraseña">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end align-items-center mb-3" style="margin-top: -8px;">
                    <a href="javascript:void(0)" class="text-decoration-none small text-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#modalOlvidoContra">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-login" id="bIngresarLogin">
                    <span>Iniciar Sesión</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
                
            </form>

            <div class="text-center mt-3 pt-2 border-top">
                <p class="mb-1 text-muted small">¿No tienes cuenta para tu estacionamiento?</p>
                <a href="?v=registro" class="btn btn-outline-primary btn-sm fw-bold px-3 py-2 rounded-pill text-decoration-none">
                    <i class="bi bi-gift-fill text-warning me-1"></i> Registrarme (7 Días Gratis)
                </a>
            </div>
            
        </div>
    </div>

    <!-- Modal para Recuperar Contraseña -->
    <div class="modal fade" id="modalOlvidoContra" tabindex="-1" aria-labelledby="modalOlvidoContraLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 pb-0 pe-4 pt-4">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4 pt-1 text-center">
                    <div class="bg-primary bg-opacity-10 text-primary mb-3 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                        <i class="bi bi-key-fill fs-2"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">¿Olvidaste tu contraseña?</h4>
                    <p class="text-muted small mb-4">No te preocupes. Introduce tu correo electrónico registrado y te enviaremos una clave temporal de acceso.</p>

                    <form id="formReContra" novalidate>
                        <div class="mb-4 text-start">
                            <label for="reEmail" class="form-label small fw-semibold text-secondary">Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" class="form-control border-start-0 py-2" id="reEmail" name="reEmail" placeholder="usuario@estacionamiento.com" required>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light w-50 py-2.5 fw-medium rounded-pill" data-bs-dismiss="modal" id="bCanReCo">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary w-50 py-2.5 fw-bold rounded-pill shadow-sm" id="bAceReCo">
                                <i class="bi bi-send me-1"></i> Enviar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="vistas/assets/plugins/jquery-4.0.0.min.js"></script>
    <script>
      if (window.jQuery && typeof jQuery.trim !== 'function') {
        jQuery.trim = function (text) {
          return text == null ? '' : String(text).replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
        };
      }
    </script>
    <script src="vistas/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vistas/assets/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
    <script src="vistas/assets/plugins/sweetalert/dist/sweetalert2.all.min.js"></script>
    <script src="vistas/assets/js/login.js"></script>
</body>
</html>
