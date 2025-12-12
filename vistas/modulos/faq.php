<!-- Help Center Header: Start -->
<section class="section-py first-section-pt help-center-header position-relative overflow-hidden">
  <img class="banner-bg-img z-n1" src="vistas/assets/img/pages/header.png" alt="Help center header" />
  <h4 class="text-center text-primary">Hola, ¿cómo podemos ayudarte?</h4>
  <div class="input-wrapper mb-4 input-group input-group-merge position-relative mx-auto">
    <span class="input-group-text" id="basic-addon1"><i class="icon-base bx bx-search"></i></span>
    <input
      type="text"
      class="form-control"
      placeholder="Buscar en la base de conocimientos"
      aria-label="Search"
      aria-describedby="basic-addon1"
      id="knowledgeSearch"
      autocomplete="off"
      spellcheck="false" />
  </div>
  <p class="text-center px-4 mb-0">Busca por categorías o títulos de artículos para encontrar la información que necesitas de forma rápida y sencilla.</p>
</section>
    <!-- Help Center Header: End -->

   
    <!-- Knowledge Base: Start -->
   <section class="section-py bg-body">
  <div class="container knowledge-base">
    <h4 class="text-center mb-6">Base de Conocimientos</h4>
    <div class="row">
      <div class="col-lg-10 mx-auto">
        <div class="row g-6" id="knowledgeBaseCards">
          <?php
              $conn = new mysqli("localhost", "root", "", "helpdesk");
              if ($conn->connect_error) {
                die("Conexión fallida: " . $conn->connect_error);
              }

              $catQuery = "SELECT c.id, c.nombre, c.descripcion, c.color, c.icono, 
                                     COUNT(b.id) as total_articulos 
                          FROM categorias c 
                          LEFT JOIN blogs b ON c.id = b.categoria_id 
                          GROUP BY c.id, c.nombre, c.descripcion, c.color, c.icono 
                          HAVING total_articulos > 0 
                          ORDER BY c.nombre";
              
              $catResult = $conn->query($catQuery);
              
                if ($catResult->num_rows > 0) {
            while ($categoria = $catResult->fetch_assoc()) {
              $catId = $categoria['id'];
              $iconClass = !empty($categoria['icono']) ? $categoria['icono'] : 'bx bx-file';
              ?>
              <div class="col-xl-4 col-sm-6 knowledge-card" data-category="<?php echo htmlspecialchars(strtolower($categoria['nombre'])); ?>">
                <div class="card">
                  <div class="card-body">
                    <div class="d-flex align-items-center">
                      <div class="avatar avatar-sm flex-shrink-0 me-3">
                        <span class="avatar-initial rounded bg-label-primary">
                          <i class="icon-base <?php echo htmlspecialchars($iconClass); ?>"></i>
                        </span>
                      </div>
                      <h5 class="mb-0 category-name"><?php echo htmlspecialchars($categoria['nombre']); ?></h5>
                    </div>
                    <ul class="list-unstyled my-6">
                      <?php
                          $artQuery = "SELECT id, titulo, fecha_publicacion 
                                      FROM blogs 
                                      WHERE categoria_id = ? 
                                      ORDER BY fecha_publicacion DESC 
                                      LIMIT 6";
                          $stmt = $conn->prepare($artQuery);
                          $stmt->bind_param("i", $catId);
                          $stmt->execute();
                          $artResult = $stmt->get_result();
                          
                        while ($articulo = $artResult->fetch_assoc()) {
                        ?>
                        <li class="mb-2 article-item" data-title="<?php echo htmlspecialchars(strtolower($articulo['titulo'])); ?>">
                          <a href="index.php?ruta=articulo&id=<?php echo $articulo['id']; ?>"
                             class="text-heading d-flex justify-content-between align-items-center">
                            <span class="text-truncate me-2 me-lg-4 article-title">
                              <?php echo htmlspecialchars($articulo['titulo']); ?>
                            </span>
                            <i class="icon-base bx bx-chevron-right scaleX-n1-rtl text-body-secondary"></i>
                          </a>
                        </li>
                        <?php
                      }
                          $stmt->close();
                           ?>
                    </ul>                    <p class="mb-0 fw-medium mt-6">
                      <a href="index.php?ruta=categoria&id=<?php echo $catId; ?>" class="d-flex align-items-center">
                        <span class="me-3">Ver todos los <?php echo $categoria['total_articulos']; ?> artículos</span>
                        <i class="icon-base bx bx-right-arrow-alt scaleX-n1-rtl icon-sm fw-semibold"></i>
                      </a>
                    </p>
                  </div>
                </div>
              </div>
              <?php
            }
          } else {
            echo '<div class="col-12 text-center"><p>No hay artículos disponibles en este momento.</p></div>';
          }
              
              $conn->close();
              ?>
            </div>
          </div>
        </div>
      </div>
    </section>
  
    <!-- Keep Learning: End -->
<section id="landingFAQ" class="section-py bg-body landing-faq">
  <div class="container">
    <div class="text-center mb-4">
      <span class="badge bg-label-primary">FAQ</span>
    </div>
    <h4 class="text-center mb-1">
      Preguntas frecuentes
      <span class="position-relative fw-extrabold z-1">
        <img
          src="vistas/assets/img/front-pages/icons/section-title-icon.png"
          alt="laptop charging"
          class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
      </span>
    </h4>
    <p class="text-center mb-12 pb-md-4">
      Consulta estas preguntas frecuentes para encontrar respuestas rápidas.
    </p>
    <div class="row gy-12 align-items-start">
      <?php
      $conn = new mysqli("localhost", "root", "", "helpdesk");
      if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
      }

      $catResult = $conn->query("SELECT DISTINCT categoria_id FROM faqs");
      $catIndex = 0;

      if ($catResult->num_rows > 0) {
        while ($cat = $catResult->fetch_assoc()) {
          $catIndex++;
          $catId = (int)$cat['categoria_id'];

          $name = "Categoría " . $catId;
          $r2 = $conn->query("SELECT nombre FROM categorias WHERE id = $catId");
          if ($r2 && $r2->num_rows) {
            $n = $r2->fetch_assoc();
            $name = $n['nombre'];
          }
          ?>
          <div class="col-lg-6 mb-6">
            <h5 class="mb-3"><?php echo htmlspecialchars($name); ?></h5>
            <div class="accordion" id="accordionCat<?php echo $catIndex; ?>">
              <?php
              $stmt = $conn->prepare(
                "SELECT id, pregunta, respuesta 
                   FROM faqs 
                  WHERE categoria_id = ?"
              );
              $stmt->bind_param("i", $catId);
              $stmt->execute();
              $faqResult = $stmt->get_result();
              $i = 1;
              while ($row = $faqResult->fetch_assoc()) {
                $show      = $i === 1 ? 'show' : '';
                $collapsed = $i === 1 ? '' : 'collapsed';
                $aria      = $i === 1 ? 'true' : 'false';
                ?>
                <div class="card accordion-item">
                  <h2 class="accordion-header" id="heading<?php echo $catIndex . '_' . $i; ?>">
                    <button
                      class="accordion-button <?php echo $collapsed; ?>"
                      type="button"
                      data-bs-toggle="collapse"
                      data-bs-target="#collapse<?php echo $catIndex . '_' . $i; ?>"
                      aria-expanded="<?php echo $aria; ?>"
                      aria-controls="collapse<?php echo $catIndex . '_' . $i; ?>">
                      <?php echo htmlspecialchars($row['pregunta']); ?>
                    </button>
                  </h2>
                  <div
                    id="collapse<?php echo $catIndex . '_' . $i; ?>"
                    class="accordion-collapse collapse <?php echo $show; ?>"
                    aria-labelledby="heading<?php echo $catIndex . '_' . $i; ?>"
                    data-bs-parent="#accordionCat<?php echo $catIndex; ?>">
                    <div class="accordion-body">
                      <?php echo $row['respuesta']; ?>
                    </div>
                  </div>
                </div>
                <?php
                $i++;
              }
              $stmt->close();
              ?>
            </div>
          </div>
          <?php
        }
      } else {
        echo '<p class="text-center">No hay preguntas frecuentes registradas.</p>';
      }

      $conn->close();
      ?>
    </div>
  </div>
</section>


<script>
document.addEventListener('DOMContentLoaded', function() {
  function cleanDecorations() {
    const decorations = document.querySelectorAll('.editor-squiggler, .squiggly-error, .squiggly-warning, .squiggly-info, .decoration-item');
    decorations.forEach(element => {
      element.remove();
    });
  }

  cleanDecorations();

  const observer = new MutationObserver(cleanDecorations);
  observer.observe(document.body, { childList: true, subtree: true });

  const searchInput = document.getElementById('knowledgeSearch');
  const cards = document.querySelectorAll('.knowledge-card');

  searchInput.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase().trim();

    cards.forEach(function(card) {
      let hasMatch = false;

      if (searchTerm === '') {
        card.style.display = '';
        card.querySelectorAll('.article-item').forEach(function(item) {
          item.style.display = '';
        });
        return;
      }

      const categoryName = card.querySelector('.category-name').textContent.toLowerCase();
      if (categoryName.includes(searchTerm)) {
        hasMatch = true;
        card.querySelectorAll('.article-item').forEach(function(item) {
          item.style.display = '';
        });
      } else {
        const articles = card.querySelectorAll('.article-item');
        articles.forEach(function(article) {
          const articleTitle = article.querySelector('.article-title').textContent.toLowerCase();
          if (articleTitle.includes(searchTerm)) {
            hasMatch = true;
            article.style.display = '';
          } else {
            article.style.display = 'none';
          }
        });
      }

      card.style.display = hasMatch ? '' : 'none';
    });
  });
});
</script>