function togglePasswordVisibility() {
    const passwordInput = document.getElementById('pass');
    const toggleIcon = document.getElementById('toggleIcon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

jQuery(document).ready(function ($) {
    $('#formLogin').validate({
        rules: {
            email: {
                required: true,
                email: true
            },
            pass: {
                required: true
            }
        },
        messages: {
            email: {
                required: "Ingresa tu correo electrónico.",
                email: "Introduce un correo válido."
            },
            pass: {
                required: "La contraseña es requerida."
            }
        },
        errorClass: 'is-invalid',
        errorElement: 'span',
        errorPlacement: function (error, element) {
            var group = element.closest('.login-input-group');
            if (group.length) {
                error.insertAfter(group);
            } else if (element.closest('.input-group').length) {
                error.insertAfter(element.closest('.input-group'));
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function (element) {
            $(element).addClass('is-invalid');
            $(element).closest('.login-input-group').addClass('is-invalid');
        },
        unhighlight: function (element) {
            $(element).removeClass('is-invalid');
            $(element).closest('.login-input-group').removeClass('is-invalid');
        },
        submitHandler: function (form) {
            var data = "accion=login&correo=" + encodeURIComponent($("#email").val().trim()) + "&contrasena=" + encodeURIComponent($("#pass").val());

            $.ajax({
                url: 'index.php',
                type: 'POST',
                data: data,
                beforeSend: function () {
                    $("#mensaAV").html('<div class="alert alert-primary alert-dismissible fade show" role="alert"><i class="fas fa-spinner fa-spin me-2"></i><strong>Verificando credenciales...</strong></div>').show();
                    $("#bIngresarLogin").prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status"></span> Accediendo...');
                }
            })
            .done(function (res) {
                var respuesta = res.trim();
                if (respuesta == "Correcto") {
                    $("#mensaAV").html('<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="fas fa-check-circle me-2"></i><strong>¡Acceso concedido! Entrando...</strong></div>');
                    setTimeout(function () {
                        window.location.href = './';
                    }, 800);
                } else if (respuesta == "No existe") {
                    $("#mensaAV").html('<div class="alert alert-warning alert-dismissible fade show" role="alert"><i class="fas fa-exclamation-triangle me-2"></i><strong>El correo no está registrado en el sistema.</strong></div>');
                } else if (respuesta == "0") {
                    $("#mensaAV").html('<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle me-2"></i><strong>Contraseña incorrecta.</strong></div>');
                } else {
                    $("#mensaAV").html('<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-exclamation-circle me-2"></i><strong>' + respuesta + '</strong></div>');
                }
            })
            .fail(function () {
                $("#mensaAV").html('<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-triangle-exclamation me-2"></i><strong>Error de conexión al servidor.</strong></div>');
            })
            .always(function () {
                setTimeout(function () {
                    $("#bIngresarLogin").prop('disabled', false).html('<i class="fa-solid fa-right-to-bracket me-2"></i> Iniciar Sesión');
                }, 1000);
            });
        }
    });

    $('#formReContra').validate({
        rules: {
            reEmail: {
                required: true,
                email: true
            }
        },
        messages: {
            reEmail: {
                required: "Ingresa tu correo registrado.",
                email: "Introduce un correo válido."
            }
        },
        errorClass: 'is-invalid',
        errorElement: 'span',
        errorPlacement: function (error, element) {
            var group = element.closest('.input-group, .login-input-group');
            if (group.length) {
                error.insertAfter(group);
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function (element) {
            $(element).addClass('is-invalid');
            $(element).closest('.input-group, .login-input-group').addClass('is-invalid');
        },
        unhighlight: function (element) {
            $(element).removeClass('is-invalid');
            $(element).closest('.input-group, .login-input-group').removeClass('is-invalid');
        },
        submitHandler: function (form) {
            var email = $("#reEmail").val().trim();
            var data = "accion=olvido&correo=" + encodeURIComponent(email);

            $.ajax({
                url: 'index.php',
                type: 'POST',
                data: data,
                beforeSend: function () {
                    $("#bAceReCo").prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Enviando...');
                    $("#bCanReCo").prop('disabled', true);
                }
            })
            .done(function (res) {
                var resp = res.trim();
                if (resp === "Correcto") {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Contraseña Enviada!',
                        text: 'Hemos generado y enviado una clave temporal a ' + email + '. Revisa tu bandeja de entrada o carpeta de spam.',
                        confirmButtonColor: '#0284c7'
                    });
                    document.getElementById("formReContra").reset();
                    var modalEl = document.getElementById('modalOlvidoContra');
                    if (modalEl && typeof bootstrap !== 'undefined') {
                        var modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        modalInstance.hide();
                    }
                } else if (resp === "Error 3 No existe") {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Correo no registrado',
                        text: 'El correo electrónico ingresado no coincide con ninguna cuenta de estacionamiento.',
                        confirmButtonColor: '#0284c7'
                    }).then(function () {
                        $("#reEmail").focus();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo generar la contraseña: ' + resp,
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .fail(function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No fue posible conectar con el servidor. Intenta de nuevo.',
                    confirmButtonColor: '#ef4444'
                });
            })
            .always(function () {
                $("#bAceReCo").prop('disabled', false).html('<i class="bi bi-send me-1"></i> Enviar');
                $("#bCanReCo").prop('disabled', false);
            });
        }
    });
});
