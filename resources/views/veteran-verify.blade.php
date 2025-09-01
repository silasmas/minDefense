<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vérification – Ancien combattant</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            padding: 20px;
            max-width: 720px;
            margin: auto
        }

        .row {
            display: flex;
            gap: 16px;
            align-items: flex-start
        }

        .photo {
            width: 120px;
            height: 150px;
            object-fit: cover;
            border: 1px solid #ccc
        }

        .ok {
            color: #047857;
            font-weight: 700
        }

        .warn {
            color: #a16207;
            font-weight: 700
        }
    </style>
</head>

<body>
    <h2>Vérification d'identité – Ancien combattant</h2>
    <div class="row">
        @if ($veteran->photo_path)
            <img class="photo" src="{{ $veteran->photo_url }}" alt="photo">
        @endif
        <div>
            <div><strong>Nom :</strong> {{ $veteran->lastname }} {{ $veteran->firstname }}</div>
            <div><strong>Matricule :</strong> {{ $veteran->service_number }}</div>
            <div><strong>Statut :</strong>
                @if ($veteran->status === 'recognized')
                    <span class="ok">Reconnu</span>
                @else
                    <span class="warn">{{ ucfirst($veteran->status) }}</span>
                @endif
            </div>
        </div>
    </div>
    <p style="margin-top:12px">Si les informations ne correspondent pas, contactez l'administration.</p>
</body>

</html>
