<?php

$servidor = Ruta::ctrRutaServidor();
//$plantilla = ControladorInterfaz::ctrSeleccionarPlantilla();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_ticket']) && isset($_GET['ruta']) && $_GET['ruta'] === 'contactenos') {
    error_log("POST recibido en plantilla.php para contactenos: " . print_r($_POST, true));
    error_log("Procesando creación de ticket...");
    
    $conn = new mysqli("localhost", "root", "", "helpdesk");
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }
    
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $nombre_completo = trim($_POST['nombre_completo']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $categoria_id = intval($_POST['categoria_id']);
    $prioridad_id = intval($_POST['prioridad_id']);
    $departamento_id = intval($_POST['departamento_id']);
    
    error_log("Datos del formulario - Título: $titulo, Email: $email, Categoría: $categoria_id");
    
    $errores = [];
    
    if (empty($titulo)) {
        $errores[] = 'El título es obligatorio.';
    } elseif (strlen($titulo) < 5) {
        $errores[] = 'El título debe tener al menos 5 caracteres.';
    } elseif (strlen($titulo) > 200) {
        $errores[] = 'El título no puede exceder 200 caracteres.';
    }
    
    if (empty($descripcion)) {
        $errores[] = 'La descripción es obligatoria.';
    } elseif (strlen($descripcion) < 10) {
        $errores[] = 'La descripción debe tener al menos 10 caracteres.';
    } elseif (strlen($descripcion) > 1000) {
        $errores[] = 'La descripción no puede exceder 1000 caracteres.';
    }
    
    if (empty($nombre_completo)) {
        $errores[] = 'El nombre completo es obligatorio.';
    } elseif (strlen($nombre_completo) < 3) {
        $errores[] = 'El nombre debe tener al menos 3 caracteres.';
    }
    
    if (empty($email)) {
        $errores[] = 'El email es obligatorio.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El email no tiene un formato válido.';
    }
    
    if (!empty($telefono) && !preg_match('/^[\+]?[0-9\s\-\(\)]{7,20}$/', $telefono)) {
        $errores[] = 'El formato del teléfono no es válido.';
    }
    
    if ($categoria_id <= 0) {
        $errores[] = 'Debe seleccionar una categoría.';
    }
    
    if ($prioridad_id <= 0) {
        $errores[] = 'Debe seleccionar una prioridad.';
    }
    
    if ($departamento_id <= 0) {
        $errores[] = 'Debe seleccionar un departamento.';
    }
    
    if (empty($errores)) {
        $validCategoria = $conn->query("SELECT id FROM categorias WHERE id = $categoria_id");
        $validPrioridad = $conn->query("SELECT id FROM prioridades WHERE id = $prioridad_id");
        $validDepartamento = $conn->query("SELECT id FROM departamentos WHERE id = $departamento_id");
        
        if ($validCategoria->num_rows === 0) {
            $errores[] = 'La categoría seleccionada no es válida.';
        }
        if ($validPrioridad->num_rows === 0) {
            $errores[] = 'La prioridad seleccionada no es válida.';
        }
        if ($validDepartamento->num_rows === 0) {
            $errores[] = 'El departamento seleccionado no es válido.';
        }
    }
    
    if (!empty($errores)) {
        $mensajeError = urlencode(implode('. ', $errores));
        header("Location: index.php?ruta=contactenos&error=1&mensaje=" . $mensajeError);
        exit();
    } else {
        $conn->begin_transaction();
        
        try {
            $stmtUser = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmtUser->bind_param("s", $email);
            $stmtUser->execute();
            $userResult = $stmtUser->get_result();
            
            if ($userResult->num_rows > 0) {
                $usuario = $userResult->fetch_assoc();
                $usuario_id = $usuario['id'];
                
                $stmtUpdate = $conn->prepare("UPDATE usuarios SET nombre = ?, telefono = ? WHERE id = ?");
                $stmtUpdate->bind_param("ssi", $nombre_completo, $telefono, $usuario_id);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            } else {
                $password_temp = password_hash('temporal123', PASSWORD_DEFAULT);
                
                $nombres = explode(' ', $nombre_completo, 2);
                $nombre = $nombres[0];
                $apellido = isset($nombres[1]) ? $nombres[1] : '';
                
                $stmtNewUser = $conn->prepare("INSERT INTO usuarios (nombre, apellido, email, emailEncriptado, usuario, telefono, password, perfil, estado, ultimo_login, departamento_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $emailEncriptado = base64_encode($email);
                $usuario_name = strtolower(str_replace(' ', '', $nombre . $apellido));
                $perfil = 'Cliente';
                $estado = 1;
                $ultimo_login = date('Y-m-d H:i:s');
                
                $stmtNewUser->bind_param("ssssssssiis", 
                    $nombre, $apellido, $email, $emailEncriptado, 
                    $usuario_name, $telefono, $password_temp, $perfil, $estado, $ultimo_login, $departamento_id
                );
                
                if (!$stmtNewUser->execute()) {
                    throw new Exception('Error al crear el usuario: ' . $stmtNewUser->error);
                }
                
                $usuario_id = $conn->insert_id;
                $stmtNewUser->close();
            }
            $stmtUser->close();
            
            $stmtTecnico = $conn->prepare("SELECT id FROM usuarios WHERE perfil IN ('admin', 'tecnico') ORDER BY id ASC LIMIT 1");
            $stmtTecnico->execute();
            $tecnicoResult = $stmtTecnico->get_result();
            $tecnico_id = $tecnicoResult->num_rows > 0 ? $tecnicoResult->fetch_assoc()['id'] : 1;
            $stmtTecnico->close();
            
            $stmtTicket = $conn->prepare("INSERT INTO tickets (titulo, descripcion, usuario_creador_id, tecnico_asignado_id, categoria_id, prioridad_id, estado, fecha_creacion, fecha_actualizacion, departamento_origen_id, departamento_asignado_id) VALUES (?, ?, ?, ?, ?, ?, 'abierto', NOW(), NOW(), ?, ?)");
            $stmtTicket->bind_param("ssiiiiii", $titulo, $descripcion, $usuario_id, $tecnico_id, $categoria_id, $prioridad_id, $departamento_id, $departamento_id);
            
            if (!$stmtTicket->execute()) {
                throw new Exception('Error al crear el ticket');
            }
            
            $ticketId = $conn->insert_id;
            $stmtTicket->close();
            
            $conn->commit();
            
            require_once "config/email.config.php";
            
            date_default_timezone_set("America/Caracas");
            
            $stmtTecnicoEmail = $conn->prepare("SELECT email, nombre, apellido FROM usuarios WHERE id = ?");
            $stmtTecnicoEmail->bind_param("i", $tecnico_id);
            $stmtTecnicoEmail->execute();
            $tecnicoData = $stmtTecnicoEmail->get_result()->fetch_assoc();
            $stmtTecnicoEmail->close();
            
            $stmtDatos = $conn->prepare("
                SELECT c.nombre as categoria_nombre, p.nombre as prioridad_nombre, d.nombre as departamento_nombre 
                FROM categorias c, prioridades p, departamentos d 
                WHERE c.id = ? AND p.id = ? AND d.id = ?
            ");
            $stmtDatos->bind_param("iii", $categoria_id, $prioridad_id, $departamento_id);
            $stmtDatos->execute();
            $datosTicket = $stmtDatos->get_result()->fetch_assoc();
            $stmtDatos->close();
            
            include_once "vistas/modulos/email_functions.php";
            $mensajeCliente = crear_mensaje_cliente($ticketId, $titulo, $descripcion, $nombre_completo, $datosTicket);
            $mensajeTecnico = crear_mensaje_tecnico($ticketId, $titulo, $descripcion, $nombre_completo, $email, $telefono, $datosTicket, $tecnicoData);
            
            $mail = new PHPMailer(true);
            configurarPHPMailer($mail);
            
            $asuntoCliente = "Ticket creado exitosamente #$ticketId";
            $resultadoCliente = enviarEmail($email, $nombre_completo, $asuntoCliente, $mensajeCliente, true);
            
            if ($tecnicoData) {
                $tecnicoNombreCompleto = trim($tecnicoData['nombre'] . ' ' . $tecnicoData['apellido']);
                $asuntoTecnico = "Nuevo ticket asignado #$ticketId";
                $resultadoTecnico = enviarEmail($tecnicoData['email'], $tecnicoNombreCompleto, $asuntoTecnico, $mensajeTecnico, true);
            }
            
            if ($resultadoCliente['success']) {
                $mensajeExito = urlencode("¡Ticket creado exitosamente! Su número de ticket es: #$ticketId. Se ha enviado un email de confirmación a: $email");
            } else {
                $mensajeExito = urlencode("¡Ticket creado exitosamente! Su número de ticket es: #$ticketId. Nota: No se pudo enviar el email de confirmación.");
            }
            
            header("Location: index.php?ruta=contactenos&success=1&ticket_id=$ticketId&email=" . urlencode($email) . "&mensaje=" . $mensajeExito);
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $mensajeError = urlencode('Ocurrió un error al procesar su solicitud. Por favor, intente nuevamente.');
            
            error_log("Error al crear ticket: " . $e->getMessage());
            
            header("Location: index.php?ruta=contactenos&error=1&mensaje=" . $mensajeError);
            exit();
        }
    }
    
    $conn->close();
}

 ?>
<!DOCTYPE html>
<html
  lang="en"
  class="layout-navbar-fixed layout-wide"
  dir="ltr"
  data-skin="default"
  data-assets-path="vistas/assets/"
  data-template="front-pages"
  data-bs-theme="light">
 <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Soludesk | Sistema de Mesa de Ayuda</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;base64,PHN2ZyB2ZXJzaW9uPSIxLjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjI0MCIgaGVpZ2h0PSIxNDAiIHZpZXdCb3g9IjAgMCAxMDAgMTAwIiBwcmVzZXJ2ZUFzcGVjdFJhdGlvPSJ4TWlkWU1pZCBtZWV0IiBjbGFzcz0iYXBwLWJyYW5kLWxvZ28tc3ZnIj48ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwLDEwMCkgc2NhbGUoMC4wNSwtMC4wNSkiIGZpbGw9IiM2OTZjZmYiIHN0cm9rZT0ibm9uZSI+PHBhdGggZD0iTTAgMTAwMCBsMCAtMTAwMCAxMDAwIDAgMTAwMCAwIDAgMTAwMCAwIDEwMDAgLTEwMDAgMCAtMTAwMCAwIDAgLTEwMDB6IG0xMjM2IDY5NiBjMTUgLTE1IDI0IC05MiAyNCAtMjEwIGwwIC0xODYgLTcwIDAgLTcwIDAgMCAxNjAgMCAxNjAgLTE1MCAwIC0xNTAgMCAxIC0xNDUgMCAtMTQ1IDIyMCAtMjQ4IDIxOSAtMjQ4IDAgLTIxMyBjMCAtMjcwIDkgLTI2MSAtMjgzIC0yNjEgLTI5MiAwIC0yOTcgNCAtMjk3IDI2NCBsMCAxNzYgNzAgMCA3MCAwIDAgLTE3MCAwIC0xNzAgMTUwIDAgMTUwIDAgMCAxNzEgMCAxNzIgLTE5NyAyMjMgYy0yNDIgMjc1IC0yNDMgMjc3IC0yNDMgNDU4IDAgMjI4IDEwIDIzNiAyOTcgMjM2IDE1NiAwIDI0MyAtOCAyNTkgLTI0eiIvPjwvZz48L3N2Zz4=" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet" />

    <link rel="stylesheet" href="vistas/assets/vendor/fonts/iconify-icons.css" />

    <!-- Core CSS -->
    <!-- build:css assets/vendor/css/theme.css  -->

    <link rel="stylesheet" href="vistas/assets/vendor/libs/pickr/pickr-themes.css" />

    <link rel="stylesheet" href="vistas/assets/vendor/css/core.css" />
    <link rel="stylesheet" href="vistas/assets/css/demo.css" />

    <link rel="stylesheet" href="vistas/assets/vendor/css/pages/front-page.css" />

    <!-- Vendors CSS -->

    <!-- endbuild -->

    
    <!-- Page CSS -->

    <?php
    if (!isset($_GET['ruta']) || $_GET['ruta'] === 'home') {
      echo '<link rel="stylesheet" href="vistas/assets/vendor/libs/nouislider/nouislider.css" />
    <link rel="stylesheet" href="vistas/assets/vendor/libs/swiper/swiper.css" />
<link rel="stylesheet" href="vistas/assets/vendor/css/pages/front-page-landing.css" />';
    } elseif (isset($_GET['ruta']) && ($_GET['ruta'] === 'faq' || $_GET['ruta'] === 'articulo' || $_GET['ruta'] === 'categoria')) {
      echo '<link rel="stylesheet" href="vistas/assets/vendor/css/pages/front-page-help-center.css" />';
    }
    ?>
    
    <!-- Quill content styles -->
    <link rel="stylesheet" href="vistas/css/quill-content.css" />

    <style>
.editor-squiggler,
.squiggly-error,
.squiggly-warning,
.squiggly-info,
.monaco-editor .squiggly-error,
.monaco-editor .squiggly-warning,
.monaco-editor .squiggly-info,
.decoration-item,
.editor-decoration {
  display: none !important;
}

#knowledgeSearch {
 
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

#knowledgeSearch:focus {
 
  box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
  outline: none;
}

#knowledgeSearch::spelling-error,
#knowledgeSearch::grammar-error {
  text-decoration: none;
}
</style>
   
    <!-- Helpers -->
    <script src="vistas/assets/vendor/js/helpers.js"></script>

    <script src="vistas/assets/vendor/js/template-customizer.js"></script>


    <script src="vistas/assets/js/front-config.js"></script>
  </head>

  <body>
  <script src="vistas/assets/vendor/js/dropdown-hover.js"></script>
    <script src="vistas/assets/vendor/js/mega-dropdown.js"></script>


<?php

 include "modulos/menu.php";



 if(isset($_GET["ruta"])){

      if($_GET["ruta"] == "home" ||
        $_GET["ruta"] == "about" ||
        $_GET["ruta"] == "pricing" ||
        $_GET["ruta"] == "gallery" ||
        $_GET["ruta"] == "services" ||
        $_GET["ruta"] == "details" ||
        $_GET["ruta"] == "team" ||
        $_GET["ruta"] == "booking" ||
        $_GET["ruta"] == "faq" ||
        $_GET["ruta"] == "articulo" ||
        $_GET["ruta"] == "categoria" ||
        $_GET["ruta"] == "contactenos" ||
        $_GET["ruta"] == "consultar-ticket" ||
        $_GET["ruta"] == "contact"){

        include "modulos/".$_GET["ruta"].".php";

      }else{

        include "modulos/404.php";

      }

    }else{

      include "modulos/home.php";

    }


 include "modulos/footer.php";

?>

    

     <script src="vistas/assets/vendor/libs/popper/popper.js"></script>
    <script src="vistas/assets/vendor/js/bootstrap.js"></script>
    <script src="vistas/assets/vendor/libs/@algolia/autocomplete-js.js"></script>

    <script src="vistas/assets/vendor/libs/pickr/pickr.js"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->
 
    <!-- Main JS -->

   
    <!-- Page JS -->
    <?php
    if (!isset($_GET['ruta']) || $_GET['ruta'] === 'home') {
    ?>   <script src="vistas/assets/vendor/libs/nouislider/nouislider.js"></script>
    <script src="vistas/assets/vendor/libs/swiper/swiper.js"></script>

      <script src="vistas/assets/js/front-page-landing.js"></script>
    <?php
    }
    ?>
  
 <script src="vistas/assets/js/front-main.js"></script>

</body>
</html>
