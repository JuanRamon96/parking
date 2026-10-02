<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  <title>ParkingPro | Sistema Punto de Venta y App para Estacionamientos</title>
  <meta name="description" content="El software y app móvil para estacionamientos más rápido y completo. Control de entradas, salidas, tickets térmicos con QR, cortes de caja y reportes en la nube.">
  <meta name="keywords" content="sistema estacionamiento, app estacionamiento, software pensiones, punto de venta estacionamiento, tickets qr, corte caja">

  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/images/favicon.png">
  <link rel="apple-touch-icon" href="assets/images/favicon.png">

  <!-- Open Graph -->
  <meta property="og:title" content="ParkingPro - Software y App para Estacionamientos">
  <meta property="og:description" content="Control de entradas, salidas, tickets térmicos con QR, cortes de caja y reportes en la nube. Prueba gratis 7 días.">
  <meta property="og:image" content="assets/images/favicon.png">
  <meta property="og:type" content="website">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <style>
    :root {
      --primary: #0284C7;
      --primary-dark: #0369A1;
      --primary-light: #E0F2FE;
      --secondary: #0F172A;
      --accent: #10B981;
      --surface: #F8FAFC;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      color: #334155;
      background-color: #FFFFFF;
      overflow-x: hidden;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    h1, h2, h3, h4, h5, .brand-font {
      font-family: 'Sora', sans-serif;
    }

    /* ===================================================
       1. NAVBAR RESPONSIVE & MOBILE-FRIENDLY
       =================================================== */
    .navbar-parking {
      background: rgba(255, 255, 255, 0.96);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border-bottom: 1px solid rgba(226, 232, 240, 0.85);
      transition: all 0.3s ease;
      z-index: 1040;
    }

    .navbar-brand-logo {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      background: linear-gradient(135deg, #0284C7 0%, #0369A1 100%);
      color: #FFFFFF;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.3rem;
      box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
    }

    /* Links de navegación: Jamás se cortan en dos líneas */
    .nav-link {
      font-weight: 500;
      font-size: 0.95rem;
      color: #475569 !important;
      padding: 0.5rem 0.85rem !important;
      white-space: nowrap !important;
      border-radius: 10px;
      transition: all 0.18s ease;
    }

    .nav-link:hover,
    .nav-link:focus {
      color: var(--primary) !important;
      background-color: var(--primary-light);
    }

    /* Botón Toggler Móvil */
    .navbar-toggler-custom {
      border: 1px solid #E2E8F0 !important;
      padding: 8px 12px !important;
      border-radius: 10px !important;
      background: #F8FAFC !important;
      transition: all 0.2s ease;
    }
    .navbar-toggler-custom:focus {
      box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.2) !important;
    }

    /* Menú desplegable en dispositivos móviles (< 992px) */
    @media (max-width: 991.98px) {
      .navbar-collapse {
        background: #FFFFFF;
        border-radius: 20px;
        padding: 1.25rem;
        margin-top: 14px;
        box-shadow: 0 16px 36px -10px rgba(15, 23, 42, 0.15);
        border: 1px solid #E2E8F0;
      }
      .nav-link {
        padding: 12px 16px !important;
        font-size: 1rem;
        font-weight: 600;
        display: block;
      }
      .navbar-nav {
        gap: 4px;
      }
      .navbar-mobile-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: 100%;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #E2E8F0;
      }
      .navbar-mobile-actions .btn {
        width: 100%;
        padding: 12px;
        justify-content: center;
      }
    }

    /* ===================================================
       2. HERO SECTION RESPONSIVA
       =================================================== */
    .hero-section {
      background: radial-gradient(1100px 550px at 50% -10%, rgba(2, 132, 199, 0.12), transparent 70%),
                  radial-gradient(900px 600px at 90% 80%, rgba(16, 185, 129, 0.08), transparent 70%),
                  #FFFFFF;
      padding-top: clamp(100px, 12vw, 150px);
      padding-bottom: clamp(50px, 8vw, 90px);
      position: relative;
    }

    .hero-title {
      font-size: clamp(2.1rem, 5.2vw, 3.6rem);
      font-weight: 800;
      line-height: 1.15;
      letter-spacing: -0.02em;
    }

    .hero-subtitle {
      font-size: clamp(1rem, 2.2vw, 1.18rem);
      line-height: 1.6;
      color: #64748B;
    }

    .hero-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 7px 18px;
      background: rgba(2, 132, 199, 0.08);
      border: 1px solid rgba(2, 132, 199, 0.22);
      border-radius: 9999px;
      color: var(--primary-dark);
      font-size: clamp(0.78rem, 1.8vw, 0.88rem);
      font-weight: 600;
      margin-bottom: 20px;
      max-width: 100%;
      white-space: nowrap;
      text-overflow: ellipsis;
      overflow: hidden;
    }

    /* Botones CTA Principales */
    .btn-primary-custom {
      background: linear-gradient(135deg, #0284C7 0%, #0369A1 100%);
      color: #FFFFFF;
      border: none;
      font-weight: 700;
      padding: 12px 26px;
      border-radius: 12px;
      white-space: nowrap !important;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: all 0.2s ease;
      text-decoration: none;
    }
    .btn-primary-custom:hover,
    .btn-primary-custom:focus {
      background: linear-gradient(135deg, #0369A1 0%, #075985 100%);
      color: #FFFFFF;
      transform: translateY(-2px);
      box-shadow: 0 10px 20px -5px rgba(2, 132, 199, 0.4);
    }

    .btn-outline-custom {
      border: 1.5px solid #CBD5E1;
      color: #334155;
      background: #FFFFFF;
      font-weight: 600;
      padding: 12px 24px;
      border-radius: 12px;
      white-space: nowrap !important;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: all 0.2s ease;
      text-decoration: none;
    }
    .btn-outline-custom:hover {
      border-color: #94A3B8;
      background: #F8FAFC;
      color: #0F172A;
    }

    /* Mockup Visual */
    .mockup-container {
      background: #0F172A;
      border-radius: 24px;
      padding: clamp(16px, 3vw, 24px);
      box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .mockup-header {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 14px;
    }
    .mockup-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
    }

    /* ===================================================
       3. CARACTERÍSTICAS
       =================================================== */
    .feature-card {
      border: 1px solid #E2E8F0;
      border-radius: 20px;
      padding: clamp(20px, 3.5vw, 32px) clamp(16px, 2.5vw, 24px);
      background: #FFFFFF;
      transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
      height: 100%;
    }
    .feature-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 20px 35px -10px rgba(15, 23, 42, 0.08);
      border-color: #BAE6FD;
    }
    .feature-icon {
      width: 52px;
      height: 52px;
      border-radius: 14px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      margin-bottom: 18px;
    }

    /* ===================================================
       4. PRECIOS (TARJETAS SIN RECORTES)
       =================================================== */
    .pricing-card {
      border-radius: 24px;
      border: 1px solid #E2E8F0;
      background: #FFFFFF;
      transition: transform 0.25s ease, box-shadow 0.25s ease;
      overflow: visible !important;
      position: relative;
    }
    .pricing-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 20px 35px -10px rgba(15, 23, 42, 0.08);
    }
    .pricing-card.popular {
      border: 2px solid var(--primary) !important;
      box-shadow: 0 15px 35px -10px rgba(2, 132, 199, 0.22);
    }

    .badge-popular-floating {
      position: absolute;
      top: -16px;
      left: 0;
      right: 0;
      text-align: center;
      z-index: 50;
      overflow: visible !important;
      pointer-events: none;
    }
    .badge-popular-floating span {
      pointer-events: auto;
      font-size: 0.76rem;
      letter-spacing: 0.5px;
      padding: 7px 18px;
      box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);
    }

    .step-badge {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background: var(--primary);
      color: #FFFFFF;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 1.1rem;
      margin-bottom: 16px;
    }

    /* Accordion FAQ */
    .accordion-button:not(.collapsed) {
      background-color: var(--primary-light);
      color: var(--primary-dark);
      box-shadow: none;
    }
    .accordion-button:focus {
      box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.2);
    }

    /* Responsividad en pantallas pequeñas (< 576px) */
    @media (max-width: 575.98px) {
      .hero-actions-container {
        flex-direction: column;
        width: 100%;
      }
      .hero-actions-container .btn {
        width: 100%;
      }
      .hero-pill {
        white-space: normal;
        text-align: left;
      }
    }
  </style>
</head>

<body>

  <!-- ==========================================
       1. BARRA DE NAVEGACIÓN
       ========================================== -->
  <nav class="navbar navbar-expand-lg fixed-top navbar-parking py-2 py-lg-3">
    <div class="container-xl px-3 px-sm-4">
      
      <!-- Marca / Logo -->
      <a class="navbar-brand d-flex align-items-center gap-2 m-0" href="#inicio">
        <img src="assets/images/favicon.png" alt="ParkingPro" width="38" height="38" class="rounded-3 shadow-sm">
        <span class="fw-bold fs-4 text-dark brand-font">Parking<span class="text-primary">Pro</span></span>
      </a>

      <!-- Botón Hamburguesa Móvil -->
      <button class="navbar-toggler navbar-toggler-custom shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Abrir Menú">
        <i class="fa-solid fa-bars text-dark fs-5"></i>
      </button>

      <!-- Menú Colapsable -->
      <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav mx-auto mb-2 mb-lg-0 py-2 py-lg-0">
          <li class="nav-item"><a class="nav-link" href="#inicio">Inicio</a></li>
          <li class="nav-item"><a class="nav-link" href="#caracteristicas">Características</a></li>
          <li class="nav-item"><a class="nav-link" href="#como-funciona">Cómo Funciona</a></li>
          <li class="nav-item"><a class="nav-link" href="#precios">Planes y Precios</a></li>
          <li class="nav-item"><a class="nav-link" href="#faq">Preguntas</a></li>
        </ul>

        <!-- Botones de Acción (Escritorio y Móvil) -->
        <div class="navbar-mobile-actions d-lg-flex align-items-center gap-2">
          <a href="./app/" class="btn btn-outline-custom btn-sm px-3 rounded-pill fw-semibold">
            <i class="fa-solid fa-arrow-right-to-bracket"></i>
            <span>Ingresar</span>
          </a>
          <a href="./app/?v=registro" class="btn btn-primary-custom btn-sm rounded-pill px-4 shadow-sm">
            <i class="fa-solid fa-bolt"></i>
            <span>Prueba 7 Días</span>
          </a>
        </div>
      </div>

    </div>
  </nav>

  <!-- ==========================================
       2. HERO SECTION
       ========================================== -->
  <header id="inicio" class="hero-section">
    <div class="container-xl px-3 px-sm-4">
      <div class="row align-items-center gy-5">
        
        <div class="col-lg-6 text-center text-lg-start">
          <div class="hero-pill">
            <i class="fa-solid fa-shield-halved text-success"></i> Sistema SaaS & App Multi-Tenant
          </div>
          
          <h1 class="hero-title text-dark mb-3">
            Control total de tu <span class="text-primary">estacionamiento</span> en tiempo real
          </h1>
          
          <p class="hero-subtitle mb-4">
            La plataforma moderna para registrar entradas, salidas, imprimir tickets térmicos con QR, generar cortes de caja y consultar ingresos en la nube desde cualquier dispositivo.
          </p>

          <!-- Acciones CTA -->
          <div class="d-flex flex-wrap gap-3 mb-4 justify-content-center justify-content-lg-start hero-actions-container">
            <a href="./app/?v=registro" class="btn btn-primary-custom btn-lg shadow-sm">
              <i class="fa-solid fa-gift"></i>
              <span>Comenzar Prueba Gratis</span>
            </a>
            <a href="./app/" class="btn btn-outline-custom btn-lg">
              <i class="fa-solid fa-desktop"></i>
              <span>Panel de Clientes</span>
            </a>
          </div>

          <!-- Beneficios Rápidos -->
          <div class="d-flex flex-wrap gap-3 gap-sm-4 text-muted small justify-content-center justify-content-lg-start">
            <div><i class="fa-solid fa-circle-check text-success me-1"></i> 7 Días Gratis</div>
            <div><i class="fa-solid fa-circle-check text-success me-1"></i> Tablets Ilimitadas</div>
            <div><i class="fa-solid fa-circle-check text-success me-1"></i> Sin Tarjeta</div>
          </div>
        </div>

        <div class="col-lg-6">
          <!-- Mockup Visual de Patio en Vivo -->
          <div class="mockup-container">
            <div class="mockup-header">
              <div class="mockup-dot bg-danger"></div>
              <div class="mockup-dot bg-warning"></div>
              <div class="mockup-dot bg-success"></div>
              <span class="text-white-50 small ms-2 font-monospace" style="font-size: 0.78rem;">parkingpro.com/app/</span>
            </div>

            <div class="p-3 bg-dark rounded-4 text-white">
              <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary border-opacity-50">
                <div>
                  <small class="text-secondary text-uppercase fw-bold" style="font-size: 0.7rem;">Estacionamiento Conectado</small>
                  <h6 class="fw-bold text-white mb-0" style="font-size: 0.95rem;">Patio Central #1</h6>
                </div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill small">
                  <i class="fa-solid fa-circle me-1" style="font-size: 7px;"></i> En Línea
                </span>
              </div>

              <!-- Estadísticas en 3 columnas -->
              <div class="row g-2 text-center mb-3">
                <div class="col-4">
                  <div class="p-2 bg-secondary bg-opacity-25 rounded-3 border border-secondary border-opacity-25">
                    <small class="text-white-50 d-block" style="font-size: 0.7rem;">Adentro</small>
                    <strong class="fs-5 text-info">28</strong>
                  </div>
                </div>
                <div class="col-4">
                  <div class="p-2 bg-secondary bg-opacity-25 rounded-3 border border-secondary border-opacity-25">
                    <small class="text-white-50 d-block" style="font-size: 0.7rem;">Cobrados</small>
                    <strong class="fs-5 text-success">142</strong>
                  </div>
                </div>
                <div class="col-4">
                  <div class="p-2 bg-secondary bg-opacity-25 rounded-3 border border-secondary border-opacity-25">
                    <small class="text-white-50 d-block" style="font-size: 0.7rem;">Ingresos</small>
                    <strong class="fs-5 text-warning">$3,850</strong>
                  </div>
                </div>
              </div>

              <!-- Último Ticket -->
              <div class="p-2.5 p-sm-3 bg-secondary bg-opacity-10 rounded-3 border border-secondary border-opacity-25 text-start">
                <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary text-white p-2 rounded-3 fs-6">
                      <i class="fa-solid fa-car"></i>
                    </div>
                    <div>
                      <strong class="d-block text-white" style="font-size: 0.85rem;">Ticket #143</strong>
                      <small class="text-white-50" style="font-size: 0.75rem;">Placas: JX-8821 · Entrada 16:42</small>
                    </div>
                  </div>
                  <span class="badge bg-primary px-2.5 py-1">Cobrado</span>
                </div>
              </div>

            </div>
          </div>
        </div>

      </div>
    </div>
  </header>

  <!-- ==========================================
       3. CARACTERÍSTICAS DESTACADAS
       ========================================== -->
  <section id="caracteristicas" class="py-5 bg-light">
    <div class="container-xl px-3 px-sm-4 py-3">
      
      <div class="text-center mx-auto mb-5" style="max-width: 680px;">
        <span class="text-primary fw-bold text-uppercase small tracking-wide">Ventajas de ParkingPro</span>
        <h2 class="fw-bold mt-2 fs-2">Todo lo que tu estacionamiento necesita para operar</h2>
        <p class="text-muted small">Diseñado para ser veloz, intuitivo y sin fallas, incluso en horas pico de alta afluencia.</p>
      </div>

      <div class="row g-3 g-md-4">
        
        <div class="col-12 col-md-6 col-lg-4">
          <div class="feature-card">
            <div class="feature-icon bg-primary bg-opacity-10 text-primary">
              <i class="fa-solid fa-tablet-screen-button"></i>
            </div>
            <h5 class="fw-bold mb-2">App Móvil para Tablets</h5>
            <p class="text-muted small mb-0">Instala en cualquier tablet Android o iPad. Registra autos, cobra con tarifas configurables e imprime tickets al instante.</p>
          </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
          <div class="feature-card">
            <div class="feature-icon bg-success bg-opacity-10 text-success">
              <i class="fa-solid fa-print"></i>
            </div>
            <h5 class="fw-bold mb-2">Tickets Térmicos con QR</h5>
            <p class="text-muted small mb-0">Compatible con cualquier impresora térmica bluetooth 58mm. Al salir el auto, solo escaneas el QR del ticket con la cámara.</p>
          </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
          <div class="feature-card">
            <div class="feature-icon bg-warning bg-opacity-10 text-warning-emphasis">
              <i class="fa-solid fa-camera"></i>
            </div>
            <h5 class="fw-bold mb-2">Lector Inteligente con IA</h5>
            <p class="text-muted small mb-0">Tómale foto al auto con la tablet y la inteligencia artificial detectará las placas, marca y modelo automáticamente.</p>
          </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
          <div class="feature-card">
            <div class="feature-icon bg-info bg-opacity-10 text-info">
              <i class="fa-solid fa-cash-register"></i>
            </div>
            <h5 class="fw-bold mb-2">Cortes de Caja Blindados</h5>
            <p class="text-muted small mb-0">El sistema verifica conexión, sube todos los registros a tu base de datos, imprime el ticket de corte y resetea los folios al #1.</p>
          </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
          <div class="feature-card">
            <div class="feature-icon bg-danger bg-opacity-10 text-danger">
              <i class="fa-solid fa-infinity"></i>
            </div>
            <h5 class="fw-bold mb-2">Tablets Ilimitadas</h5>
            <p class="text-muted small mb-0">Enlaza tantas tablets como desees con tu código único <strong class="text-dark">PK-XXXX</strong> o escaneando un código QR. Sin costos por tablet extra.</p>
          </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
          <div class="feature-card">
            <div class="feature-icon bg-primary bg-opacity-10 text-primary">
              <i class="fa-solid fa-cloud-arrow-up"></i>
            </div>
            <h5 class="fw-bold mb-2">Panel Web en la Nube</h5>
            <p class="text-muted small mb-0">Ingresa desde tu celular o laptop en cualquier parte del mundo para ver reportes de ingresos, gráficas y estado del negocio en vivo.</p>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ==========================================
       4. CÓMO FUNCIONA
       ========================================== -->
  <section id="como-funciona" class="py-5">
    <div class="container-xl px-3 px-sm-4 py-3">
      
      <div class="text-center mb-5">
        <span class="text-primary fw-bold text-uppercase small tracking-wide">Puesta en Marcha en 3 Minutos</span>
        <h2 class="fw-bold mt-2 fs-2">¿Cómo comenzar a utilizar ParkingPro?</h2>
      </div>

      <div class="row g-4 text-center">
        <div class="col-12 col-md-4">
          <div class="p-3 p-sm-4">
            <div class="step-badge">1</div>
            <h5 class="fw-bold mb-2">Crea tu Cuenta Gratis</h5>
            <p class="text-muted small">Regístrate en 30 segundos sin necesidad de tarjeta. Obtendrás 7 días completos de servicio y tu propio código corto de vinculación.</p>
          </div>
        </div>

        <div class="col-12 col-md-4">
          <div class="p-3 p-sm-4">
            <div class="step-badge">2</div>
            <h5 class="fw-bold mb-2">Enlaza tus Tablets</h5>
            <p class="text-muted small">Abre la app en tus tablets, escribe tu código de vinculación o escanea el código QR que se muestra en tu panel web.</p>
          </div>
        </div>

        <div class="col-12 col-md-4">
          <div class="p-3 p-sm-4">
            <div class="step-badge">3</div>
            <h5 class="fw-bold mb-2">¡Listo para Operar!</h5>
            <p class="text-muted small">Registra entradas, imprime tickets térmicos y realiza cortes de turno con respaldo automático en la nube.</p>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ==========================================
       5. PLANES Y PRECIOS (RESPONSIVO Y SIN RECORTES)
       ========================================== -->
  <section id="precios" class="py-5 bg-light">
    <div class="container-xl px-3 px-sm-4 py-3">
      
      <div class="text-center mb-5">
        <span class="text-primary fw-bold text-uppercase small tracking-wide">Precios Transparentes</span>
        <h2 class="fw-bold mt-2 fs-2">Elige el plan ideal para tu estacionamiento</h2>
        <p class="text-muted small">Todos los planes incluyen tablets ilimitadas, base de datos dedicada y soporte técnico.</p>
      </div>

      <div class="row g-4 justify-content-center align-items-stretch pt-2">
        
        <!-- Plan Mensual -->
        <div class="col-12 col-md-6 col-lg-4 pt-3">
          <div class="pricing-card p-4 h-100 d-flex flex-column justify-content-between" style="margin-top: 14px;">
            <div>
              <span class="badge bg-secondary-subtle text-secondary px-3 py-1 rounded-pill fw-bold small mb-3">FLEXIBLE</span>
              <h4 class="fw-bold mb-1 text-dark">Plan Mensual</h4>
              <p class="text-muted small mb-3">Ideal para iniciar mes a mes</p>
              <div class="display-5 fw-bold text-dark mb-1">$250<span class="fs-6 fw-normal text-muted">/mes</span></div>
              <small class="text-muted d-block mb-4">Pesos Mexicanos (MXN)</small>

              <ul class="list-unstyled text-start small mb-4 d-flex flex-column gap-2 text-secondary">
                <li><i class="fa-solid fa-check text-success me-2"></i><strong>Tablets ilimitadas</strong></li>
                <li><i class="fa-solid fa-check text-success me-2"></i>Entradas y salidas en tiempo real</li>
                <li><i class="fa-solid fa-check text-success me-2"></i>Cortes de caja automáticos</li>
                <li><i class="fa-solid fa-check text-success me-2"></i>Panel web en la nube</li>
              </ul>
            </div>
            <a href="./app/?v=registro" class="btn btn-outline-custom fw-bold py-2.5 rounded-pill w-100 mt-2">
              Comenzar Prueba Gratis
            </a>
          </div>
        </div>

        <!-- Plan Anual (Destacado) -->
        <div class="col-12 col-md-6 col-lg-4 pt-3" style="overflow: visible !important;">
          <div class="pricing-card popular p-4 h-100 d-flex flex-column justify-content-between position-relative" style="margin-top: 14px; overflow: visible !important;">
            
            <!-- Badge Flotante con Espacio Garantizado -->
            <div class="badge-popular-floating">
              <span class="badge bg-primary text-white rounded-pill fw-bold text-uppercase">
                ⭐ MÁS POPULAR · AHORRA 2 MESES
              </span>
            </div>

            <div class="pt-2">
              <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-bold small mb-3">ANUAL RECOMENDADO</span>
              <h4 class="fw-bold mb-1 text-primary">Plan Anual</h4>
              <p class="text-muted small mb-3">365 días de servicio ininterrumpido</p>
              <div class="display-5 fw-bold text-primary mb-1">$2,500<span class="fs-6 fw-normal text-muted">/año</span></div>
              <small class="text-muted d-block mb-4">Equivale a solo $208/mes (Ahorras $500)</small>

              <ul class="list-unstyled text-start small mb-4 d-flex flex-column gap-2 text-secondary">
                <li><i class="fa-solid fa-check text-primary me-2"></i><strong>Tablets ilimitadas</strong></li>
                <li><i class="fa-solid fa-check text-primary me-2"></i><strong>Soporte prioritario</strong></li>
                <li><i class="fa-solid fa-check text-primary me-2"></i>Actualizaciones continuas</li>
                <li><i class="fa-solid fa-check text-primary me-2"></i>Respaldos automáticos en la nube</li>
              </ul>
            </div>
            <a href="./app/?v=registro" class="btn btn-primary-custom fw-bold py-2.5 rounded-pill w-100 shadow-sm mt-2">
              Elegir Plan Anual
            </a>
          </div>
        </div>

        <!-- Plan Ilimitado -->
        <div class="col-12 col-md-6 col-lg-4 pt-3">
          <div class="pricing-card p-4 h-100 d-flex flex-column justify-content-between" style="background: linear-gradient(180deg, #FFFFFF 0%, #F8FAFC 100%); margin-top: 14px;">
            <div>
              <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-1 rounded-pill fw-bold small mb-3">PAGO ÚNICO</span>
              <h4 class="fw-bold mb-1 text-dark">Plan Ilimitado</h4>
              <p class="text-muted small mb-3">Sin mensualidades nunca más</p>
              <div class="display-5 fw-bold text-dark mb-1">$4,999<span class="fs-6 fw-normal text-muted">/vitalicio</span></div>
              <small class="text-muted d-block mb-4">Un solo pago de por vida</small>

              <ul class="list-unstyled text-start small mb-4 d-flex flex-column gap-2 text-secondary">
                <li><i class="fa-solid fa-infinity text-warning me-2"></i><strong>Acceso de por vida sin vencimiento</strong></li>
                <li><i class="fa-solid fa-check text-success me-2"></i><strong>Tablets ilimitadas</strong></li>
                <li><i class="fa-solid fa-check text-success me-2"></i>Cero cobros recurrentes</li>
                <li><i class="fa-solid fa-check text-success me-2"></i>Todas las funciones incluidas</li>
              </ul>
            </div>
            <a href="./app/?v=registro" class="btn btn-outline-custom fw-bold py-2.5 rounded-pill w-100 mt-2">
              Elegir Vitalicio
            </a>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ==========================================
       6. PREGUNTAS FRECUENTES (FAQ)
       ========================================== -->
  <section id="faq" class="py-5">
    <div class="container-xl px-3 px-sm-4 py-3" style="max-width: 820px;">
      
      <div class="text-center mb-5">
        <span class="text-primary fw-bold text-uppercase small tracking-wide">Resolvemos tus Dudas</span>
        <h2 class="fw-bold mt-2 fs-2">Preguntas Frecuentes</h2>
      </div>

      <div class="accordion accordion-flush" id="faqAccordion">
        
        <div class="accordion-item border rounded-3 mb-3 p-1">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
              ¿Qué equipo necesito para usar ParkingPro?
            </button>
          </h2>
          <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
            <div class="accordion-body text-muted small lh-base">
              Solo necesitas cualquier tablet Android o iPad para el cobrador en caseta y una impresora térmica bluetooth estándar de 58mm (las que cuestan ~$400-$700 MXN en Amazon o MercadoLibre). El dueño puede consultar reportes desde cualquier celular o PC.
            </div>
          </div>
        </div>

        <div class="accordion-item border rounded-3 mb-3 p-1">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
              ¿Puedo tener más de una tablet cobrando al mismo tiempo?
            </button>
          </h2>
          <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
            <div class="accordion-body text-muted small lh-base">
              ¡Sí! Puedes conectar tantas tablets como necesites sin costo extra. Cada tablet cuenta con su propio identificador y prefijo de folio (ej. T1, T2) para que nunca se confundan los tickets.
            </div>
          </div>
        </div>

        <div class="accordion-item border rounded-3 mb-3 p-1">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
              ¿Qué pasa si se va la luz o el internet en el estacionamiento?
            </button>
          </h2>
          <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
            <div class="accordion-body text-muted small lh-base">
              La app de la tablet funciona localmente, por lo que puedes seguir registrando entradas, cobrando salidas e imprimiendo tickets con la batería de la tablet y de la impresora. En cuanto regrese la conexión, todo se sincroniza con la nube automáticamente.
            </div>
          </div>
        </div>

        <div class="accordion-item border rounded-3 mb-3 p-1">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
              ¿Cómo funciona la prueba gratuita de 7 días?
            </button>
          </h2>
          <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
            <div class="accordion-body text-muted small lh-base">
              Solo creas tu cuenta con tu correo y nombre de negocio. Inmediatamente se genera tu base de datos y tu código de enlace. Tienes 7 días para usar todas las funciones sin restricciones. Si te gusta, pagas tu plan fácilmente con PayPal o tarjeta.
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ==========================================
       7. FOOTER RESPONSIVO
       ========================================== -->
  <footer class="py-5 bg-dark text-white">
    <div class="container-xl px-3 px-sm-4">
      <div class="row gy-4">
        
        <div class="col-12 col-lg-5 text-center text-lg-start">
          <div class="d-flex align-items-center gap-2 mb-3 justify-content-center justify-content-lg-start">
            <div class="navbar-brand-logo" style="width: 36px; height: 36px; font-size: 1.1rem;">
              <i class="fa-solid fa-square-parking"></i>
            </div>
            <span class="fw-bold fs-4 brand-font text-white">Parking<span class="text-primary">Pro</span></span>
          </div>
          <p class="text-white-50 small pe-lg-4 mb-0">
            Plataforma SaaS para la gestión, control vehicular y cobro en estacionamientos públicos, pensiones y plazas comerciales.
          </p>
        </div>

        <div class="col-6 col-lg-3 text-start">
          <h6 class="fw-bold mb-3 text-uppercase small text-white-50">Acceso al Sistema</h6>
          <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
            <li><a href="./app/" class="text-white-50 text-decoration-none hover-white"><i class="fa-solid fa-user me-2 text-primary"></i>Panel de Clientes</a></li>
            <li><a href="./app/?v=registro" class="text-white-50 text-decoration-none hover-white"><i class="fa-solid fa-user-plus me-2 text-primary"></i>Crear Cuenta</a></li>
            <li><a href="./admin/" class="text-white-50 text-decoration-none hover-white"><i class="fa-solid fa-shield-halved me-2 text-warning"></i>SuperAdmin</a></li>
          </ul>
        </div>

        <div class="col-6 col-lg-4 text-start">
          <h6 class="fw-bold mb-3 text-uppercase small text-white-50">Soporte y Contacto</h6>
          <p class="small text-white-50 mb-2">Desarrollado con altos estándares de seguridad y alta disponibilidad.</p>
          <div class="small text-white-50">
            <i class="fa-solid fa-envelope me-2 text-primary"></i>soporte@estacionamiento.com
          </div>
        </div>

      </div>

      <div class="border-top border-secondary border-opacity-25 mt-4 pt-4 d-flex flex-column flex-sm-row justify-content-between align-items-center small text-white-50 text-center text-sm-start">
        <div>&copy; 2026 ParkingPro. Todos los derechos reservados.</div>
        <div class="mt-2 mt-sm-0">
          <a href="./admin/" class="text-white-50 text-decoration-none small"><i class="fa-solid fa-lock me-1"></i>Acceso SuperAdmin</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
