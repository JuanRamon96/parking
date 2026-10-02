<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar mi Estacionamiento | ParkingPro</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="vistas/assets/images/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="vistas/assets/images/favicon.png">
    <link rel="apple-touch-icon" href="vistas/assets/images/favicon.png">
    
    <!-- Local Third-Party Libraries -->
    <link rel="stylesheet" href="vistas/assets/libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="vistas/assets/libs/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="vistas/assets/css/main.css">

    <style>
      .badge-trial {
        background: linear-gradient(135deg, #10B981, #059669);
        color: white;
        font-weight: 800;
        font-size: 0.82rem;
        padding: 6px 14px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 12px;
      }
      .plan-feature-pill {
        display: inline-block;
        background: #F1F5F9;
        border: 1px solid #CBD5E1;
        color: #334155;
        font-size: 0.78rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        margin: 2px;
      }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-bg-shape login-bg-shape-1"></div>
        <div class="login-bg-shape login-bg-shape-2"></div>
        
        <div class="login-card" style="max-width: 480px;">
            
            <div class="text-center mb-3">
                <div class="mb-2">
                    <img src="vistas/assets/images/favicon.png" width="48" height="48" class="rounded-3 shadow-sm" alt="ParkingPro">
                </div>
                <span class="badge-trial">
                    <i class="bi bi-gift-fill"></i> 7 DÍAS DE PRUEBA GRATIS
                </span>
                <h3 class="fw-bold mb-1">Registra tu Estacionamiento</h3>
                <p class="text-muted small mb-3">Empieza a controlar ingresos, egresos y vincular tus tablets al instante.</p>
                <div class="mb-2">
                    <span class="plan-feature-pill"><i class="bi bi-tablet-fill text-primary me-1"></i>Tablets Ilimitadas</span>
                    <span class="plan-feature-pill"><i class="bi bi-cloud-check-fill text-success me-1"></i>MySQL en la Nube</span>
                    <span class="plan-feature-pill"><i class="bi bi-printer-fill text-warning me-1"></i>Tickets Térmicos</span>
                </div>
            </div>

            <div id="mensajeAlerta"></div>
            
            <form id="formRegistro" novalidate>
                
                <div class="login-form-group mb-2">
                    <label for="nombreNegocio" class="login-form-label">Nombre del Estacionamiento</label>
                    <div class="login-input-group">
                        <i class="bi bi-p-square input-icon"></i>
                        <input type="text" id="nombreNegocio" name="nombreNegocio" class="login-input" placeholder="Ej: Estacionamiento Central" required>
                    </div>
                </div>

                <div class="login-form-group mb-2">
                    <label for="nombreContacto" class="login-form-label">Tu Nombre Completo</label>
                    <div class="login-input-group">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" id="nombreContacto" name="nombreContacto" class="login-input" placeholder="Ej: Juan Pérez" required>
                    </div>
                </div>

                <div class="login-form-group mb-2">
                    <label for="correo" class="login-form-label">Correo Electrónico</label>
                    <div class="login-input-group">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" id="correo" name="correo" class="login-input" placeholder="correo@ejemplo.com" required>
                    </div>
                </div>

                <div class="login-form-group mb-2">
                    <label for="telefono" class="login-form-label">Teléfono o WhatsApp</label>
                    <div class="login-input-group">
                        <i class="bi bi-telephone input-icon"></i>
                        <input type="tel" id="telefono" name="telefono" class="login-input" placeholder="Ej: 4431234567" required>
                    </div>
                </div>
                
                <div class="login-form-group mb-4">
                    <label for="contrasena" class="login-form-label">Contraseña</label>
                    <div class="login-input-group">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <input type="password" id="contrasena" name="contrasena" class="login-input login-input-password" placeholder="Mínimo 6 caracteres" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-login" id="btnRegistrar">
                    <span>Crear Cuenta y Comenzar</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
                
            </form>

            <div class="text-center mt-3 pt-2 border-top">
                <small class="text-muted">¿Ya tienes una cuenta registrada?</small><br>
                <a href="./" class="fw-bold text-decoration-none mt-1 d-inline-block">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión en mi Panel
                </a>
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

    <script>
      $('#formRegistro').on('submit', function(e) {
        e.preventDefault();

        const nombreNegocio = $('#nombreNegocio').val().trim();
        const nombreContacto = $('#nombreContacto').val().trim();
        const correo = $('#correo').val().trim();
        const telefono = $('#telefono').val().trim();
        const contrasena = $('#contrasena').val();

        if (!nombreNegocio || !nombreContacto || !correo || !contrasena) {
          mostrarAlerta('Por favor completa todos los campos obligatorios.', 'danger');
          return;
        }

        if (contrasena.length < 6) {
          mostrarAlerta('La contraseña debe tener al menos 6 caracteres.', 'danger');
          return;
        }

        const btn = $('#btnRegistrar');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Creando tu sistema...');

        $.ajax({
          url: 'index.php',
          method: 'POST',
          dataType: 'json',
          data: {
            accion: 'registro',
            nombreNegocio,
            nombreContacto,
            correo,
            telefono,
            contrasena
          },
          success: function(res) {
            if (res.status === 'success') {
              mostrarAlerta('¡Registro exitoso! Configurando tu estacionamiento...', 'success');
              setTimeout(function() {
                window.location.href = './';
              }, 1200);
            } else {
              mostrarAlerta(res.message || 'Ocurrió un error al registrar.', 'danger');
              btn.prop('disabled', false).html('<span>Crear Cuenta y Comenzar</span> <i class="bi bi-arrow-right"></i>');
            }
          },
          error: function() {
            mostrarAlerta('Error al comunicarse con el servidor. Revisa tu conexión.', 'danger');
            btn.prop('disabled', false).html('<span>Crear Cuenta y Comenzar</span> <i class="bi bi-arrow-right"></i>');
          }
        });
      });

      function mostrarAlerta(msg, tipo) {
        $('#mensajeAlerta').html(
          '<div class="alert alert-' + tipo + ' alert-dismissible fade show p-2 px-3 small" role="alert">' +
            msg +
            '<button type="button" class="btn-close p-2" data-bs-dismiss="alert"></button>' +
          '</div>'
        );
      }
    </script>
</body>
</html>
