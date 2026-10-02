function v_cuenta() {
  cargarDatosCuenta();

  // Toggle password visibility
  $(document).off('click', '.toggle-pass').on('click', '.toggle-pass', function (e) {
    e.preventDefault();
    var target = $($(this).data('target'));
    var icon = $(this).find('i');
    if (target.attr('type') === 'password') {
      target.attr('type', 'text');
      icon.removeClass('bi-eye').addClass('bi-eye-slash');
    } else {
      target.attr('type', 'password');
      icon.removeClass('bi-eye-slash').addClass('bi-eye');
    }
  });

  // Validar y enviar formulario
  if ($('#formPerfil').length && typeof $('#formPerfil').validate === 'function') {
    $('#formPerfil').validate({
      rules: {
        nombreAdmin: {
          required: true,
          minlength: 3
        },
        correoAdmin: {
          required: true,
          email: true
        },
        nuevaContrasena: {
          minlength: 6
        },
        confirmarContrasena: {
          equalTo: '#nuevaContrasena'
        }
      },
      messages: {
        nombreAdmin: {
          required: 'Ingresa tu nombre completo.',
          minlength: 'El nombre debe tener al menos 3 caracteres.'
        },
        correoAdmin: {
          required: 'Ingresa un correo electrónico.',
          email: 'Ingresa un correo electrónico válido.'
        },
        nuevaContrasena: {
          minlength: 'La nueva contraseña debe tener al menos 6 caracteres.'
        },
        confirmarContrasena: {
          equalTo: 'Las contraseñas no coinciden.'
        }
      },
      errorClass: 'is-invalid',
      errorElement: 'div',
      errorPlacement: function (error, element) {
        error.addClass('invalid-feedback d-block');
        if (element.closest('.input-group').length) {
          error.insertAfter(element.closest('.input-group'));
        } else {
          error.insertAfter(element);
        }
      },
      highlight: function (element) {
        $(element).addClass('is-invalid').removeClass('is-valid');
      },
      unhighlight: function (element) {
        $(element).removeClass('is-invalid');
      },
      submitHandler: function (form) {
        var passActual = $('#contrasenaActual').val();
        var passNueva = $('#nuevaContrasena').val();

        if (passNueva !== '' && passActual === '') {
          Swal.fire({
            icon: 'warning',
            title: 'Contraseña requerida',
            text: 'Debes ingresar tu contraseña actual para establecer una nueva.'
          });
          $('#contrasenaActual').focus();
          return;
        }

        var data = {
          metodo: 'modificar',
          accion: 'cuenta',
          nombre: $('#nombreAdmin').val().trim(),
          correo: $('#correoAdmin').val().trim(),
          contrasenaActual: passActual,
          nuevaContrasena: passNueva
        };

        $.ajax({
          url: 'index.php',
          type: 'POST',
          data: data,
          beforeSend: function () {
            $('#carga').css('display', 'flex').show();
            $('#bGuardarPerfil').prop('disabled', true);
          }
        })
        .done(function (res) {
          var resp = typeof res === 'string' ? res.trim() : String(res).trim();
          if (resp === 'Correcto') {
            Swal.fire({
              icon: 'success',
              title: '¡Perfil actualizado!',
              text: 'Tus datos de acceso se han actualizado correctamente.'
            });

            $('#contrasenaActual').val('');
            $('#nuevaContrasena').val('');
            $('#confirmarContrasena').val('');

            // Recargar datos y refrescar UI
            cargarDatosCuenta();
          } else if (resp === 'Correo Duplicado') {
            Swal.fire({
              icon: 'error',
              title: 'Correo no disponible',
              text: 'Este correo electrónico ya está registrado por otro administrador.'
            });
          } else if (resp === 'Contrasena Actual Incorrecta') {
            Swal.fire({
              icon: 'error',
              title: 'Contraseña incorrecta',
              text: 'La contraseña actual que ingresaste no es correcta.'
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: resp || 'Ocurrió un error al guardar los cambios.'
            });
          }
        })
        .fail(function () {
          Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo comunicar con el servidor.'
          });
        })
        .always(function () {
          $('#carga').hide();
          $('#bGuardarPerfil').prop('disabled', false);
        });
      }
    });
  }
}

function cargarDatosCuenta() {
  $.ajax({
    url: 'index.php',
    type: 'POST',
    data: {
      metodo: 'consultar',
      accion: 'cuenta'
    }
  })
  .done(function (res) {
    var data = parseRespuesta(res);

    if (data && data.Nombre) {
      $('#nombreAdmin').val(data.Nombre);
      $('#correoAdmin').val(data.Correo);
      $('#perfilNombreHeader').text(data.Nombre);
      $('#perfilCorreoHeader').text(data.Correo);

      var parts = data.Nombre.trim().split(/\s+/);
      var ini = parts[0] ? parts[0].charAt(0).toUpperCase() : 'A';
      if (parts.length > 1 && parts[1]) {
        ini += parts[1].charAt(0).toUpperCase();
      } else if (parts[0] && parts[0].length > 1) {
        ini += parts[0].charAt(1).toUpperCase();
      }

      $('#perfilAvatarIniciales').text(ini);
      $('.sidebar-profile-avatar').text(ini);
      $('.sidebar-profile-name, .navbar-profile-name, .profile-header-name').text(data.Nombre);
      $('.sidebar-profile-email, .profile-header-email').text(data.Correo);
    }
  });
}
