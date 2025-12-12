<?php
  $rutaActual = $_GET['ruta'] ?? 'home';
  $prefijo   = $rutaActual !== 'home' ? 'index.php?ruta=home' : '';
?>
<nav class="layout-navbar shadow-none py-0">
      <div class="container">
        <div class="navbar navbar-expand-lg landing-navbar px-3 px-md-8">
          <div class="navbar-brand app-brand demo d-flex py-0 me-4 me-xl-8">
            <button
              class="navbar-toggler border-0 px-0 me-4"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#navbarSupportedContent"
              aria-controls="navbarSupportedContent"
              aria-expanded="false"
              aria-label="Toggle navigation">
              <i class="icon-base bx bx-menu icon-lg align-middle text-heading fw-medium"></i>
            </button>
            <a href="home" class="app-brand-link">
              <span class="app-brand-logo demo">
                <span class="text-primary">
                  <svg
                    version="1.0"
                    xmlns="http://www.w3.org/2000/svg"
                    width="240"
                    height="140"
                    viewBox="0 0 100 100"
                    preserveAspectRatio="xMidYMid meet"
                    class="app-brand-logo-svg"
                  >
                    <g
                      transform="translate(0,100) scale(0.05,-0.05)"
                      fill="currentColor"
                      stroke="none"
                    >
                      <path
                        d="M0 1000 l0 -1000 1000 0 1000 0 0 1000 0 1000 -1000 0 -1000 0 0 -1000z m1236 696 c15 -15 24 -92 24 -210 l0 -186 -70 0 -70 0 0 160 0 160 -150 0 -150 0 1 -145 0 -145 220 -248 219 -248 0 -213 c0 -270 9 -261 -283 -261 -292 0 -297 4 -297 264 l0 176 70 0 70 0 0 -170 0 -170 150 0 150 0 0 171 0 172 -197 223 c-242 275 -243 277 -243 458 0 228 10 236 297 236 156 0 243 -8 259 -24z"
                      />
                    </g>
                  </svg>
                </span>
              </span>
              &nbsp;<span class="app-brand-text demo text-heading fw-bold">Soludesk</span>
            </a>
          </div>
      
          <div class="collapse navbar-collapse landing-nav-menu" id="navbarSupportedContent">
            <button
              class="navbar-toggler border-0 text-heading position-absolute end-0 top-0 scaleX-n1-rtl p-2"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#navbarSupportedContent"
              aria-controls="navbarSupportedContent"
              aria-expanded="false"
              aria-label="Toggle navigation">
              <i class="icon-base bx bx-x icon-lg"></i>
            </button>
            <ul class="navbar-nav me-auto">
              <li class="nav-item">
                <a class="nav-link fw-medium" aria-current="page" href="<?= $prefijo ?>#landingHero">Inicio</a>
              </li>
              <li class="nav-item">
                <a class="nav-link fw-medium" href="<?= $prefijo ?>#landingFeatures">Características</a>
              </li>
              <li class="nav-item">
                <a class="nav-link fw-medium" href="<?= $prefijo ?>#landingTeam">Equipo</a>
              </li>
              <li class="nav-item">
                <a class="nav-link fw-medium" href="<?= $prefijo ?>#landingFAQ">FAQ</a>
              </li>              <li class="nav-item">
                <a class="nav-link fw-medium" href="index.php?ruta=contactenos">Contáctanos</a>
              </li>
               <li class="nav-item">
                <a class="nav-link fw-medium" href="index.php?ruta=faq">Help Center</a>
              </li>
            </ul>
          </div>
          <div class="landing-menu-overlay d-lg-none"></div>
        
          <ul class="navbar-nav flex-row align-items-center ms-auto">
            <li class="nav-item me-2 me-xl-0">
              <a
                class="nav-link"
                href="manual/Manual Cliente.exe"
                download="Manual Cliente.exe"
                data-bs-toggle="tooltip"
                data-bs-placement="bottom"
                title="Descargar Manual">
                <i class="icon-base bx bx-book icon-lg"></i>
                
              </a>
            </li>  
            <li class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
              <a
                class="nav-link dropdown-toggle hide-arrow"
                id="nav-theme"
                href="javascript:void(0);"
                data-bs-toggle="dropdown">
                <i class="icon-base bx bx-sun icon-lg theme-icon-active"></i>
                <span class="d-none ms-2" id="nav-theme-text">Toggle theme</span>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
                <li>
                  <button
                    type="button"
                    class="dropdown-item align-items-center active"
                    data-bs-theme-value="light"
                    aria-pressed="false">
                    <span><i class="icon-base bx bx-sun icon-md me-3" data-icon="sun"></i>Light</span>
                  </button>
                </li>
                <li>
                  <button
                    type="button"
                    class="dropdown-item align-items-center"
                    data-bs-theme-value="dark"
                    aria-pressed="true">
                    <span><i class="icon-base bx bx-moon icon-md me-3" data-icon="moon"></i>Dark</span>
                  </button>
                </li>
              </ul>
            </li>
          
            <li>
              <a href="../../helpdesk" class="btn btn-primary" target="_blank"
                ><span class="tf-icons icon-base bx bx-log-in-circle scaleX-n1-rtl me-md-1"></span
                ><span class="d-none d-md-block">Login</span></a
              >
            </li>
           
          </ul>
        
        </div>
      </div>
    </nav>