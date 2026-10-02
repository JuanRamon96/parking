<div class="clientes-content" id="areaClientes">
  
  <!-- Page Header Banner (Spark Admin Template) -->
  <div class="page-header d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h1 class="page-title">Clientes y Estacionamientos</h1>
      <p class="page-subtitle">Gestión de cuentas suscritas, bases de datos y códigos de vinculación de tablets.</p>
    </div>
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-outline-success btn-sm d-flex align-items-center gap-1 cargarVista" carga="v_clientes" titulo="Clientes y Negocios" id="bRecargarClientes" style="border-color: #072F1F; color: #072F1F;">
        <i class="bi bi-arrow-clockwise"></i>
        <span>Actualizar</span>
      </button>
    </div>
  </div>

  <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
    <div class="table-container">
      <table id="tablaClientes" class="myDataTable table table-hover table-striped table-bordered text-center align-middle" width="100%" style="font-size: 13.5px;">
        <thead class="table-light">
          <tr>
            <th orden="Fecha">Fecha Registro</th>
            <th orden="Negocio" class="text-start">Estacionamiento / Correo</th>
            <th orden="Codigo">Código</th>
            <th orden="BD">Base de Datos</th>
            <th orden="Plan">Plan</th>
            <th orden="Estatus">Estado</th>
            <th orden="Vence">Vigencia</th>
            <th orden="No">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr><td colspan="8">Cargando clientes...</td></tr>
        </tbody>
      </table>
    </div>
  </div>

</div>
