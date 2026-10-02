function v_cuenta() {
  cargarDatosCuenta();

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
        required: 'Ingresa tu nombre.',
        minlength: 'El nombre debe tener al menos 3 caracteres.'
      },
      correoAdmin: {
        required: 'Ingresa un correo electrónico.',
        email: 'Ingresa un correo válido.'
      },
      nuevaContrasena: {
        minlength: 'La contraseña debe tener al menos 6 caracteres.'
      },
      confirmarContrasena: {
        equalTo: 'Las contraseñas no coinciden.'
      }
    },
    errorClass: 'is-invalid',
    errorElement: 'span',
    submitHandler: function (form) {
      var passActual = $('#contrasenaActual').val();
      var passNueva = $('#nuevaContrasena').val();

      if (passNueva !== '' && passActual === '') {
        Swal.fire({
          icon: 'warning',
          title: 'Atención',
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
          $('#carga').show();
          $('#bGuardarPerfil').prop('disabled', true);
        }
      })
      .done(function (res) {
        var resp = res.trim();
        if (resp === 'Correcto') {
          Swal.fire({
            icon: 'success',
            title: '¡Perfil actualizado!',
            text: 'Tus datos se guardaron correctamente.'
          });

          $('#contrasenaActual').val('');
          $('#nuevaContrasena').val('');
          $('#confirmarContrasena').val('');

          // Actualizar datos en UI
          cargarDatosCuenta();
        } else if (resp === 'Correo Duplicado') {
          Swal.fire({
            icon: 'error',
            title: 'Correo no disponible',
            text: 'Este correo electrónico ya está registrado por otro usuario.'
          });
        } else if (resp === 'Contrasena Actual Incorrecta') {
          Swal.fire({
            icon: 'error',
            title: 'Contraseña incorrecta',
            text: 'La contraseña actual que ingresaste no coincide.'
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: resp
          });
        }
      })
      .fail(function () {
        Swal.fire({
          icon: 'error',
          title: 'Error de conexión',
          text: 'No se pudo conectar con el servidor.'
        });
      })
      .always(function () {
        $('#carga').hide();
        $('#bGuardarPerfil').prop('disabled', false);
      });
    }
  });
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
    var data = {};
    try {
      data = parseRespuesta(res);
    } catch (e) {
      console.error("Error parseando datos de cuenta:", e);
      return;
    }

    if (data.Nombre) {
      $('#nombreAdmin').val(data.Nombre);
      $('#correoAdmin').val(data.Correo);
      $('#perfilNombreHeader').text(data.Nombre);
      $('#perfilCorreoHeader').text(data.Correo);

      var parts = data.Nombre.split(' ');
      var ini = parts[0].charAt(0).toUpperCase();
      if (parts.length > 1) {
        ini += parts[1].charAt(0).toUpperCase();
      }
      $('#perfilAvatarIniciales').text(ini);
      $('.sidebar-profile-avatar').text(ini);
      $('.sidebar-profile-name, .navbar-profile-name').text(data.Nombre);
      $('.sidebar-profile-email').text(data.Correo);
    }
  });
}
