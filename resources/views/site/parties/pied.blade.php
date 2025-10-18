<script src="{{asset('js/jquery-3.6.0.min.js')}}"></script>
<script src="{{asset('js/bootstrap.bundle.min.js')}}"></script>

<script>
    $(document).ready(function () {
        $(this).scroll(function () {
            if ($(this).scrollTop() > 40) {
                $(".header-nav").addClass("boxShadow");
            } else {
                $(".header-nav").removeClass("boxShadow");
            }
        });
    });
    // ===== GESTION DU POPUP D'ACCUEIL =====
    document.addEventListener("DOMContentLoaded", function () {
        const welcomePopup = document.getElementById("welcomePopup");
        const closeWelcomeBtn = document.getElementById("closeWelcomePopup");
        const closeWelcomeBtnFooter =
            document.getElementById("closeWelcomeBtn");
        const exploreBtn = document.getElementById("exploreBtn");

        // Vérifier si l'utilisateur a déjà vu le popup
        const hasSeenWelcome = localStorage.getItem("welcomePopupSeen");

        if (!hasSeenWelcome) {
            // Afficher le popup d'accueil après un délai
            setTimeout(function () {
                welcomePopup.style.display = "flex";
                document.body.style.overflow = "hidden"; // Empêcher le scroll
            }, 1500); // Délai de 1.5 secondes
        } else {
            // Masquer le popup si déjà vu
            welcomePopup.style.display = "none";
        }

        // Fonction pour fermer le popup d'accueil
        function closeWelcomePopup() {
            welcomePopup.classList.add("hidden");
            document.body.style.overflow = "auto"; // Restaurer le scroll

            // Marquer comme vu dans le localStorage
            localStorage.setItem("welcomePopupSeen", "true");

            // Masquer complètement après l'animation
            setTimeout(function () {
                welcomePopup.style.display = "none";
            }, 300);
        }

        // Événements de fermeture
        closeWelcomeBtn.addEventListener("click", closeWelcomePopup);
        closeWelcomeBtnFooter.addEventListener("click", closeWelcomePopup);

        // Fermer en cliquant sur l'overlay
        welcomePopup.addEventListener("click", function (e) {
            if (e.target === welcomePopup) {
                closeWelcomePopup();
            }
        });

        // Fermer avec la touche Échap
        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape" && welcomePopup.style.display !== "none") {
                closeWelcomePopup();
            }
        });

        // Bouton "Explorer le site" - ferme le popup et scroll vers le contenu
        exploreBtn.addEventListener("click", function () {
            closeWelcomePopup();

            // Scroll vers le contenu principal après fermeture
            setTimeout(function () {
                const mainContent = document.querySelector(".global-div");
                if (mainContent) {
                    mainContent.scrollIntoView({
                        behavior: "smooth",
                        block: "start",
                    });
                }
            }, 350);
        });

        // Initialiser le script Twitter après fermeture du popup
        setTimeout(function () {
            if (typeof twttr !== "undefined" && twttr.widgets) {
                twttr.widgets.load();
            }
        }, 2000);
    });
</script>
</body>

</html>
