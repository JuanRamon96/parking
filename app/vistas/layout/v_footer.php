    <!-- START: Footer Component -->
    <footer class="footer-custom mt-auto py-3 text-center text-muted border-top">
      <div class="container-fluid d-flex flex-column flex-sm-row justify-content-between align-items-center">
        <span>&copy; <?= date('Y') ?> <strong>Sistema de Estacionamiento</strong>. Todos los derechos reservados.</span>
        <span class="small text-muted mt-2 mt-sm-0">Panel Administrativo Web &bull; Conectado con App Móvil</span>
      </div>
    </footer>
    <!-- END: Footer Component -->

  </div>
  <!-- END: Main Content Area -->

  <!-- Local Third-Party Scripts (100% Offline Compatible) -->
  <script src="vistas/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="vistas/assets/libs/apexcharts/apexcharts.min.js"></script>
  <script src="vistas/assets/libs/flatpickr/flatpickr.min.js"></script>

  <!-- Sidebar interaction handlers -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const sidebar = document.getElementById('sidebar');
      const mobileToggle = document.getElementById('sidebar-toggle');
      const desktopToggle = document.getElementById('desktop-sidebar-toggle');

      // Create overlay for mobile
      let overlay = document.querySelector('.sidebar-overlay');
      if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
      }

      if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', function (e) {
          e.stopPropagation();
          sidebar.classList.toggle('show');
          overlay.classList.toggle('show', sidebar.classList.contains('show'));
        });

        overlay.addEventListener('click', function () {
          sidebar.classList.remove('show');
          overlay.classList.remove('show');
        });
      }

      if (desktopToggle && sidebar) {
        desktopToggle.addEventListener('click', function () {
          sidebar.classList.toggle('collapsed');
          document.querySelector('.main-wrapper')?.classList.toggle('expanded');
        });
      }
    });
  </script>
</body>
</html>
