@extends('site.template')


@section("content")
 <div class="banner-page">
        <div class="container">
          <div class="row">
            <div class="col-lg-12">
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                  <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                  <li class="breadcrumb-item active" aria-current="page">
                    Contact
                  </li>
                </ol>
              </nav>
              <h1 class="mb-0">Contact</h1>
            </div>
          </div>
        </div>
      </div>
      <div class="block-contact">
        <div class="container">
          <div class="row g-lg-3 g-xxl-5">
            <div class="col-lg-4 col-md-5">
              <div class="card card-info-contact">
                <h4 class="mb-3">Infos contact</h4>
                <div class="d-flex flex-column gap-3">
                  <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-geo-alt-fill icon-contact"></i>
                    <p class="mb-0">
                      Ministère de la Défense Nationale Avenue des Forces
                      Armées, Kinshasa
                    </p>
                  </div>
                  <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-telephone-fill icon-contact"></i>
                    <a href="tel:+243XXXXXX" class="mb-0">
                      <p class="mb-0">+243 XXX XXX XXX</p>
                    </a>
                  </div>
                  <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-envelope-fill icon-contact"></i>
                    <a href="mailto:contact@defense.gouv.cd" class="mb-0">
                      <p class="mb-0">contact@defense.gouv.cd</p>
                    </a>
                  </div>
                </div>
                <div class="d-flex gap-3 flex-wrap mt-4">
                  <a
                    href="#"
                    class="text-light link-network facebook"
                    title="Facebook"
                  >
                    <i class="bi bi-facebook"></i>
                  </a>
                  <a
                    href="#"
                    class="text-light link-network twitter"
                    title="Twitter"
                  >
                    <i class="bi bi-twitter-x"></i>
                  </a>
                  <a
                    href="#"
                    class="text-light link-network youtube"
                    title="YouTube"
                  >
                    <i class="bi bi-youtube"></i>
                  </a>
                  <a
                    href="#"
                    class="text-light link-network linkedin"
                    title="LinkedIn"
                  >
                    <i class="bi bi-linkedin"></i>
                  </a>
                </div>
              </div>
              <div class="card-map card mt-3">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3978.5707480427477!2d15.307976875761788!3d-4.303210046367954!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1a6a33f515b7dec7%3A0x8f5fd9c19cab5edf!2sBd%20Du%2030%20Juin%2C%20Kinshasa%2C%20R%C3%A9publique%20d%C3%A9mocratique%20du%20Congo!5e0!3m2!1sfr!2scg!4v1759428416494!5m2!1sfr!2scg" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
              </div>
            </div>
            <div class="col-lg-8 col-md-7">
              <div class="card card-contact">
                <h5>Prenons contact</h5>
                <p>
                  Veuillez remplir le formulaire ci-dessous pour prendre contact
                  avec nous.
                </p>
                <form action="">
                  <div class="row g-3">
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label for="name">Nom complet</label>
                        <input
                          type="text"
                          class="form-control"
                          id="name"
                          placeholder="Nom"
                        />
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label for="email">Email</label>
                        <input
                          type="email"
                          class="form-control"
                          id="email"
                          placeholder="Email"
                        />
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label for="text">Téléphone</label>
                        <input
                          type="email"
                          class="form-control"
                          id="email"
                          placeholder="Email"
                        />
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label for="text">Sujet</label>
                        <input
                          type="email"
                          class="form-control"
                          id="email"
                          placeholder="Email"
                        />
                      </div>
                    </div>
                    <div class="col-lg-12">
                      <div class="form-group">
                        <label for="text">Message</label>
                        <textarea
                          class="form-control"
                          id="message"
                          placeholder="Message"
                          rows="5"
                        ></textarea>
                      </div>
                    </div>
                    <div class="col-lg-12">
                      <div class="d-flex justify-content-end mt-2">
                        <button class="btn btn-default btn-red">Envoyer</button>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
@endsection