<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acceso Admin | ParkingPro</title>
  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="vistas/assets/images/favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="vistas/assets/images/favicon.png">
  <link rel="apple-touch-icon" href="vistas/assets/images/favicon.png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

  <style>
    body {
      min-height: 100vh;
      font-family: 'Inter', sans-serif;
      background: radial-gradient(1000px 500px at 10% 10%, rgba(2, 132, 199, 0.15), transparent 70%),
                  radial-gradient(800px 600px at 90% 90%, rgba(16, 185, 129, 0.1), transparent 70%),
                  #0F172A;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #F8FAFC;
    }
    .login-card {
      background: rgba(30, 41, 59, 0.85);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 24px;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
      width: 100%;
      max-width: 440px;
    }
    .brand-icon {
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, #0284C7 0%, #0369A1 100%);
      border-radius: 16px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: #FFFFFF;
      font-size: 28px;
      box-shadow: 0 10px 20px -5px rgba(2, 132, 199, 0.5);
    }
    .form-control {
      background: rgba(15, 23, 42, 0.6) !important;
      border: 1px solid rgba(255, 255, 255, 0.15) !important;
      color: #FFFFFF !important;
      border-radius: 12px;
      padding: 12px 14px;
    }
    .form-control::placeholder {
      color: #94A3B8 !important;
      opacity: 0.9 !important;
    }
    .form-control::-webkit-input-placeholder {
      color: #94A3B8 !important;
      opacity: 0.9 !important;
    }
    .form-control:-moz-placeholder {
      color: #94A3B8 !important;
      opacity: 0.9 !important;
    }
    .form-control::-moz-placeholder {
      color: #94A3B8 !important;
      opacity: 0.9 !important;
    }
    .form-control:-ms-input-placeholder {
      color: #94A3B8 !important;
      opacity: 0.9 !important;
    }
    .form-control:focus {
      background: rgba(15, 23, 42, 0.9) !important;
      border-color: #38BDF8 !important;
      color: #FFFFFF !important;
      box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.2) !important;
    }
    .input-group-text {
      background: rgba(15, 23, 42, 0.6) !important;
      border: 1px solid rgba(255, 255, 255, 0.15) !important;
      color: #94A3B8 !important;
      border-radius: 12px;
    }
    .btn-login {
      background: linear-gradient(135deg, #0284C7 0%, #0369A1 100%);
      border: none;
      color: #FFFFFF;
      font-weight: 700;
      border-radius: 12px;
      padding: 14px;
      letter-spacing: 0.5px;
      transition: all 0.2s ease;
    }
    .btn-login:hover {
      background: linear-gradient(135deg, #0369A1 0%, #075985 100%);
      transform: translateY(-1px);
      box-shadow: 0 10px 20px -5px rgba(2, 132, 199, 0.5);
    }
  </style>
</head>

<body>

  <div class="container p-3">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-5">
        
        <div class="login-card p-4 p-sm-5">
          <div class="text-center mb-4">
            <div class="mb-3">
              <img src="vistas/assets/images/favicon.png" width="64" height="64" class="rounded-3 shadow" alt="ParkingPro">
            </div>
            <h3 class="fw-bold mb-1 font-sora">Panel de Control</h3>
            <p class="text-secondary small mb-0">Acceso exclusivo para administradores del SaaS</p>
          </div>

          <form id="formLogin">
            <div class="mb-3">
              <label for="email" class="form-label small fw-semibold text-secondary">Correo Electrónico</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
                <input type="email" class="form-control" id="email" name="email" placeholder="admin@parkingpro.top" required autocomplete="email">
              </div>
            </div>

            <div class="mb-4">
              <label for="pass" class="form-label small fw-semibold text-secondary">Contraseña</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                <input type="password" class="form-control" id="pass" name="pass" placeholder="••••••••" required autocomplete="current-password">
                <span class="input-group-text" style="cursor: pointer;" onclick="togglePasswordVisibility()">
                  <i class="fa-regular fa-eye" id="toggleIcon"></i>
                </span>
              </div>
            </div>

            <button type="submit" class="btn btn-login w-100 mb-3" id="bIngresarLogin">
              <i class="fa-solid fa-right-to-bracket me-2"></i> Iniciar Sesión
            </button>
          </form>

          <div class="text-center mt-3 pt-3 border-top border-secondary border-opacity-25">
            <a href="../" class="text-secondary small text-decoration-none">
              <i class="fa-solid fa-arrow-left me-1"></i> Volver a la página principal
            </a>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="vistas/assets/js/login.js"></script>

</body>

</html>
