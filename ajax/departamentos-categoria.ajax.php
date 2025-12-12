<?php
require_once "../modelos/conexion.php";

if (isset($_POST['categoria_id']) && is_numeric($_POST['categoria_id'])) {
    $categoria_id = intval($_POST['categoria_id']);
    
    $conn = new mysqli("localhost", "root", "", "helpdesk");
    
    if ($conn->connect_error) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error de conexión a la base de datos'
        ]);
        exit;
    }
    
    $queryCategoria = "SELECT departamento_id FROM categorias WHERE id = ?";
    $stmtCategoria = $conn->prepare($queryCategoria);
    $stmtCategoria->bind_param("i", $categoria_id);
    $stmtCategoria->execute();
    $resultadoCategoria = $stmtCategoria->get_result();
    
    if ($resultadoCategoria->num_rows > 0) {
        $categoria = $resultadoCategoria->fetch_assoc();
        $departamento_id = $categoria['departamento_id'];
        
        if ($departamento_id) {
            $queryDepartamento = "SELECT id, nombre FROM departamentos WHERE id = ? ORDER BY nombre";
            $stmtDepartamento = $conn->prepare($queryDepartamento);
            $stmtDepartamento->bind_param("i", $departamento_id);
            $stmtDepartamento->execute();
            $resultadoDepartamento = $stmtDepartamento->get_result();
            
            $departamentos = [];
            while ($row = $resultadoDepartamento->fetch_assoc()) {
                $departamentos[] = $row;
            }
            
            $stmtDepartamento->close();
        } else {
            $queryTodos = "SELECT id, nombre FROM departamentos ORDER BY nombre";
            $resultadoTodos = $conn->query($queryTodos);
            
            $departamentos = [];
            if ($resultadoTodos) {
                while ($row = $resultadoTodos->fetch_assoc()) {
                    $departamentos[] = $row;
                }
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'departamentos' => $departamentos
        ]);
        
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Categoría no encontrada'
        ]);
    }
    
    $stmtCategoria->close();
    $conn->close();
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Categoría no válida'
    ]);
}
?>
