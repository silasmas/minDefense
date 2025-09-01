<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Vérification d'identité</title>
    <style>
        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            max-width: 680px;
            margin: 24px auto;
            padding: 0 16px
        }

        .card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 16px
        }

        .row {
            display: flex;
            gap: 16px
        }

        .photo {
            width: 120px;
            height: 150px;
            object-fit: cover;
            border: 1px solid #ccc;
            border-radius: 6px;
            background: #f2f2f2
        }

        .btn {
            display: inline-block;
            background: #0ea5e9;
            color: #fff;
            padding: 10px 16px;
            border-radius: 8px;
            text-decoration: none
        }

        .btn:active {
            transform: translateY(1px)
        }

        .meta {
            color: #666;
            font-size: 14px
        }
    </style>
</head>

<body>
    <h2>Confirmer vos informations</h2>
    <p>Veuillez vérifier les informations ci-dessous puis confirmer. Ce lien expire le
        <strong>{{ $verify->expires_at->format('d/m/Y H:i') }}</strong>.</p>

    <div class="card">
        <div class="row">
            @if ($v->photo_path)
                <img class="photo" src="{{ $v->photo_url }}" alt="photo">
            @else
                <div class="photo"></div>
            @endif
            <div>
                <div><strong>Nom :</strong> {{ $v->lastname }} {{ $v->firstname }}</div>
                <div><strong>Matricule :</strong> {{ $v->service_number }}</div>
                <div><strong>Statut actuel :</strong> {{ $map[$v->status] ?? $v->status }}</div>
                @if ($verify->next_status)
                    <div><strong>Statut proposé :</strong> {{ $map[$verify->next_status] }}</div>
                @endif
                <div class="meta">Téléphone : {{ $verify->phone }}</div>
            </div>
        </div>

        <form method="post" action="{{ route('veterans.sms.verify.submit', $verify->token) }}" style="margin-top:16px">
            @csrf
            <button class="btn" type="submit">Je confirme</button>
        </form>
        <form method="post" action="{{ route('veterans.sms.verify.decline', $verify->token) }}" style="margin-top:12px">
  @csrf
  <label for="reason" style="display:block;margin-bottom:6px">Mes informations sont incorrectes :</label>
  <textarea id="reason" name="reason" rows="3" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:8px"></textarea>
  <button class="btn" type="submit" style="background:#ef4444;margin-top:8px">Je décline</button>
</form>
    </div>
</body>

</html>
