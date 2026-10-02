<?php
date_default_timezone_set('America/Mexico_City');
require_once __DIR__ . '/../modelo/m_modelo.php';
require_once __DIR__ . '/c_login.php';
require_once __DIR__ . '/c_dashboard.php';
require_once __DIR__ . '/c_clientes.php';
require_once __DIR__ . '/c_reportes_admin.php';
require_once __DIR__ . '/c_cuenta.php';

class controller
{
    public function _layouts()
    {
        $file = __DIR__ . '/../vistas/v_html.php';
        if (!file_exists($file)) {
            return "Error: Layout v_html no encontrado";
        }
        $pagina = file_get_contents($file);
        $pagina = $this->remplazar($pagina, 'v_html');
        return $pagina;
    }

    public function _contenido($vista)
    {
        $vista = basename(str_replace('\\', '/', $vista));
        $file = __DIR__ . "/../vistas/$vista.php";

        if (!file_exists($file)) {
            return '<div class="alert alert-danger m-3"><i class="fa-solid fa-triangle-exclamation me-2"></i>La vista solicitada no existe.</div>';
        }

        $pagina = file_get_contents($file);
        $pagina = $this->remplazar($pagina, $vista);
        return $pagina;
    }

    public function _consultar($metodo)
    {
        if (class_exists($metodo)) {
            $objeto = new $metodo();
            $objeto->_consultar();
        } else {
            echo json_encode(['error' => "Controlador $metodo no encontrado"]);
        }
    }

    public function _insertar($metodo)
    {
        if (class_exists($metodo)) {
            $objeto = new $metodo();
            $objeto->_insertar();
        } else {
            echo "Error: Controlador $metodo no encontrado";
        }
    }

    public function _modificar($metodo)
    {
        if (class_exists($metodo)) {
            $objeto = new $metodo();
            $objeto->_modificar();
        } else {
            echo "Error: Controlador $metodo no encontrado";
        }
    }

    public function _eliminar($metodo)
    {
        if (class_exists($metodo)) {
            $objeto = new $metodo();
            $objeto->_eliminar();
        } else {
            echo "Error: Controlador $metodo no encontrado";
        }
    }

    public function _detalles($metodo)
    {
        if (class_exists($metodo)) {
            $objeto = new $metodo();
            $objeto->_detalles();
        } else {
            echo "Error: Controlador $metodo no encontrado";
        }
    }

    public function remplazar($pagina, $nombre)
    {
        $nombreAdmin = $_SESSION['user_parking_admin']['Nombre'] ?? 'Administrador';
        $correoAdmin = $_SESSION['user_parking_admin']['Correo'] ?? 'admin@parkingpro.top';
        $palabras = strtoupper(mb_substr($nombreAdmin, 0, 2, 'UTF-8'));

        $pagina = str_replace('#nombre#', htmlspecialchars($nombreAdmin), $pagina);
        $pagina = str_replace('#correo#', htmlspecialchars($correoAdmin), $pagina);
        $pagina = str_replace('#palabras#', htmlspecialchars($palabras), $pagina);
        $pagina = str_replace('#v#', time(), $pagina);

        return $pagina;
    }
}
?>
