function moneda() {
  $(".dinero").each(function () {
    var raw = $(this).html().replace('$', '').replace(/,/g, '');
    var val = parseFloat(raw);
    if (isNaN(val)) return;
    if (val < 0) {
      $(this).html('-$' + new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Math.abs(val)));
    } else {
      $(this).html('$' + new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(val));
    }
  });

  $(".porcentaje").each(function () {
    var raw = $(this).html().replace('%', '').replace(/,/g, '');
    var val = parseFloat(raw);
    if (isNaN(val)) return;
    $(this).html(new Intl.NumberFormat('en-US').format(val) + '%');
  });

  $(".cantidad").each(function () {
    var raw = $(this).html().replace(/,/g, '');
    var val = parseFloat(raw);
    if (isNaN(val)) return;
    $(this).html(new Intl.NumberFormat('en-US').format(val));
  });
}

function parseRespuesta(res) {
  if (typeof res === 'object' && res !== null) return res;
  var txt = String(res == null ? '' : res).replace(/[\uFEFF]/g, '').trim();
  if (txt === '') return {};
  var inicio = txt.search(/[\[{]/);
  if (inicio > 0) txt = txt.substring(inicio);
  try {
    return JSON.parse(txt);
  } catch (e) {
    return {};
  }
}

function cargarVista(element) {
  var nombre, titulo, atri, pesta;

  if (typeof element === 'object' && element !== null && element.length) {
    nombre = element.attr('carga');
    titulo = element.attr('titulo') || '';
    atri = element.attr('atri') || '';
    pesta = element.attr('pesta') || '';

    $('.sidebar-menu-link').removeClass('active');
    if (element.hasClass('sidebar-menu-link')) {
      element.addClass('active');
    }
  } else if (typeof element === 'string') {
    nombre = element;
    titulo = arguments[1] || '';
    atri = arguments[2] || '';
    pesta = arguments[3] || '';

    $('.sidebar-menu-link').removeClass('active');
    $(".sidebar-menu-link[carga='" + nombre + "']").addClass('active');
  }

  if (!nombre) return;

  $('.modal').modal('hide');
  $('.modal-backdrop').remove();
  $('body').removeClass('modal-open').css('overflow', '');

  const sidebar = document.querySelector('#sidebar');
  const overlay = document.querySelector('.sidebar-overlay');
  if (sidebar && window.innerWidth < 992) sidebar.classList.remove('show');
  if (overlay) overlay.classList.remove('show');

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
      console.error("Error AJAX al cargar vista admin");
    })
    .always(function () {
      $("#carga").hide();
    });
}

window.cargarVista = cargarVista;

jQuery(document).ready(function ($) {
  // Mobile & Desktop sidebar toggles
  const sidebar = document.querySelector('#sidebar');
  const sidebarToggle = document.querySelector('#sidebar-toggle');
  let overlay = document.querySelector('.sidebar-overlay');
  if (!overlay) {
    overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);
  }

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function () {
      sidebar.classList.toggle('show');
      overlay.classList.toggle('show');
    });

    overlay.addEventListener('click', function () {
      sidebar.classList.remove('show');
      overlay.classList.remove('show');
    });
  }

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

  // Carga inicial segura (solo una vez)
  if (!window.__adminInicialCargado) {
    window.__adminInicialCargado = true;
    setTimeout(function () {
      var $inicio = $("#bMenuDashboard").length ? $("#bMenuDashboard") : $(".sidebar-menu-link.cargarVista").first();
      if ($inicio && $inicio.length) {
        cargarVista($inicio);
      } else {
        $("#carga").hide();
      }
    }, 0);
  }

  // Click handler unificado
  $(document).on('click', '.cargarVista', function (e) {
    e.preventDefault();
    cargarVista($(this));
  });

  // Cerrar sesión
  $(document).on('click', '#bCerrarSe, .bCerrarSe', function (e) {
    e.preventDefault();
    Swal.fire({
      title: '¿Cerrar sesión?',
      text: 'Se cerrará la sesión de administración.',
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

  // Renovación periódica de la sesión
  setInterval(function () {
    var data = "metodo=renovar";

    $.ajax({
      url: 'index.php',
      type: 'POST',
      data: data
    })
      .done(function (res) {
        console.log("Sesion renovada");
      })
      .fail(function () {
        console.log("error ajax");
      });
  }, 60000 * 10);
});