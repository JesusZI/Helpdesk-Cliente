<?php
$categoriaId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($categoriaId <= 0) {
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

$stmtCat = $conn->prepare("SELECT id, nombre, descripcion, color, icono FROM categorias WHERE id = ?");
$stmtCat->bind_param("i", $categoriaId);
$stmtCat->execute();
$resultCat = $stmtCat->get_result();

if ($resultCat->num_rows === 0) {
  header("Location: index.php?ruta=faq");
  exit();
}

$categoria = $resultCat->fetch_assoc();
$stmtCat->close();

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$articlesPerPage = 12;
$offset = ($page - 1) * $articlesPerPage;

$countStmt = $conn->prepare("SELECT COUNT(*) as total FROM blogs WHERE categoria_id = ?");
$countStmt->bind_param("i", $categoriaId);
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalArticles = $countResult->fetch_assoc()['total'];
$countStmt->close();

$totalPages = ceil($totalArticles / $articlesPerPage);

$stmt = $conn->prepare("SELECT id, titulo, contenido, fecha_publicacion, fecha_actualizada 
                       FROM blogs 
                       WHERE categoria_id = ? 
                       ORDER BY fecha_publicacion DESC 
                       LIMIT ? OFFSET ?");
$stmt->bind_param("iii", $categoriaId, $articlesPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$otherCategoriesStmt = $conn->prepare("SELECT c.id, c.nombre, c.icono, COUNT(b.id) as total_articulos 
                                     FROM categorias c 
                                     LEFT JOIN blogs b ON c.id = b.categoria_id 
                                     WHERE c.id != ? 
                                     GROUP BY c.id, c.nombre, c.icono 
                                     HAVING total_articulos > 0 
                                     ORDER BY c.nombre 
                                     LIMIT 8");
$otherCategoriesStmt->bind_param("i", $categoriaId);
$otherCategoriesStmt->execute();
$otherCategories = $otherCategoriesStmt->get_result();
$otherCategoriesStmt->close();

$conn->close();

$iconClass = !empty($categoria['icono']) ? $categoria['icono'] : 'bx bx-file';
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
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($categoria['nombre']); ?></li>
          </ol>
        </nav>
        
        <div class="d-flex align-items-center mb-4">
          <div class="avatar avatar-md flex-shrink-0 me-3">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="icon-base <?php echo htmlspecialchars($iconClass); ?> fs-4"></i>
            </span>
          </div>
          <div>
            <h4 class="mb-1"><?php echo htmlspecialchars($categoria['nombre']); ?></h4>
            <p class="mb-0 text-body-secondary"><?php echo $totalArticles; ?> artículo<?php echo $totalArticles !== 1 ? 's' : ''; ?> disponible<?php echo $totalArticles !== 1 ? 's' : ''; ?></p>
          </div>
        </div>

        <?php if (!empty($categoria['descripcion'])): ?>
        <div class="alert alert-primary mb-6" role="alert">
          <div class="alert-body">
            <?php echo htmlspecialchars($categoria['descripcion']); ?>
          </div>
        </div>
        <?php endif; ?>
        
        <hr class="my-6" />
        
        <?php if ($result->num_rows > 0): ?>
        <div class="row g-6">
          <?php while ($articulo = $result->fetch_assoc()): 
            $fechaPublicacion = new DateTime($articulo['fecha_publicacion']);
            $fechaActualizada = null;
            $mostrarActualizado = false;
            
            if ($articulo['fecha_actualizada'] && 
                $articulo['fecha_actualizada'] !== '0000-00-00 00:00:00' && 
                $articulo['fecha_actualizada'] !== null) {
                $fechaActualizada = new DateTime($articulo['fecha_actualizada']);
                $mostrarActualizado = true;
            }
            
            $fechaHoy = new DateTime();
            $esNuevo = $fechaPublicacion->format('Y-m-d') === $fechaHoy->format('Y-m-d');
            
            $contenidoLimpio = strip_tags($articulo['contenido']);
            $resumen = strlen($contenidoLimpio) > 150 ? 
                      substr($contenidoLimpio, 0, 150) . '...' : 
                      $contenidoLimpio;
          ?>
          <div class="col-md-6">
            <div class="card h-100">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title mb-3">
                  <a href="index.php?ruta=articulo&id=<?php echo $articulo['id']; ?>" 
                     class="text-heading text-decoration-none">
                    <?php echo htmlspecialchars($articulo['titulo']); ?>
                  </a>
                  <?php if ($esNuevo): ?>
                    <span class="badge bg-label-success ms-2">Nuevo</span>
                  <?php endif; ?>
                </h5>
                <p class="card-text text-body-secondary mb-3 flex-grow-1">
                  <?php echo htmlspecialchars($resumen); ?>
                </p>
                <div class="d-flex justify-content-between align-items-center mt-auto">
                  <small class="text-muted">
                    <?php echo $fechaPublicacion->format('d/m/Y'); ?>
                    <?php if ($mostrarActualizado): ?>
                      <br><span class="badge bg-label-info">Actualizado <?php echo $fechaActualizada->format('d/m/Y'); ?></span>
                    <?php endif; ?>
                  </small>
                  <a href="index.php?ruta=articulo&id=<?php echo $articulo['id']; ?>" 
                     class="btn btn-outline-primary btn-sm">
                    Leer más
                    <i class="icon-base bx bx-right-arrow-alt ms-1"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
          <?php endwhile; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-6">
          <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
            <li class="page-item">
              <a class="page-link" href="index.php?ruta=categoria&id=<?php echo $categoriaId; ?>&page=<?php echo ($page - 1); ?>">
                <i class="icon-base bx bx-chevron-left"></i>
              </a>
            </li>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
              <a class="page-link" href="index.php?ruta=categoria&id=<?php echo $categoriaId; ?>&page=<?php echo $i; ?>">
                <?php echo $i; ?>
              </a>
            </li>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
            <li class="page-item">
              <a class="page-link" href="index.php?ruta=categoria&id=<?php echo $categoriaId; ?>&page=<?php echo ($page + 1); ?>">
                <i class="icon-base bx bx-chevron-right"></i>
              </a>
            </li>
            <?php endif; ?>
          </ul>
        </nav>
        <?php endif; ?>

        <?php else: ?>
        <div class="text-center py-6">
          <img src="vistas/assets/img/illustrations/misc-under-maintenance.png" 
               alt="No articles" class="mb-4" width="300">
          <h5 class="mb-2">No hay artículos disponibles</h5>
          <p class="text-body-secondary mb-4">
            Aún no se han publicado artículos en esta categoría.
          </p>
          <a href="index.php?ruta=faq" class="btn btn-primary">
            <i class="icon-base bx bx-arrow-back me-2"></i>
            Volver al Centro de Ayuda
          </a>
        </div>
        <?php endif; ?>

        <div class="mt-6">
          <a href="index.php?ruta=faq" class="btn btn-label-primary">
            <i class="icon-base bx bx-arrow-back me-2"></i>
            Volver al Centro de Ayuda
          </a>
        </div>
      </div>
      
      <div class="col-lg-4">
        <div class="input-group input-group-merge mb-6 mt-6 mt-lg-0">
          <span class="input-group-text" id="category-search">
            <i class="icon-base bx bx-search"></i>
          </span>
          <input
            type="text"
            class="form-control"
            placeholder="Buscar en esta categoría..."
            aria-label="Search..."
            aria-describedby="category-search"
            id="categorySearch" />
        </div>

        <div class="card mb-6">
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
              <div class="avatar avatar-sm flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="icon-base <?php echo htmlspecialchars($iconClass); ?>"></i>
                </span>
              </div>
              <h6 class="mb-0">Acerca de esta categoría</h6>
            </div>
            <p class="text-body-secondary mb-3">
              <?php echo !empty($categoria['descripcion']) ? 
                        htmlspecialchars($categoria['descripcion']) : 
                        'Artículos relacionados con ' . htmlspecialchars($categoria['nombre']); ?>
            </p>
            <div class="d-flex justify-content-between text-body-secondary small">
              <span>Total de artículos:</span>
              <span class="fw-medium"><?php echo $totalArticles; ?></span>
            </div>
          </div>
        </div>
        
        <?php if ($otherCategories->num_rows > 0): ?>
        <div class="bg-lighter py-2 px-4 rounded">
          <h5 class="mb-0">Otras categorías</h5>
        </div>
        
        <ul class="list-unstyled mt-4 mb-0">
          <?php while ($otherCategory = $otherCategories->fetch_assoc()): 
            $otherIconClass = !empty($otherCategory['icono']) ? $otherCategory['icono'] : 'bx bx-file';
          ?>
          <li class="mb-4">
            <a href="index.php?ruta=categoria&id=<?php echo $otherCategory['id']; ?>" 
               class="text-heading d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <div class="avatar avatar-xs flex-shrink-0 me-2">
                  <span class="avatar-initial rounded bg-label-secondary">
                    <i class="icon-base <?php echo htmlspecialchars($otherIconClass); ?> icon-xs"></i>
                  </span>
                </div>
                <span class="text-truncate me-2">
                  <?php echo htmlspecialchars($otherCategory['nombre']); ?>
                </span>
              </div>
              <div class="d-flex align-items-center">
                <span class="badge bg-label-primary me-2"><?php echo $otherCategory['total_articulos']; ?></span>
                <i class="icon-base bx bx-chevron-right scaleX-n1-rtl text-body-secondary"></i>
              </div>
            </a>
          </li>
          <?php endwhile; ?>
        </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('categorySearch');
  const articleCards = document.querySelectorAll('.col-md-6');

  searchInput.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase().trim();

    articleCards.forEach(function(card) {
      const title = card.querySelector('.card-title a').textContent.toLowerCase();
      const content = card.querySelector('.card-text').textContent.toLowerCase();
      
      if (searchTerm === '' || title.includes(searchTerm) || content.includes(searchTerm)) {
        card.style.display = '';
      } else {
        card.style.display = 'none';
      }
    });

    const visibleCards = Array.from(articleCards).filter(card => card.style.display !== 'none');
    let noResultsMsg = document.getElementById('noResultsMessage');
    
    if (visibleCards.length === 0 && searchTerm !== '') {
      if (!noResultsMsg) {
        noResultsMsg = document.createElement('div');
        noResultsMsg.id = 'noResultsMessage';
        noResultsMsg.className = 'col-12 text-center py-6';
        noResultsMsg.innerHTML = `
          <div class="text-body-secondary">
            <i class="icon-base bx bx-search fs-1 mb-3"></i>
            <h6>No se encontraron resultados</h6>
            <p>Intenta con diferentes palabras clave</p>
          </div>
        `;
        document.querySelector('.row.g-6').appendChild(noResultsMsg);
      }
      noResultsMsg.style.display = '';
    } else if (noResultsMsg) {
      noResultsMsg.style.display = 'none';
    }
  });
});
</script>