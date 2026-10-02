if (window.jQuery) {
  if (typeof jQuery.trim !== 'function') {
    jQuery.trim = function (text) {
      return text == null ? '' : String(text).replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
    };
  }
  if (typeof jQuery.isArray !== 'function') {
    jQuery.isArray = Array.isArray;
  }
  if (typeof jQuery.isFunction !== 'function') {
    jQuery.isFunction = function (obj) {
      return typeof obj === 'function';
    };
  }
}

function parseRespuesta(res) {
  if (typeof res === 'object' && res !== null) {
    if (res.session_expired) {
      window.location.href = './';
    }
    return res;
  }
  var txt = String(res == null ? '' : res).replace(/[\uFEFF]/g, '').trim();
  if (txt === '') {
    return {};
  }
  if (txt.indexOf('<!DOCTYPE') !== -1 || txt.indexOf('<html') !== -1 || txt.indexOf('id="formLogin"') !== -1 || txt.indexOf('session_expired') !== -1) {
    if (txt.indexOf('formLogin') !== -1 || txt.indexOf('session_expired') !== -1) {
      window.location.href = './';
      return { session_expired: true };
    }
  }
  var inicio = txt.search(/[\[{]/);
  if (inicio > 0) {
    txt = txt.substring(inicio);
  }
  try {
    var data = JSON.parse(txt);
    if (data && data.session_expired) {
      window.location.href = './';
    }
    return data;
  } catch (err) {
    console.error("Error parseando respuesta JSON:", err);
    return {};
  }
}

function formatoMoneda(valor) {
  if (valor === null || valor === undefined || valor === '') return '$0.00';
  var limpio = String(valor).replace(/\$/g, '').replace(/,/g, '').trim();
  var num = parseFloat(limpio);
  if (isNaN(num)) return '$0.00';

  var formato = new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });

  if (num < 0) {
    return '-$' + formato.format(Math.abs(num));
  } else {
    return '$' + formato.format(num);
  }
}

function moneda() {
  $(".dinero").each(function (index, el) {
    var texto = $(this).is('input') ? $(this).val() : $(this).html();
    if (!texto) return;

    var limpio = texto.replace(/\$/g, '').replace(/,/g, '').trim();
    if (limpio === '' || isNaN(limpio)) return;

    var num = parseFloat(limpio);
    var formato = new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });

    var resultado = '';
    if (num < 0) {
      resultado = '-$' + formato.format(Math.abs(num));
    } else {
      resultado = '$' + formato.format(num);
    }

    if ($(this).is('input')) {
      $(this).val(resultado);
    } else {
      $(this).html(resultado);
    }
  });

  $(".porcentaje").each(function (index, el) {
    var texto = $(this).is('input') ? $(this).val() : $(this).html();
    if (!texto) return;

    var limpio = texto.replace(/%/g, '').replace(/,/g, '').trim();
    if (limpio === '' || isNaN(limpio)) return;

    var num = parseFloat(limpio);
    var formato = new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 1,
      maximumFractionDigits: 2
    });

    var resultado = '';
    if (num < 0) {
      resultado = '-' + formato.format(Math.abs(num)) + '%';
      $(this).css('color', 'red');
    } else {
      resultado = formato.format(num) + '%';
      $(this).css('color', '');
    }

    if ($(this).is('input')) {
      $(this).val(resultado);
    } else {
      $(this).html(resultado);
    }
  });

  $(".cantidad").each(function (index, el) {
    var texto = $(this).is('input') ? $(this).val() : $(this).html();
    if (!texto) return;

    var limpio = texto.replace(/\$/g, '').replace(/,/g, '').trim();
    if (limpio === '' || isNaN(limpio)) return;

    var num = parseFloat(limpio);
    var formato = new Intl.NumberFormat('en-US');

    var resultado = formato.format(Math.round(num * 100) / 100);

    if ($(this).is('input')) {
      $(this).val(resultado);
    } else {
      $(this).html(resultado);
    }
  });
}

jQuery(document).ready(function ($) {
  // Mobile backdrop overlay
  let overlay = document.querySelector('.sidebar-overlay');
  if (!overlay) {
    overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);
  }

  // Toggle móvil de sidebar
  $(document).on('click', '#sidebar-toggle', function (e) {
    e.stopPropagation();
    const sidebar = document.querySelector('#sidebar');
    if (sidebar) {
      sidebar.classList.toggle('show');
      overlay.classList.toggle('show', sidebar.classList.contains('show'));
    }
  });

  // Cierre de sidebar móvil al tocar overlay
  $(overlay).on('click', function () {
    const sidebar = document.querySelector('#sidebar');
    if (sidebar) sidebar.classList.remove('show');
    overlay.classList.remove('show');
  });

  // Toggle de escritorio para minimizar sidebar
  $(document).on('click', '#desktop-sidebar-toggle', function () {
    document.body.classList.toggle('sidebar-minimized');
    const icon = this.querySelector('i');
    if (icon) {
      if (document.body.classList.contains('sidebar-minimized')) {
        icon.className = 'bi bi-chevron-bar-right';
      } else {
        icon.className = 'bi bi-chevron-bar-left';
      }
    }
    setTimeout(function () {
      window.dispatchEvent(new Event('resize'));
    }, 300);
  });

  // Pantalla completa
  $(document).on('click', '#btn-fullscreen', function () {
    const btn = this;
    if (!document.fullscreenElement) {
      document.documentElement.requestFullscreen().then(() => {
        const icon = btn.querySelector('i');
        if (icon) icon.className = 'bi bi-fullscreen-exit';
      }).catch(err => {
        console.error("Error fullscreen:", err);
      });
    } else {
      document.exitFullscreen().then(() => {
        const icon = btn.querySelector('i');
        if (icon) icon.className = 'bi bi-arrows-fullscreen';
      });
    }
  });

  // Carga automática inicial del Dashboard (solo una vez)
  if (!window.__vistaInicialCargada) {
    window.__vistaInicialCargada = true;
    setTimeout(function () {
      var $inicio = $("#bMenuDashboard").length ? $("#bMenuDashboard") : $(".sidebar-menu-link.cargarVista").first();
      if ($inicio && $inicio.length) {
        cargarVista($inicio);
      } else {
        $("#carga").hide();
      }
    }, 0);
  }

  // Renovación periódica de sesión
  setInterval(function () {
    $.ajax({
      url: 'index.php',
      type: 'POST',
      data: "metodo=renovar"
    }).done(function () {
      console.log("Sesión activa");
    });
  }, 60000 * 10);

  // Motor SPA de carga de vistas
  function cargarVista(element) {
    if (!element || !element.length) return;

    var nombre = element.attr('carga');
    if (!nombre || nombre === 'undefined') return;

    $('.modal').modal('hide');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open').css('overflow', '');

    // Cerrar sidebar en pantallas chicas
    const sidebar = document.querySelector('#sidebar');
    if (sidebar) sidebar.classList.remove('show');
    if (overlay) overlay.classList.remove('show');

    // Actualizar clase activa en menú
    $('.sidebar-menu-link').removeClass('active');
    $('.dropdown-item').removeClass('active');
    if (element.hasClass('sidebar-menu-link')) {
      element.addClass('active');
    }

    var titulo = element.attr('titulo') || '',
        atri = element.attr('atri') || element.attr('attrID') || '',
        pesta = element.attr('pesta') || '';

    if (!atri || atri === 'undefined') {
      atri = element.closest('tr').attr('id') || '';
    }

    var data = "metodo=cambiar&accion=" + encodeURIComponent(nombre) + "&atri=" + encodeURIComponent(atri) + "&pesta=" + encodeURIComponent(pesta);

    $.ajax({
      url: 'index.php',
      type: 'POST',
      data: data,
      beforeSend: function () {
        $("#carga").css('display', 'flex').show();
      }
    })
      .done(function (res) {
        if (typeof res === 'object' && res !== null && res.session_expired) {
          window.location.href = './';
          return;
        }
        if (typeof res === 'string') {
          if (res.indexOf('session_expired') !== -1 || res.indexOf('id="formLogin"') !== -1) {
            window.location.href = './';
            return;
          }
        }

        $("#verVista").html(res);
        if (titulo) {
          $("#topbarTitulo").html(titulo);
        }

        if (typeof crearDataTable === 'function') {
          crearDataTable();
        }

        if (typeof window[nombre] === 'function') {
          window[nombre]();
        }

        moneda();
        window.scrollTo(0, 0);
      })
      .fail(function () {
        console.error("Error AJAX al cargar vista");
      })
      .always(function () {
        $("#carga").hide();
      });
  }

  window.cargarVista = cargarVista;

  $(document).on('click', '.cargarVista', function (e) {
    e.preventDefault();
    cargarVista($(this));
  });

  // Cerrar sesión
  $(document).on('click', '#bCerrarSe, .bCerrarSe', function (e) {
    e.preventDefault();
    Swal.fire({
      title: '¿Deseas cerrar sesión?',
      text: 'Se cerrará la sesión actual del sistema.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#072F1F',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí, salir',
      cancelButtonText: 'Cancelar'
    }).then(function (result) {
      if (result.isConfirmed) {
        $.ajax({
          url: 'index.php',
          type: 'POST',
          data: "metodo=eliminar&accion=login",
          beforeSend: function () {
            $("#carga").css('display', 'flex').show();
          }
        }).done(function () {
          window.location.href = './';
        }).fail(function () {
          window.location.href = './';
        });
      }
    });
  });

  document.addEventListener("wheel", function (event) {
    if (document.activeElement.type === "number") {
      document.activeElement.blur();
    }
  });
});
