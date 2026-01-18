<?php

$articuloId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($articuloId <= 0) {
  header("Location: index.php?ruta=faq");
  exit();
}

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';
$dbName = getenv('DB_NAME') ?: 'helpdesk';
$dbPort = getenv('DB_PORT') ? intval(getenv('DB_PORT')) : 3306;

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
if ($conn->connect_error) {
  die("Conexión fallida: " . $conn->connect_error);
}


$stmt = $conn->prepare("SELECT b.*, c.nombre as categoria_nombre 
                       FROM blogs b 
                       INNER JOIN categorias c ON b.categoria_id = c.id 
                       WHERE b.id = ?");
$stmt->bind_param("i", $articuloId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  header("Location: index.php?ruta=faq");
  exit();
}

$articulo = $result->fetch_assoc();
$stmt->close();

$stmtRelated = $conn->prepare("SELECT id, titulo 
                              FROM blogs 
                              WHERE categoria_id = ? AND id != ? 
                              ORDER BY fecha_publicacion DESC 
                              LIMIT 5");
$stmtRelated->bind_param("ii", $articulo['categoria_id'], $articuloId);
$stmtRelated->execute();
$relacionados = $stmtRelated->get_result();
$stmtRelated->close();

$conn->close();

$fechaPublicacion = new DateTime($articulo['fecha_publicacion']);

$fechaHoy = new DateTime();
$esNuevo = $fechaPublicacion->format('Y-m-d') === $fechaHoy->format('Y-m-d');

$fechaActualizada = null;
$mostrarActualizado = false;
if ($articulo['fecha_actualizada'] && 
    $articulo['fecha_actualizada'] !== '0000-00-00 00:00:00' && 
    $articulo['fecha_actualizada'] !== null) {
    $fechaActualizada = new DateTime($articulo['fecha_actualizada']);
    $mostrarActualizado = true;
}
?>

<section class="section-py bg-body first-section-pt">
  
  <div class="container">
    <div class="row g-6">
      <div class="col-lg-8">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb breadcrumb-style1 mb-2">
            <li class="breadcrumb-item">
              <a href="index.php?ruta=faq">Centro de Ayuda</a>
            </li>
            <li class="breadcrumb-item">
              <a href="index.php?ruta=categoria&id=<?php echo $articulo['categoria_id']; ?>">
                <?php echo htmlspecialchars($articulo['categoria_nombre']); ?>
              </a>
            </li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($articulo['titulo']); ?></li>
          </ol>
        </nav>
        
        <h4 class="mb-2">
          <?php echo htmlspecialchars($articulo['titulo']); ?>
          <?php if ($esNuevo): ?>
            <span class="badge bg-label-success ms-2">Nuevo</span>
          <?php endif; ?>
        </h4>
        <p>
          <?php echo $fechaPublicacion->format('d/m/Y'); ?> 
          <?php if ($mostrarActualizado): ?>
            - Actualizado el <?php echo $fechaActualizada->format('d/m/Y'); ?>
          <?php endif; ?>
        </p>
        
        <hr class="my-6" />
        
        <div class="article-content">
          <?php echo $articulo['contenido']; ?>
        </div>
        
        <div class="mt-6">
          <a href="index.php?ruta=faq" class="btn btn-label-primary">
            <i class="icon-base bx bx-arrow-back me-2"></i>
            Volver al Centro de Ayuda
          </a>
        </div>
      </div>
      
      <div class="col-lg-4">
        <div class="input-group input-group-merge mb-6 mt-6 mt-lg-0">
          <span class="input-group-text" id="article-search">
            <i class="icon-base bx bx-search"></i>
          </span>
          <input
            type="text"
            class="form-control"
            placeholder="Buscar..."
            aria-label="Search..."
            aria-describedby="article-search" />
        </div>
        
        <div class="bg-lighter py-2 px-4 rounded">
          <h5 class="mb-0">Artículos relacionados</h5>
        </div>
        
        <ul class="list-unstyled mt-4 mb-0">
          <?php if ($relacionados->num_rows > 0): ?>
            <?php while ($relacionado = $relacionados->fetch_assoc()): ?>
              <li class="mb-4">
                <a href="index.php?ruta=articulo&id=<?php echo $relacionado['id']; ?>" 
                   class="text-heading d-flex justify-content-between">
                  <span class="text-truncate me-2"><?php echo htmlspecialchars($relacionado['titulo']); ?></span>
                  <i class="icon-base bx bx-chevron-right scaleX-n1-rtl text-body-secondary"></i>
                </a>
              </li>
            <?php endwhile; ?>
          <?php else: ?>
            <li class="mb-4">
              <span class="text-muted">No hay artículos relacionados disponibles.</span>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>
  <br>
  <br>
  <br>
</section>

<style>
.article-content {
  line-height: 1.7;
}

.article-content h1,
.article-content h2,
.article-content h3,
.article-content h4,
.article-content h5,
.article-content h6 {
  margin-top: 1.5rem;
  margin-bottom: 1rem;
  font-weight: 600;
}

.article-content p {
  margin-bottom: 1rem;
}

.article-content ul,
.article-content ol {
  margin-bottom: 1rem;
  padding-left: 1.5rem;
}

.article-content li {
  margin-bottom: 0.5rem;
}

.article-content blockquote {
  border-left: 4px solid #dee2e6;
  padding-left: 1rem;
  margin: 1.5rem 0;
  font-style: italic;
  color: #6c757d;
}

.article-content strong {
  font-weight: 600;
}

.article-content em {
  font-style: italic;
}

.article-content u {
  text-decoration: underline;
}

.article-content a {
  color: #696cff;
  text-decoration: none;
}

.article-content a:hover {
  text-decoration: underline;
}

.article-content img {
  max-width: 100%;
  height: auto;
  margin: 1rem 0;
  border-radius: 0.375rem;
}

.article-content pre {
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
  padding: 1rem;
  overflow-x: auto;
  margin: 1rem 0;
}

.article-content code {
  background-color: #f8f9fa;
  padding: 0.125rem 0.25rem;
  border-radius: 0.25rem;
  font-size: 0.875em;
}

.article-content hr {
  margin: 2rem 0;
  border: 0;
  border-top: 1px solid #dee2e6;
}
</style>