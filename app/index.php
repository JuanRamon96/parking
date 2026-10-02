<?php
ob_start(function ($buffer) {
    return preg_replace('/^\xEF\xBB\xBF+/', '', $buffer);
});
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 86400 * 30);
    session_set_cookie_params([
        'lifetime' => 86400 * 30,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

require_once __DIR__ . "/controladores/c_controller.php";
$controller = new controller();

if (isset($_SESSION['user_estacionamiento'])) {
    extract($_POST);

    if (isset($metodo)) {
        if ($metodo == 'cambiar') {
            echo $controller->_contenido($accion);
        } else if ($metodo == 'consultar') {
            $controller->_consultar($accion);
        } else if ($metodo == 'insertar') {
            $controller->_insertar($accion);
        } else if ($metodo == 'modificar') {
            $controller->_modificar($accion);
        } else if ($metodo == 'eliminar') {
            $controller->_eliminar($accion);
        } else if ($metodo == 'detalles') {
            $controller->_detalles($accion);
        } else if ($metodo == 'renovar') {
            echo "Sesion renovada";
        }
    } else {
        echo $controller->_layouts();
    }
    return false;
} else {
    if (isset($_POST['accion']) && $_POST['accion'] == 'registro') {
        $controller->_insertar('registro');
    } else if (isset($_POST['accion']) && $_POST['accion'] == 'olvido' && isset($_POST['correo'])) {
        $controller->_modificar('login');
    } else if (isset($_POST['accion']) && $_POST['accion'] == 'login' && isset($_POST['correo']) && isset($_POST['contrasena'])) {
        $controller->_consultar('login');
    } else if (isset($_POST['metodo']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
        header('Content-Type: application/json');
        echo json_encode(array('session_expired' => true, 'error' => 'Sesion expirada'));
    } else if ((isset($_GET['v']) && $_GET['v'] === 'registro') || (isset($_GET['vista']) && $_GET['vista'] === 'registro')) {
        echo $controller->_contenido('v_registro');
    } else {
        echo $controller->_contenido('v_login');
    }
}
?>
