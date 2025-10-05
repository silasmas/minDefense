@extends('site.template')


@section("content")

      <div class="banner">
        <div
          id="carouselExampleAutoplaying"
          class="carousel slide"
          data-bs-ride="carousel"
        >
          <div class="carousel-indicators">
            <button
              type="button"
              data-bs-target="#carouselExampleAutoplaying"
              data-bs-slide-to="0"
              class="active"
              aria-current="true"
              aria-label="Slide 1"
            ></button>
            <button
              type="button"
              data-bs-target="#carouselExampleAutoplaying"
              data-bs-slide-to="1"
              aria-label="Slide 2"
            ></button>
            <button
              type="button"
              data-bs-target="#carouselExampleAutoplaying"
              data-bs-slide-to="2"
              aria-label="Slide 3"
            ></button>
          </div>
          <div class="carousel-inner">
            <div class="carousel-item active">
              <div class="content-banner">
                <div class="img-banner">
                  <img
                    src="images/info.webp"
                    alt="Bannière"
                    class="w-100 h-100 object-fit-cover"
                  />
                </div>
                <div class="content-text w-100">
                  <div class="container">
                    <div class="row">
                      <div class="col-lg-10 col-xl-8 col-xxl-7">
                        <div class="text-start card-actus">
                          <h1 class="mb-3">Actus</h1>
                          <h2 class="mb-lg-3">
                            S.E. Eliezer Ntambwe déterminé à redonner de la
                            valeur au patrimoine des anciens combattants
                          </h2>
                          <a href="#" class="btn btn-default btn-red"
                            >Savoir plus</a
                          >
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="carousel-item">
              <div class="content-banner">
                <div class="img-banner">
                  <img
                    src="images/info1.webp"
                    alt="Bannière"
                    class="w-100 h-100 object-fit-cover"
                  />
                </div>
                <div class="content-text w-100">
                  <div class="container">
                    <div class="row">
                      <div class="col-lg-10 col-xl-8 col-xxl-7">
                        <div class="text-start card-actus">
                          <h1 class="mb-3">Actus</h1>
                          <h2 class="mb-lg-3">
                            Honorable Eliezer NTAMBWE : La voix du peuple, l’âme
                            de la République, le vœu de la grande partie de la
                            population
                          </h2>
                          <a href="#" class="btn btn-default btn-red"
                            >Savoir plus</a
                          >
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="carousel-item">
              <div class="content-banner">
                <div class="img-banner">
                  <img
                    src="images/banners/banner4.webp"
                    alt="Bannière"
                    class="w-100 h-100 object-fit-cover"
                  />
                </div>
                <div class="content-text w-100">
                  <div class="container">
                    <div class="row">
                      <div class="col-lg-10 col-xl-8 col-xxl-7">
                        <div class="text-start card-actus">
                          <h1 class="mb-3">Actus</h1>
                          <h2 class="mb-lg-3">
                            Pourquoi la RDC veut-elle le départ des troupes
                            est-africaines ?
                          </h2>
                          <a href="#" class="btn btn-default btn-red"
                            >Savoir plus</a
                          >
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="block-actus">
        <div class="container">
          <div class="row">
            <div class="col-lg-12">
              <div
                class="d-flex align-items-center justify-content-between mb-lg-4 mb-3"
              >
                <h2 class="mt-0">Actualités</h2>
                <a href="actualites.html" class="link-red d-inline-flex gap-2">
                  Voir toutes les actualités
                  <i class="bi bi-arrow-right"></i>
                </a>
              </div>
              <div class="row g-lg-2 g-xxl-3">
                <div class="col-lg-3">
                  <div class="card card-actus h-100">
                    <div class="card-img">
                      <div
                        class="d-flex date align-items-center justify-content-center gap-1 flex"
                      >
                        <i class="bi bi-calendar"></i>
                        26 sept 2025
                      </div>
                      <img
                        src="images/banners/banner4.webp"
                        alt="Actualité"
                        class="w-100 h-100 object-fit-cover"
                      />
                    </div>
                    <div class="content-actus p-2">
                      <h3>
                        Pourquoi la RDC veut-elle le départ des troupes
                        est-africaines ?
                      </h3>
                      <a href="#" class="link-red"> Lire la suite </a>
                    </div>
                  </div>
                </div>
                <div class="col-lg-3">
                  <div class="card card-actus h-100">
                    <div class="card-img">
                      <div
                        class="d-flex date align-items-center justify-content-center gap-1 flex"
                      >
                        <i class="bi bi-calendar"></i>
                        18 sept 2025
                      </div>
                      <img
                        src="images/info4.webp"
                        alt="Actualité"
                        class="w-100 h-100 object-fit-cover"
                      />
                    </div>
                    <div class="content-actus p-2">
                      <h3>
                        RDC : Eliezer Ntambwe Mposhi impulse une nouvelle ère
                        pour les anciens combattants avec l’opération
                        d’identification, de sécurisation et de valorisation de
                        leurs biens
                      </h3>
                      <a href="#" class="link-red"> Lire la suite </a>
                    </div>
                  </div>
                </div>
                <div class="col-lg-3">
                  <div class="card card-actus h-100">
                    <div class="card-img">
                      <div
                        class="d-flex date align-items-center justify-content-center gap-1 flex"
                      >
                        <i class="bi bi-calendar"></i>
                        18 sept 2025
                      </div>
                      <img
                        src="images/info5.webp"
                        alt="Actualité"
                        class="w-100 h-100 object-fit-cover"
                      />
                    </div>
                    <div class="content-actus p-2">
                      <h3>
                        Dernier hommage au Général Mbuluku : Le Ministre Délégué
                        Eliezer Ntambwe Mposhi s’illustre par sa présence et son
                        engagement pour la mémoire des militaires
                      </h3>
                      <a href="#" class="link-red"> Lire la suite </a>
                    </div>
                  </div>
                </div>

                <div class="col-lg-3">
                  <div class="card card-actus h-100">
                    <div class="card-img">
                      <div
                        class="d-flex date align-items-center justify-content-center gap-1 flex"
                      >
                        <i class="bi bi-calendar"></i>
                        12 sept 2025
                      </div>
                      <img
                        src="images/info3.webp"
                        alt="Actualité"
                        class="w-100 h-100 object-fit-cover"
                      />
                    </div>
                    <div class="content-actus p-2">
                      <h3>
                        Eliezer NTAMBWE , Ministre délégué à la défense
                        nationale, ressuscite un héros ancien combattant de
                        40-45 à Uvira(Sud Kivu)
                      </h3>
                      <a href="#" class="link-red"> Lire la suite </a>
                    </div>
                  </div>
                </div>
                <div class="col-lg-3">
                  <div class="card card-actus h-100">
                    <div class="card-img">
                      <div
                        class="d-flex date align-items-center justify-content-center gap-1 flex"
                      >
                        <i class="bi bi-calendar"></i>
                        26 août 2025
                      </div>
                      <img
                        src="images/info.webp"
                        alt="Actualité"
                        class="w-100 h-100 object-fit-cover"
                      />
                    </div>
                    <div class="content-actus p-2">
                      <h3>
                        S.E. Eliezer Ntambwe déterminé à redonner de la valeur
                        au patrimoine des anciens combattants
                      </h3>
                      <a href="#" class="link-red"> Lire la suite </a>
                    </div>
                  </div>
                </div>
                <div class="col-lg-3">
                  <div class="card card-actus h-100">
                    <div class="card-img">
                      <div
                        class="d-flex date align-items-center justify-content-center gap-1 flex"
                      >
                        <i class="bi bi-calendar"></i>
                        2 août 2025
                      </div>
                      <img
                        src="images/info1.webp"
                        alt="Actualité"
                        class="w-100 h-100 object-fit-cover"
                      />
                    </div>
                    <div class="content-actus p-2">
                      <h3>
                        Honorable Eliezer NTAMBWE : La voix du peuple, l’âme de
                        la République, le vœu de la grande partie de la
                        population
                      </h3>
                      <a href="#" class="link-red"> Lire la suite </a>
                    </div>
                  </div>
                </div>
                <div class="col-lg-3">
                  <div class="card card-actus h-100">
                    <div class="card-img">
                      <div
                        class="d-flex date align-items-center justify-content-center gap-1 flex"
                      >
                        <i class="bi bi-calendar"></i>
                        02 août 2025
                      </div>
                      <img
                        src="images/info7.webp"
                        alt="Actualité"
                        class="w-100 h-100 object-fit-cover"
                      />
                    </div>
                    <div class="content-actus p-2">
                      <h3>
                        Honorable Eliezer NTAMBWE : La voix du peuple, l’âme de
                        la République, le vœu de la grande partie de la
                        population
                      </h3>
                      <a href="#" class="link-red"> Lire la suite </a>
                    </div>
                  </div>
                </div>
                <div class="col-lg-3">
                  <div class="card card-actus h-100">
                    <div class="card-img">
                      <div
                        class="d-flex date align-items-center justify-content-center gap-1 flex"
                      >
                        <i class="bi bi-calendar"></i>
                        10 juil 2025
                      </div>
                      <img
                        src="images/info8.webp"
                        alt="Actualité"
                        class="w-100 h-100 object-fit-cover"
                      />
                    </div>
                    <div class="content-actus p-2">
                      <h3>
                        Eliezer NTAMBWE exhibe un pas de danse quand Félix Tshisekedi inaugure les nouvelles infrastructures de l’UPN à Kinshasa
                      </h3>
                      <a href="#" class="link-red"> Lire la suite </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="block-mission">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-8 text-center text-white">
              <h2>Notre mission</h2>
              <p class="mb-4">
                Lorem, ipsum dolor sit amet consectetur adipisicing elit.
                Doloremque enim, culpa architecto, veniam quaerat dolore rem
                quam voluptatibus cum corrupti neque explicabo nam accusantium,
                molestiae eaque. Ea ex, beatae ad inventore voluptas cum
                deserunt, quaerat nulla a placeat voluptatibus nobis!
              </p>
              <a href="#" class="btn btn-default btn-white"> En savoir plus </a>
            </div>
          </div>
        </div>
      </div>
      <div class="mot-president">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-10">
              <div class="card">
                <div class="quote">
                  <i class="bi bi-quote"></i>
                </div>
                <div class="content-card d-flex align-items-center">
                  <div class="img flex-shrink-0">
                    <img src="images/president.jpg" alt="" />
                  </div>
                  <div
                    class="h-100 flex flex-column align-items-center justify-content-center content-message"
                  >
                    <h4>Mot du Président</h4>
                    <cite>
                      <p class="mb-3">
                        " Chers soldats, Vous êtes le bouclier de notre Nation
                        et la fierté de notre peuple. Par votre courage et votre
                        discipline, vous défendez notre souveraineté et
                        garantissez la paix. En tant que Président, je vous
                        assure de mon soutien et de celui de toute la Nation.
                        Ensemble, bâtissons une armée forte, moderne et
                        respectée. "
                      </p>
                    </cite>
                    <h5>Félix Antoine Tshisekedi</h5>
                    <span>Président de la République</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="block-organisme">
        <div class="container">
          <div class="text-center">
            <h2 class="mb-5">Nos organismes et nos armées</h2>
          </div>
          <div class="row g-lg-2 g-xxl-4">
            <div class="col-lg-3">
              <div class="card">
                <div class="img">
                  <img src="images/forceT.jpg" alt="force" class="w-100" />
                </div>
                <div class="content-organisme p-2">
                  <h3 class="mb-1">Forces terrestres</h3>
                  <p>
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit.
                    Nesciunt quis nobis repellendus!
                  </p>
                </div>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="card">
                <div class="img">
                  <img src="images/forceN.jpg" alt="force" class="w-100" />
                </div>
                <div class="content-organisme p-2">
                  <h3 class="mb-1">Forces navales</h3>
                  <p>
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit.
                    Nesciunt quis nobis repellendus!
                  </p>
                </div>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="card">
                <div class="img">
                  <img src="images/forceA.jpg" alt="force" class="w-100" />
                </div>
                <div class="p-2">
                  <h3 class="mb-1">Force aérienne</h3>
                  <p>
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit.
                    Nesciunt quis nobis repellendus!
                  </p>
                </div>
              </div>
            </div>

            <div class="col-lg-3">
              <div class="card">
                <div class="img">
                  <img src="images/p.jpg" alt="force" class="w-100" />
                </div>
                <div class="p-2">
                  <h3 class="mb-1">Police Nationale Congolaise</h3>
                  <p>
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit.
                    Nesciunt quis nobis repellendus!
                  </p>
                </div>
              </div>
            </div>
            <!-- <div class="col-lg-3">
              <div class="card">
                <div class="img">
                  <img src="images/p.jpg" alt="force" class="w-100" />
                </div>
                <div class="p-2">
                  <h3 class="mb-1">Services de Renseignement et de Sécurité</h3>
                <p>
                  Lorem ipsum dolor sit amet, consectetur adipisicing elit.
                  Nesciunt quis nobis repellendus!
                </p>
                </div>
              </div>
            </div> -->
          </div>
        </div>
      </div>
      <div class="block-info-ministre">
        <div class="container">
          <div class="row g-lg-5 align-items-center">
            <div class="col-lg-6">
              <div class="card card-ministre">
                <div class="card-img">
                  <div class="badge-ministre">Le Ministre</div>
                  <img
                    src="images/ministre.jpg"
                    alt="Ministre"
                    class="w-100 h-100 object-fit-cover"
                  />
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <h2>Son Excellence Monsieur Éliézer Ntambwe</h2>
              <span class="d-block mb-3">
                Ministre Délégué Près le Ministre de la Défense Nationale et
                Anciens Combattants en charge des Anciens Combattants
              </span>
              <cite>
                <p>
                  " Lorem ipsum dolor sit amet consectetur adipisicing elit. Ut
                  nostrum corrupti amet quas quis! Eius accusantium a corrupti
                  nesciunt odio, in veritatis, libero non dolores, optio illum!
                  Dolores, veritatis doloribus. "
                </p>
              </cite>
              <cite>
                <p class="mb-4">
                  " Lorem ipsum dolor sit amet consectetur adipisicing elit. Ut
                  nostrum corrupti amet quas quis! Eius accusantium a corrupti
                  nesciunt odio, in veritatis, libero non dolores, optio illum!
                  Dolores, veritatis doloribus. "
                </p>
              </cite>
              <a href="leministre.html" class="btn btn-default btn-red"> En savoir plus </a>
            </div>
          </div>
        </div>
      </div>
      <div class="event">
        <div class="container">
          <div class="row g-lg-4 g-xxl-5">
            <div class="col-lg-8">
              <div
                class="d-flex justify-content-between align-items-center mb-4"
              >
                <h2 class="mb-0 text-white">Evévements recents</h2>
                <a href="#" class="link-red d-inline-flex gap-2 text-white">
                  Voir tout
                  <i class="bi bi-arrow-right"></i>
                </a>
              </div>
              <div class="row g-lg-3">
                <div class="col-lg-4">
                  <div class="card card-event">
                    <div class="img">
                      <div
                        class="date d-flex justify-content-center align-items-center"
                      >
                        27 <br />
                        sept
                      </div>
                      <img src="images/p.jpg" alt="force" class="w-100" />
                    </div>
                    <div class="content-event p-2">
                      <h5 class="mt-1 mb-3">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit.
                        Quam repellat porro tempore!
                      </h5>
                      <a href="#" class="link-red"> Lire la suite </a>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="card card-event">
                    <div class="img">
                      <div
                        class="date d-flex justify-content-center align-items-center"
                      >
                        27 <br />
                        sept
                      </div>
                      <img src="images/p.jpg" alt="force" class="w-100" />
                    </div>
                    <div class="content-event p-2">
                      <h5 class="mt-1 mb-3">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit.
                        Quam repellat porro tempore!
                      </h5>
                      <a href="#" class="link-red"> Lire la suite </a>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="card card-event">
                    <div class="img">
                      <div
                        class="date d-flex justify-content-center align-items-center"
                      >
                        27 <br />
                        sept
                      </div>
                      <img src="images/p.jpg" alt="force" class="w-100" />
                    </div>
                    <div class="content-event p-2">
                      <h5 class="mt-1 mb-3">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit.
                        Quam repellat porro tempore!
                      </h5>
                      <a href="#" class="link-red"> Lire la suite </a>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="card card-event">
                    <div class="img">
                      <div
                        class="date d-flex justify-content-center align-items-center"
                      >
                        27 <br />
                        sept
                      </div>
                      <img src="images/p.jpg" alt="force" class="w-100" />
                    </div>
                    <div class="content-event p-2">
                      <h5 class="mt-1 mb-3">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit.
                        Quam repellat porro tempore!
                      </h5>
                      <a href="#" class="link-red"> Lire la suite </a>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="card card-event">
                    <div class="img">
                      <div
                        class="date d-flex justify-content-center align-items-center"
                      >
                        27 <br />
                        sept
                      </div>
                      <img src="images/p.jpg" alt="force" class="w-100" />
                    </div>
                    <div class="content-event p-2">
                      <h5 class="mt-1 mb-3">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit.
                        Quam repellat porro tempore!
                      </h5>
                      <a href="#" class="link-red"> Lire la suite </a>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="card card-event">
                    <div class="img">
                      <div
                        class="date d-flex justify-content-center align-items-center"
                      >
                        27 <br />
                        sept
                      </div>
                      <img src="images/p.jpg" alt="force" class="w-100" />
                    </div>
                    <div class="content-event p-2">
                      <h5 class="mt-1 mb-3">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit.
                        Quam repellat porro tempore!
                      </h5>
                      <a href="#" class="link-red"> Lire la suite </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="card card-communique h-100">
                <div
                  class="d-flex align-items-center justify-content-between mb-3"
                >
                  <h4>Communiqués</h4>
                  <a href="#" class="link-red d-inline-flex gap-2">
                    Voir tout
                    <i class="bi bi-arrow-right"></i>
                  </a>
                </div>
                <div class="d-flex flex-column gap-2">
                  <div class="card">
                    <div class="d-flex gap-2">
                      <div class="icon">
                        <i class="bi bi-file-earmark-text"></i>
                      </div>
                      <div class="content">
                        <h5>
                          Lorem ipsum dolor sit amet consectetur adipisicing
                          elit. Quam repellat porro tempore!
                        </h5>

                        <div class="date">26 sept 2025</div>
                      </div>
                    </div>
                  </div>
                  <div class="card">
                    <div class="d-flex gap-2">
                      <div class="icon">
                        <i class="bi bi-file-earmark-text"></i>
                      </div>
                      <div class="content">
                        <h5>
                          Lorem ipsum dolor sit amet consectetur adipisicing
                          elit. Quam repellat porro tempore!
                        </h5>

                        <div class="date">26 sept 2025</div>
                      </div>
                    </div>
                  </div>
                  <div class="card">
                    <div class="d-flex gap-2">
                      <div class="icon">
                        <i class="bi bi-file-earmark-text"></i>
                      </div>
                      <div class="content">
                        <h5>
                          Lorem ipsum dolor sit amet consectetur adipisicing
                          elit. Quam repellat porro tempore!
                        </h5>

                        <div class="date">26 sept 2025</div>
                      </div>
                    </div>
                  </div>
                  <div class="card">
                    <div class="d-flex gap-2">
                      <div class="icon">
                        <i class="bi bi-file-earmark-text"></i>
                      </div>
                      <div class="content">
                        <h5>
                          Lorem ipsum dolor sit amet consectetur adipisicing
                          elit. Quam repellat porro tempore!
                        </h5>

                        <div class="date">26 sept 2025</div>
                      </div>
                    </div>
                  </div>
                  <div class="card">
                    <div class="d-flex gap-2">
                      <div class="icon">
                        <i class="bi bi-file-earmark-text"></i>
                      </div>
                      <div class="content">
                        <h5>
                          Lorem ipsum dolor sit amet consectetur adipisicing
                          elit. Quam repellat porro tempore!
                        </h5>

                        <div class="date">26 sept 2025</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="officiels">
        <div class="container">
          <div class="d-flex justify-content-between align-items-center mb-5">
            <h2 class="mb-0">Les officiels</h2>
            <a href="#" class="link-red d-inline-flex gap-2">
              Voir tout
              <i class="bi bi-arrow-right"></i>
            </a>
          </div>
          <div class="row">
            <div class="col-lg-3">
              <div class="card card-officiel">
                <div class="img">
                  <img src="images/officiel.png" alt="officiel" class="w-100" />
                </div>
                <div class="content p-2">
                  <h5 class="mb-1">Ministre de la Défense Nationale</h5>
                  <p class="date">26 sept 2025</p>
                </div>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="card card-officiel">
                <div class="img">
                  <img src="images/officiel.png" alt="officiel" class="w-100" />
                </div>
                <div class="content p-2">
                  <h5 class="mb-1">Ministre de la Défense Nationale</h5>
                  <p class="date">26 sept 2025</p>
                </div>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="card card-officiel">
                <div class="img">
                  <img src="images/officiel.png" alt="officiel" class="w-100" />
                </div>
                <div class="content p-2">
                  <h5 class="mb-1">Ministre de la Défense Nationale</h5>
                  <p class="date">26 sept 2025</p>
                </div>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="card card-officiel">
                <div class="img">
                  <img src="images/officiel.png" alt="officiel" class="w-100" />
                </div>
                <div class="content p-2">
                  <h5 class="mb-1">Ministre de la Défense Nationale</h5>
                  <p class="date">26 sept 2025</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
   


@endsection
