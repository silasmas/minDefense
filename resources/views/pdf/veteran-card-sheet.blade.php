<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, 85.6mm);
            gap: 6mm;
            justify-content: center;
        }

        .slot {
            width: 85.6mm;
            height: 54mm;
            border: 0.35mm solid #222;
            border-radius: 1.2mm;
            padding: 3mm 4mm;
            box-sizing: border-box;
            display: grid;
            grid-template-rows: auto 1fr auto;
            row-gap: 2mm;
        }

        .head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start
        }

        .title {
            font-weight: 800;
            font-size: 10pt
        }

        .muted {
            color: #666;
            font-size: 8pt
        }

        .body {
            display: grid;
            grid-template-columns: 26mm 1fr;
            column-gap: 3mm
        }

        .ph {
            width: 26mm;
            height: 34mm;
            object-fit: cover;
            border: .25mm solid #777;
            border-radius: 1mm;
            background: #f3f3f3
        }

        .lbl {
            font-size: 7pt;
            color: #666
        }

        .val {
            font-size: 8.5pt;
            font-weight: 700
        }

        .row {
            margin-bottom: 1mm
        }

        .foot {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 7pt
        }
    </style>
</head>

<body>
    <div class="grid">
        @foreach ($veterans as $v)
            @php
                $photo = null;
                try {
                    if ($v->photo_path) {
                        $disk = $v->photo_disk ?: 'public';
                        $b = \Storage::disk($disk)->get($v->photo_path);
                        $m = \Storage::disk($disk)->mimeType($v->photo_path) ?: 'image/jpeg';
                        $photo = 'data:' . $m . ';base64,' . base64_encode($b);
                    }
                } catch (\Throwable $e) {
                }
                $map = [
                    'draft' => 'Brouillon',
                    'recognized' => 'Reconnu',
                    'suspended' => 'Suspendu',
                    'deceased' => 'Décédé',
                ];
            @endphp
            <div class="slot">
                <div class="head">
                    <div class="title">CARTE D'ANCIEN COMBATTANT</div>
                    <div class="muted">{{ $v->card_number ?? '' }}</div>
                </div>
                <div class="body">
                    @if ($photo)
                    <img class="ph" src="{{ $photo }}">@else<div class="ph"></div>
                    @endif
                    <div>
                        <div class="row">
                            <div class="lbl">Nom & Prénom</div>
                            <div class="val">{{ $v->lastname }} {{ $v->firstname }}</div>
                        </div>
                        <div class="row">
                            <div class="lbl">Matricule</div>
                            <div class="val">{{ $v->service_number }}</div>
                        </div>
                        <div class="row">
                            <div class="lbl">Statut</div>
                            <div class="val">{{ $map[$v->status] ?? $v->status }}</div>
                        </div>
                    </div>
                </div>
                <div class="foot">
                    <div>Expire : <strong>{{ optional($v->card_expires_at)->format('d/m/Y') }}</strong></div>
                    <div></div>
                </div>
            </div>
        @endforeach
    </div>
</body>

</html>
