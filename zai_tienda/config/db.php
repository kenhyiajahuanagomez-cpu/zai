<?php
/**
 * Conexión a la base de datos usando MySQLi
 * Proyecto: BodegaViva - Tienda de Abarrotes
 */

require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        // Suprimir el warning nativo para mostrar nuestro error personalizado
        $this->conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            $this->mostrarErrorBD();
        }
        $this->conn->set_charset(DB_CHARSET);
    }

    private function mostrarErrorBD() {
        // Si es una petición AJAX, devolver JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error de conexión a la base de datos',
                'mensaje' => 'No se pudo conectar. Verifica que MySQL esté corriendo en XAMPP.'
            ]);
            exit;
        }
        // Página HTML amigable
        http_response_code(500);
        echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error de conexión | BodegaViva</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Quicksand:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:"Poppins",sans-serif; }
        body {
            min-height:100vh; display:flex; align-items:center; justify-content:center;
            background:linear-gradient(135deg,#0E5A3C,#5DA838);
            padding:20px;
        }
        .card {
            background:white; max-width:560px; width:100%; padding:40px;
            border-radius:24px; box-shadow:0 30px 80px rgba(0,0,0,0.3);
            text-align:center;
        }
        .icon-err {
            width:90px; height:90px; margin:0 auto 20px;
            background:rgba(229,57,53,0.1); border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            color:#E53935; font-size:42px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow:0 0 0 0 rgba(229,57,53,0.4); }
            70% { box-shadow:0 0 0 20px rgba(229,57,53,0); }
            100% { box-shadow:0 0 0 0 rgba(229,57,53,0); }
        }
        h1 { color:#0E5A3C; font-family:"Quicksand",sans-serif; font-size:26px; margin-bottom:10px; }
        .subtitle { color:#6B7568; font-size:14px; margin-bottom:24px; }
        .steps {
            text-align:left; background:#F7F8F5; padding:20px;
            border-radius:14px; margin:24px 0;
        }
        .steps h3 { font-size:14px; color:#0E5A3C; margin-bottom:12px; }
        .steps ol { padding-left:20px; }
        .steps li { margin-bottom:10px; font-size:13px; color:#3D4A41; line-height:1.6; }
        .steps code {
            background:#0E5A3C; color:#FFD23F; padding:2px 8px;
            border-radius:4px; font-size:12px;
        }
        .btn {
            display:inline-flex; align-items:center; gap:8px;
            background:#0E5A3C; color:white; padding:12px 28px;
            border-radius:30px; text-decoration:none; font-weight:600;
            transition:all 0.3s;
        }
        .btn:hover { background:#5DA838; transform:translateY(-2px); }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-err"><i class="fa-solid fa-database"></i></div>
        <h1>No se pudo conectar a la base de datos</h1>
        <p class="subtitle">Estamos teniendo problemas para acceder a la información. Sigue estos pasos para solucionarlo:</p>
        
        <div class="steps">
            <h3><i class="fa-solid fa-list-check"></i> Pasos para solucionar:</h3>
            <ol>
                <li>Abre el <strong>Panel de Control de XAMPP</strong></li>
                <li>Verifica que los servicios <strong>Apache</strong> y <strong>MySQL</strong> estén iniciados (en verde)</li>
                <li>Si no están, da clic en <strong>Start</strong> para cada uno</li>
                <li>Importa la base de datos desde phpMyAdmin usando el archivo <code>config/zai_tienda.sql</code></li>
                <li>Revisa las credenciales en <code>config/config.php</code></li>
            </ol>
        </div>
        
        <a href="javascript:location.reload()" class="btn">
            <i class="fa-solid fa-rotate-right"></i> Reintentar
        </a>
    </div>
</body>
</html>';
        exit;
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    /**
     * Ejecuta una consulta preparada y devuelve el resultado
     * Uso: $db->query("SELECT * FROM productos WHERE id = ?", ['i', $id])
     */
    public function query($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die('Error en la consulta: ' . $this->conn->error);
        }
        if (!empty($params)) {
            $types = $params[0];
            $values = array_slice($params, 1);
            $stmt->bind_param($types, ...$values);
        }
        $stmt->execute();
        return $stmt;
    }

    /**
     * Obtiene múltiples registros
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }

    /**
     * Obtiene un solo registro
     */
    public function fetchOne($sql, $params = []) {
        $rows = $this->fetchAll($sql, $params);
        return !empty($rows) ? $rows[0] : null;
    }

    /**
     * Inserta y devuelve el último ID insertado
     */
    public function insert($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $id = $this->conn->insert_id;
        $stmt->close();
        return $id;
    }

    /**
     * Ejecuta UPDATE o DELETE y devuelve las filas afectadas
     */
    public function execute($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $affected = $this->conn->affected_rows;
        $stmt->close();
        return $affected;
    }

    public function escape($value) {
        return $this->conn->real_escape_string($value);
    }
}

// Instancia global de la base de datos
$db = Database::getInstance();
$conn = $db->getConnection();
