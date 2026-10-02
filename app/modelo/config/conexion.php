<?php
class conexion {
    private $_connection;
    private $_host = "localhost";
    private $_username = "root";
    private $_password = "";
    private $_database = "parking_subs";

    public function __construct($database = null)
    {   
        $targetDB = $database ?: $this->_database;

        if (!$database && isset($_SESSION['user_parking_bd']) && !empty($_SESSION['user_parking_bd'])) {
            $targetDB = $_SESSION['user_parking_bd'];
        }

        $this->_connection = new mysqli($this->_host, $this->_username, $this->_password, $targetDB);
        
        if (mysqli_connect_error()) {
            // Si la base de datos solicitada no existe, fallback a parking_subs
            $this->_connection = new mysqli($this->_host, $this->_username, $this->_password, $this->_database);
            if (mysqli_connect_error()) {
                trigger_error("Error al conectar con la Base de datos: " . mysqli_connect_error(), E_USER_ERROR);
            }
        }
        
        $this->_connection->set_charset("utf8mb4");
        return $this->_connection;
    }
}
?>
