  <header class="sticky-top header-nav">
        <div class="navtop">
          <div class="container-xxl container-fluid px-lg-4 px-xxl-0">
            <div
              class="row g-lg-3 justify-content-center align-items-center g-2"
            >
              <div class="col-lg-6">
                <a
                  class="navbar-brand d-flex align-items-center gap-2"
                  href="index.html">
                  <img src="images/logos/armoirie.png" alt="logo de Bethel" />
                  <div class="bande">
                    <span></span>
                    <span></span>
                    <span></span>
                  </div>
                  <span>
                    Ministère Délégué Près le Ministre de la Défense Nationale
                    et Anciens Combattants en charge des Anciens
                    Combattants
                </span>
                </a>
              </div>
              <div class="col-lg-6">
                <div
                  class="d-flex align-items-center justify-content-end gap-3"
                >
                  <div class="search-bar d-flex align-items-center gap-2">
                    <input
                      type="text"
                      placeholder="Rechercher"
                      class="form-control"
                    />
                    <div class="icon">
                      <i class="bi bi-search"></i>
                    </div>
                  </div>
                  <div class="block-network d-flex align-items-center gap-2">
                    <a href="#" class="facebook">
                      <i class="bi bi-facebook"></i>
                    </a>
                    <a href="#" class="twitter">
                      <i class="bi bi-twitter-x"></i>
                    </a>
                    <a href="#" class="youtube">
                      <i class="bi bi-youtube"></i>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <nav class="navbar navbar-expand-lg">
          <div class="container-xxl container-fluid px-lg-4 px-xxl-0">
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
              <ul class="navbar-nav me-auto mb-0 mb-lg-0 gap-lg-4">
                <li class="nav-item">
                  <a class="nav-link {{ Route::current()->getName() == 'home' ? 'active' : '' }}" aria-current="page"
                     href="{{ route('home') }}"
                    >Acccueil</a
                  >
                </li>

                <li class="nav-item">
                  <a class="nav-link {{ Route::current()->getName() == 'about' ? 'active' : '' }}"
                    href="{{ route( 'about') }}">A propos</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ Route::current()->getName() == 'ministre' ? 'active' : '' }}"
                     href="{{ route('ministre') }}">Le ministre</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ Route::current()->getName() == 'gouvernance' ? 'active' : '' }}"
                     href="{{ route('gouvernance') }}">Gouvernance</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ Route::current()->getName() == 'actualites' ? 'active' : '' }}"
                  href="{{ route('actualites') }}">Actualités</a>
                </li>
              </ul>
              <a href="{{ route('contact') }}" class="btn btn-default btn-red ms-lg-4 ms-xl-5">
                Nous-contactez
              </a>
            </div>
          </div>
        </nav>
      </header>
