{{-- resources/views/cards/preview.blade.php --}}
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Prévisualisation carte</title>
    <style>
      :root{ --zoom:1; --bg:#c7eef7; --border:#2aa9bd; --line:#f2c431; } /* variables thème + zoom */
      /* ===================== OVERRIDES PREVIEW ===================== */
/* ---- Variables que TU peux modifier facilement ---- */
:root{
  --id-label-size: 12px;     /* taille des libellés : "Prénom", "Nom", "Date de naissance", etc. */
  --id-value-size: 14px;     /* taille des valeurs : "Marcel", "Adam", l’adresse, etc.            */
  --role-size:      13px;     /* taille du grade sous la photo : "Lieutenant"                      */
  --badge-font:     13px;     /* taille du texte du badge statut : "VALIDÉ"                        */
  --badge-pad-y:     3px;     /* padding vertical du badge                                         */
  --badge-pad-x:     7px;    /* padding horizontal du badge                                       */
  --photo-role-gap:  2px;     /* ESPACE entre la photo ronde et le grade (texte sous la photo)    */
}

body{                               /* page de preview */
  margin:0;                         /* enlève les marges du navigateur */
  font-family:system-ui,-apple-system,Segoe UI,Roboto,DejaVu Sans,sans-serif; /* police lisible */
  background:#f6f7fb;               /* gris clair doux derrière la carte */
}
/* ---- Identité (colonne droite) ---- */
#side-front .label{
  font-size: var(--id-label-size);     /* ↓ libellés plus petits */
}
#side-front .value{
  font-size: var(--id-value-size);     /* ↓ valeurs plus petites */
}

/* ---- Grade (sous la photo) + espacement ---- */
#side-front .photobox{
  gap: var(--photo-role-gap);          /* ↓ distance photo ←→ grade                                */
}
#side-front .role{
  font-size: var(--role-size);         /* ↓ taille du texte "Lieutenant"                            */
}

/* ---- Badge de statut ---- */
#side-front .badge{
  font-size: var(--badge-font);        /* ↓ taille du texte du badge                                */
  padding: var(--badge-pad-y) var(--badge-pad-x); /* ↓ épaisseur/longueur du badge               */
}
/* =================== FIN OVERRIDES PREVIEW =================== */
.toolbar{                            /* barre d’outils en haut */
  position:sticky; top:0;           /* reste collée en haut au scroll */
  background:#fff;                  /* fond blanc */
  border-bottom:1px solid #e5e7eb;  /* fine séparation */
  padding:10px 12px;                /* espace intérieur */
  display:flex; gap:8px;            /* boutons alignés avec un espacement */
  align-items:center;               /* centrage vertical des boutons */
  z-index:10;                       /* passe au-dessus du contenu */
}

.btn{                                /* style générique des boutons */
  appearance:none;                   /* aspect neutre cross-browser */
  border:1px solid #d1d5db;          /* fin contour gris */
  background:#fff;                   /* fond blanc */
  padding:8px 12px;                  /* espace intérieur */
  border-radius:10px;                /* coins adoucis */
  cursor:pointer;                    /* curseur main */
  font-weight:600;                   /* texte un peu fort */
}
.btn.primary{                        /* variante bouton principal */
  background:#0ea5e9;                /* bleu */
  color:#fff;                        /* texte blanc */
  border-color:#0ea5e9;              /* même couleur que le fond */
}

.wrap{                                /* conteneur pour centrer la carte */
  display:grid; place-items:center;  /* centre horizontal + vertical */
  padding:24px;                      /* respiration autour */
}
.canvas{                              /* zone zoomable */
  transform:scale(var(--zoom));      /* applique le zoom */
  transform-origin:top center;       /* zoom depuis le haut/centre */
}

/* --------- CARTE (taille écran dynamique) --------- */
.idcard{
  width: {{ $CARD_W_PX }}px;         /* largeur calculée (px) */
  height: {{ $CARD_H_PX }}px;        /* hauteur calculée (px) */
  box-shadow:0 10px 25px rgba(0,0,0,.08); /* ombre douce */
  background:var(--bg);              /* fond thème */
  border:1px solid var(--border);    /* fin contour coloré */
  border-radius:12px;                /* coins arrondis */
  padding:12px 16px;                 /* marge interne */
  box-sizing:border-box;             /* padding inclus dans width/height */
}

/* --------- EN-TÊTE --------- */
.hdr{ width:100%; }                  /* table pleine largeur */
.left,.right{ width:54px; }          /* réservations pour drapeau / logo */
.slot{                               /* cadre blanc pour drapeau/logo */
  width:54px; height:42px;           /* taille fixe (peut être ajustée) */
  border-radius:8px;                 /* arrondi doux */
  background:#fff;                   /* fond blanc */
  border:1px solid #dfe7ea;          /* léger liseré */
  display:block; overflow:hidden;    /* masque l’image qui dépasse */
}
.slot img{                           /* image de drapeau/logo */
  width:100%; height:100%;           /* remplit le slot */
  object-fit:cover;                  /* recadrage propre */
}
.center{ text-align:center; }        /* titres centrés */
.title-1{ font-weight:800; font-size:16px; }  /* ligne 1 de l’entête */
.title-2{ font-weight:800; font-size:14px; }  /* ligne 2 de l’entête */
.cut{                                 /* barre jaune sous l’entête */
  height:6px; background:var(--line);/* couleur de séparation */
  border-radius:6px;                 /* extrémités arrondies */
  margin:8px 0 12px;                 /* espaces autour */
}

/* --------- CORPS --------- */
.grid{ width:100%; }                 /* table de mise en page (compat PDF) */
.leftcol{
  width: calc({{ $CARD_W_PX }}px * 0.38); /* colonne gauche ~38% de la carte */
  vertical-align:top;                /* aligne le contenu en haut */
}

/* ---- CADRE PHOTO BLANC ---- */
.photobox{
  background:#fff;                 /* cadre blanc autour de la photo + texte */
  border-radius:12px;              /* coins arrondis */
  border:1px solid #e5eef2;        /* liseré discret */
  padding:8px;                    /* respiration interne */
  display:flex;                    /* empilement vertical contrôlé */
  flex-direction:column;           /* ↓ photo puis texte */
  align-items:center;              /* centre horizontalement */
  justify-content:center;          /* centre verticalement (si plus de hauteur) */
  gap:1px;                         /* espace entre la photo et le texte */
}

.photo{
  width: min(140px, calc({{ $CARD_W_PX }}px * 0.24));  /* diamètre du cercle */
  height: min(140px, calc({{ $CARD_W_PX }}px * 0.24)); /* idem pour maintenir un cercle */
  border-radius:50%;               /* forme ronde */
  object-fit:cover;                /* recadrage propre */
  display:block;
  border:3px solid #fff;           /* anneau blanc interne */
  box-shadow:0 0 0 3px #e5eef2;    /* anneau gris clair externe */
}

.role{
  font-weight:800;                 /* texte du poste en gras */
  text-align:center;               /* centré */
  font-size:14px;                  /* taille lisible */
  line-height:1.1;                 /* compacité */
  margin:0;                        /* on gère l’espace via gap */
}


/* ---- Badge de statut ---- */
.status{ margin-top:10px; text-align:center; } /* centré sous le rôle */
.badge{
  display:inline-block;              /* boîtier autour du texte */
  padding:6px 10px;                  /* coussin */
  border-radius:8px;                 /* coin arrondi */
  color:#fff;                        /* texte blanc */
  font-weight:800;                   /* gras */
  font-size:14px;                    /* taille texte */
}
.b-green{ background:#16a34a; }     /* VALIDÉ */
.b-amber{ background:#f59e0b; }     /* SUSPENDU */
.b-gray{  background:#6b7280; }     /* BROUILLON */
.b-red{   background:#dc2626; }     /* DÉCÉDÉ */

/* ---- Champs à droite ---- */
.field{ margin-bottom:8px; }         /* espace entre lignes */
.label{ color:#334155; font-size:14px; } /* libellé gris foncé */
.value{ font-size:16px; font-weight:700; } /* valeur accentuée */
.noline{ text-decoration:underline; }/* souligne le N° de série */

/* ---- Délivrance & signature ---- */
.meta{
  margin-top:10px;                   /* espace avant le bloc */
  font-size:14px;                    /* taille texte */
  text-align:center;                 /* centrage */
}
.sign{
  margin-top:8px;                    /* espace au-dessus */
  text-align:center;                 /* signature au centre */
}
.sign img{ height:48px; }            /* hauteur de la signature (image) */

/* ---- Bas de carte : QR à droite ---- */
.footer{
  margin-top:8px;                    /* espace avant le bas */
  display:flex;                      /* ligne flex pour écarter les blocs */
  justify-content:space-between;     /* left…right */
  align-items:flex-end;              /* aligne en bas */
}
.qr svg{
  width: max(72px, {{ (int)($CARD_W_PX*0.18) }}px); /* taille mini ou proportionnelle */
  height:auto;                       /* conserve le ratio du QR */
}

/* --------- VERSO --------- */
.verso-title{
  font-weight:800; font-size:18px;   /* titre fort */
  letter-spacing:.3px;               /* léger espacement */
  margin:10px 0 14px;                /* marges */
  text-align:center;                 /* centré */
}
.qr-big svg{
  width: max(120px, 120px); /* QR bien visible */
  height:auto;                      /* conserve le ratio */
  display:block; margin:0 auto;     /* centré */
}
.serial{
  margin-top:12px;                   /* espace au-dessus */
  font-size:18px; font-weight:800;   /* numéro lisible */
  text-align:center;                 /* centré */
}

/* --------- Impression navigateur : taille réelle en mm --------- */
@media print{
  body{ background:#fff; }           /* fond papier */
  .toolbar{ display:none; }          /* cache la barre d’outils */
  .wrap{ padding:0; }                /* pas de padding */
  .canvas{ transform:none; }         /* désactive le zoom */
  @page{ margin:0; size: {{ $CARD_W_MM }}mm {{ $CARD_H_MM }}mm; } /* format exact */
  .idcard{
    width: {{ $CARD_W_MM }}mm;       /* largeur réelle */
    height: {{ $CARD_H_MM }}mm;      /* hauteur réelle */
    box-shadow:none;                 /* pas d’ombre à l’impression */
    border:0.45mm solid var(--border); /* bord précis */
    border-radius:3mm;               /* arrondi précis */
    padding:3mm 4mm;                 /* marges internes précises */
  }
}

    </style>
</head>

<body>
    <div class="toolbar">
        <button class="btn" onclick="zoom(-.1)">Zoom −</button>
        <button class="btn" onclick="zoom(+.1)">Zoom +</button>
        <button class="btn" onclick="resetZoom()">100%</button>
        <div style="flex:1"></div>
        <button class="btn" onclick="showSide('front')">Recto</button>
        <button class="btn" onclick="showSide('back')">Verso</button>
        <a class="btn" href="{{ route('veterans.card.duplex', $v->id) }}" target="_blank">PDF Recto/Verso</a>
        <button class="btn primary" onclick="window.print()">Imprimer</button>
    </div>

    <div class="wrap">
        <div id="canvas" class="canvas">
            {{-- ===== RECTO ===== --}}
            <div id="side-front">
                <div class="idcard">
                    <table class="hdr">
                        <tr>
                            <td class="left"><span class="slot">
                                        <img src="{{ asset('images/rdcc.png') }}">

                                </span>
                            </td>
                            <td class="center">
                                <div class="title-1">{{ $title1 }}</div>
                                <div class="title-2">{{ $title2 }}</div>
                            </td>
                            <td class="right"><span class="slot">
                                        <img src="{{ asset('images/images.jpeg') }}">
                                </span>
                            </td>
                        </tr>
                    </table>
                    <div class="cut"></div>

                    <table class="grid">
                        <tr>
                            <td class="leftcol">
                                <div class="photobox">
                                    @if (!empty($photoData))
                                        <img class="photo" src="{{ $photoData }}"></br>
                                    @else
                                        <div class="photo" style="background:linear-gradient(135deg,#e5e7eb,#cbd5e1)">
                                        </div>
                                    @endif

                                    <div class="role">{{ $v->rank ?? '—' }}</div>
                                </div>
                                @php
                                    $labelMap = [
                                        'recognized' => 'VALIDÉ',
                                        'suspended' => 'SUSPENDU',
                                        'draft' => 'BROUILLON',
                                        'deceased' => 'DÉCÉDÉ',
                                    ];
                                    $classMap = [
                                        'recognized' => 'b-green',
                                        'suspended' => 'b-amber',
                                        'draft' => 'b-gray',
                                        'deceased' => 'b-red',
                                    ];
                                    $lab = $labelMap[$v->status] ?? strtoupper($v->status);
                                    $cls = $classMap[$v->status] ?? 'b-gray';
                                @endphp
                                <div class="status"><span class="badge {{ $cls }}">{{ $lab }}</span>
                                </div>
                            </td>
                            <td style="vertical-align:top; padding-left:12px;">
                                <div class="field"><span class="label">Prénom :</span> <span
                                        class="value">{{ $v->firstname }}</span>
                                    @if (!empty($v->gender))
                                        <span class="label" style="float:right;">Sexe : <span
                                                class="value">{{ strtoupper($v->gender[0]) }}</span></span>
                                    @endif
                                </div>
                                <div class="field"><span class="label">Nom :</span> <span
                                        class="value">{{ $v->lastname }}</span></div>
                                @if (!empty($v->middlename))
                                    <div class="field"><span class="label">Post-nom :</span> <span
                                            class="value">{{ $v->middlename }}</span></div>
                                @endif
                                @if (!empty($v->birthplace) || !empty($v->birthdate))
                                    <div class="field"><span class="label"> Date de naissance :</span>
                                        <div class="value">@if (!empty($v->birthdate))
                                                {{ \Carbon\Carbon::parse($v->birthdate)->format('Y-m-d') }}
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                @if (!empty($v->address))
                                    <div class="field"><span class="label">Adresses :</span>
                                        <div class="value">{{ $v->address }}</div>
                                    </div>
                                @endif
                                <div class="field"><span class="label">Matricule :</span> <span
                                        class="value">{{ $v->service_number }}</span></div>

                                <div class="meta">Délivré à {{ $issued_city }} le {{ $issued_at->format('d/m/Y') }}
                                </div>
                                <div class="sign">
                                    @if (!empty($signatureDataUri))
                                        <img src="{{ $signatureDataUri }}"><br>
                                    @endif
                                    <div class="label">{{ $signatoryName }}</div>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div class="footer">
                        <div></div>
                        {{-- /* <div class="qr">{!! $qrSvg !!}</div> */ --}}
                    </div>
                </div>
            </div>

            {{-- ===== VERSO ===== --}}
            <div id="side-back" style="display:none;">
                <div class="idcard">
                    <table class="hdr">
                        <tr>
                            <td class="left"><span class="slot">
                                        <img src="{{ asset('images/rdcc.png') }}">
                                </span>
                            </td>
                            <td class="center">
                                <div class="title-1">{{ $title1 }}</div>
                                <div class="title-2">{{ $title2 }}</div>
                            </td>
                            <td class="right"><span class="slot">
                                        <img src="{{ asset('images/images.jpeg') }}">
                                </span></td>
                        </tr>
                    </table>
                    <div class="cut"></div>
                    <div class="verso-title">CARTE DES ANCIENS COMBATTANTS</div>
                    <div class="qr-big">{!! $qrSvg !!}</div>
                    <div class="serial">N° Série : {{ $v->card_number ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Zoom UI
        let z = 1;

        function zoom(d) {
            z = Math.max(.5, Math.min(2, z + d));
            document.documentElement.style.setProperty('--zoom', z);
        }

        function resetZoom() {
            z = 1;
            document.documentElement.style.setProperty('--zoom', 1);
        }
        // Bascule recto/verso
        function showSide(s) {
            document.getElementById('side-front').style.display = (s === 'front') ? 'block' : 'none';
            document.getElementById('side-back').style.display = (s === 'back') ? 'block' : 'none';
        }
    </script>
</body>

</html>
