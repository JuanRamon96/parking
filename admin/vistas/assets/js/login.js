$(document).ready(function() {
    $('#formLogin').on('submit', function(e) {
        e.preventDefault();
        var email = $('#email').val().trim();
        var pass = $('#pass').val().trim();

        if (!email || !pass) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos requeridos',
                text: 'Por favor ingresa tu correo y contraseña.',
                confirmButtonColor: '#0ea5e9'
            });
            return;
        }

        var btn = $('#bIngresarLogin');
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Iniciando sesión...');

        $.ajax({
            url: 'index.php',
            method: 'POST',
            dataType: 'text',
            data: {
                accion: 'login',
                correo: email,
                contrasena: pass
            },
            success: function(res) {
                var respuesta = String(res != null ? res : '').trim();
                if (respuesta === 'Correcto') {
                    window.location.href = './';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Acceso Denegado',
                        text: 'Credenciales inválidas o cuenta inactiva. Verifica tus datos.',
                        confirmButtonColor: '#ef4444'
                    });
                    btn.prop('disabled', false).html('<i class="fa-solid fa-right-to-bracket me-2"></i>Iniciar Sesión');
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de servidor',
                    text: 'No se pudo conectar con el servidor. Intenta de nuevo.',
                    confirmButtonColor: '#ef4444'
                });
                btn.prop('disabled', false).html('<i class="fa-solid fa-right-to-bracket me-2"></i>Iniciar Sesión');
            }
        });
    });

    window.togglePasswordVisibility = function() {
        var input = $('#pass');
        var icon = $('#toggleIcon');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    };
});
