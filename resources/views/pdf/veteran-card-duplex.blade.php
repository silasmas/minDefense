<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <style>
        /* Taille papier exacte (variables injectées depuis le contrôleur) */
        @page {
            margin: 0;
            size: {{ $CARD_W_MM }}mm {{ $CARD_H_MM }}mm;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
        }
/* ======================= OVERRIDES PDF ======================== */
/* ---- Variables (unités adaptées au PDF : points et millimètres) ---- */
:root{
  --id-label-pt:  6.7pt;  /* libellés : "Prénom", "Nom", etc.             */
  --id-value-pt:  8.2pt;  /* valeurs : "Marcel", "Adam", l’adresse, etc.  */
  --role-pt:      7.5pt;  /* grade sous la photo                          */
  --badge-pt:     7.4pt;  /* texte du badge statut                        */
  --badge-pad-y-mm: 0.9mm;/* padding vertical du badge                    */
  --badge-pad-x-mm: 2.2mm;/* padding horizontal du badge                  */
  --photo-role-gap-mm: 0.9mm; /* espace photo ↔ grade sous la photo      */
}

/* ---- Identité ---- */
.idcard .label{
  font-size: var(--id-label-pt);       /* ↓ libellés plus petits */
}
.idcard .value{
  font-size: var(--id-value-pt);       /* ↓ valeurs plus petites */
}

/* ---- Grade + espacement ---- */
.idcard .photobox{
  gap: var(--photo-role-gap-mm);       /* ↓ distance photo ←→ grade                              */
}
.idcard .role{
  font-size: var(--role-pt);           /* ↓ taille du grade sous la photo                        */
}

/* ---- Badge ---- */
.idcard .badge{
  font-size: var(--badge-pt);          /* ↓ taille du texte du badge                             */
  padding: var(--badge-pad-y-mm) var(--badge-pad-x-mm); /* ↓ épaisseur/longueur du badge    */
}
/* ===================== FIN OVERRIDES PDF ====================== */

        /* Thème couleurs (faciles à changer) */
        :root {
            --bg: #c7eef7;
            --border: #2aa9bd;
            --accent: #0b3c5d;
            --line: #f2c431;
        }

        /* Carte = bloc avec fond, bord et arrondi */
        .idcard {
            width: 100%;
            height: 100%;
            box-sizing: border-box;
            background: var(--bg);
            border: 0.45mm solid var(--border);
            border-radius: 3mm;
            padding: 3mm 4mm;
        }

        /* EN-TETE (table pour compat DomPDF) */
        .hdr {
            width: 100%;
            border-collapse: collapse;
        }

        .hdr td {
            vertical-align: middle;
        }

        .hdr .left,
        .hdr .right {
            width: 14mm;
        }

        /* largeur réservée aux deux “slots” */
        .slot {
            width: 14mm;
            height: 11mm;
            border-radius: 2mm;
            background: #fff;
            border: 0.3mm solid #dfe7ea;
            display: block;
            overflow: hidden;
        }

        .slot img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .center {
            text-align: center;
        }

        .title-1 {
            font-weight: 800;
            font-size: 9pt;
            letter-spacing: .2pt;
        }

        /* ligne 1 */
        .title-2 {
            font-weight: 800;
            font-size: 8pt;
            letter-spacing: .2pt;
        }

        /* ligne 2 */
        .cut {
            height: 1.2mm;
            background: var(--line);
            border-radius: 2mm;
            margin: 1.6mm 0 2mm;
        }

        /* CORPS RECTO */
        .grid {
            width: 100%;
            border-collapse: collapse;
        }

        .leftcol {
            width: 31mm;
            vertical-align: top;
        }

        /* colonne photo/role/statut */
        .photobox {
            background: #fff;
            border-radius: 3mm;
            border: 0.3mm solid #e5eef2;
            padding: 1.5mm;
            text-align: center;
        }

        .photo {
            width: 26mm;
            height: 32mm;
            object-fit: cover;
            border-radius: 2mm;
            display: block;
            margin: 0 auto;
        }

        .role {
            font-weight: 800;
            text-align: center;
            margin-top: 1mm;
            font-size: 8pt;
        }

        /* poste sous la photo */
        .status {
            margin-top: 2mm;
            text-align: center;
        }

        /* badge statut */
        .badge {
            display: inline-block;
            padding: 1.2mm 3mm;
            border-radius: 2mm;
            color: #fff;
            font-weight: 800;
            font-size: 8pt;
        }

        .b-green {
            background: #16a34a;
        }

        .b-amber {
            background: #f59e0b;
        }

        .b-gray {
            background: #6b7280;
        }

        .b-red {
            background: #dc2626;
        }

        /* Champs texte à droite */
        .field {
            margin-bottom: 1.3mm;
        }

        .label {
            font-size: 7.2pt;
            color: #334155;
        }

        .value {
            font-size: 9pt;
            font-weight: 700;
        }

        .noline {
            text-decoration: underline;
        }

        /* soulignement N° série */

        /* Délivré à… + signature */
        .meta {
            margin-top: 2mm;
            font-size: 8pt;
            text-align: center;
        }

        .sign {
            margin-top: 2mm;
            text-align: center;
        }

        .sign img {
            height: 10mm;
        }

        /* Pied recto : QR côté droit */
        .footer {
            margin-top: 1.5mm;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .qr svg {
            width: 17mm;
            height: 17mm;
            display: block;
        }

        /* Verso */
        .verso {
            text-align: center;
        }

        .verso-title {
            font-weight: 800;
            font-size: 11pt;
            letter-spacing: .3pt;
            margin: 2mm 0 3mm;
        }

        .qr-big svg {
            width: 34mm;
            height: 34mm;
            display: inline-block;
        }

        .serial {
            margin-top: 3mm;
            font-size: 10pt;
            font-weight: 800;
        }

        .page-break {
            page-break-after: always;
        }

        /* force saut de page PDF */
    </style>
</head>

<body>

    {{-- RECTO --}}
    <div class="idcard">
        <table class="hdr">
            <tr>
                <td class="left"><span class="slot">
                        @if (!empty($flagDataUri))
                            <img src="{{ $flagDataUri }}" alt="flag">
                        @endif
                    </span>
                </td>
                <td class="center">
                    {{-- $labelMap = ['recognized'=>'VALIDÉ','suspended'=>'SUSPENDU','draft'=>'BROUILLON','deceased'=>'DÉCÉDÉ'];
          $classMap = ['recognized'=>'b-green','suspended'=>'b-amber','draft'=>'b-gray','deceased'=>'b-red'] --}}
                    <div class="title-1">{{ $title1 }}</div>
                    <div class="title-2">{{ $title2 }}</div>
                </td>
                <td class="right"><span class="slot">
                        @if (!empty($logoDataUri))
                            <img src="{{ $logoDataUri }}" alt="logo">
                        @endif
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
                            <img class="photo" src="{{ $photoData }}" alt="photo">
                        @else
                            <div class="photo" style="background:linear-gradient(135deg,#e5e7eb,#cbd5e1)"></div>
                        @endif
                    </div>
                    <div class="role">{{ $v->rank ?? '—' }}</div>
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
                    <div class="status"><span class="badge {{ $cls }}">{{ $lab }}</span></div>
                </td>
                <td style="vertical-align:top; padding-left:3mm;">
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
                        <div class="field">
                            <span class="label">Lieu de naissance &amp; Date de naissance :</span>
                            <div class="value">{{ $v->birthplace ?? '—' }}@if (!empty($v->birthdate))
                                    , {{ \Carbon\Carbon::parse($v->birthdate)->format('Y-m-d') }}
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
                    <div class="field"><span class="label">N° Série :</span> <span
                            class="value noline">{{ $v->card_number ?? '—' }}</span></div>

                    <div class="meta">Délivré à {{ $issued_city }} le {{ $issued_at->format('d/m/Y') }}</div>
                    <div class="sign">
                        @if (!empty($signatureDataUri))
                            <img src="{{ $signatureDataUri }}" alt="signature"><br>
                        @endif
                        <div class="label">{{ $signatoryName }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer">
            <div></div>
            <div class="qr">{!! $qrSvg !!}</div>
        </div>
    </div>

    <div class="page-break"></div>

    {{-- VERSO --}}
    <div class="idcard">
        <table class="hdr">
            <tr>
                <td class="left"><span class="slot">
                        @if (!empty($flagDataUri))
                            <img src="{{ $flagDataUri }}">
                        @endif
                    </span>
                </td>
                <td class="center">
                    <div class="title-1">{{ $title1 }}</div>
                    <div class="title-2">{{ $title2 }}</div>
                </td>
                <td class="right"><span class="slot">
                        @if (!empty($logoDataUri))
                            <img src="{{ $logoDataUri }}">
                        @endif
                    </span></td>
            </tr>
        </table>
        <div class="cut"></div>

        <div class="verso">
            <div class="verso-title">CARTE DES ANCIENS COMBATTANTS</div>
            <div class="qr-big">{!! $qrSvg !!}</div>
            <div class="serial">N° Série : {{ $v->card_number ?? '—' }}</div>
        </div>
    </div>

</body>

</html>
