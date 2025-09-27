@php
    // Valeurs par défaut
    $latVal = 0.0;
    $lngVal = 0.0;

    // 1) Contexte FORM (Forms\View) -> $get est une Closure
    if (isset($get) && is_callable($get)) {
        $latVal = (float) ($get('lat') ?? 0);
        $lngVal = (float) ($get('lng') ?? 0);

    // 2) Contexte INFOLIST (ViewEntry) -> on a $record
    } elseif (isset($record)) {
        $latVal = (float) ($record->lat ?? 0);
        $lngVal = (float) ($record->lng ?? 0);

    // 3) Valeurs directement injectées via viewData(['lat' => ..., 'lng' => ...])
    } else {
        $latVal = (float) ($lat ?? 0);
        $lngVal = (float) ($lng ?? 0);
    }
@endphp

@if ($latVal && $lngVal)
    <div x-data x-init="
        if (!window.L) {
            let css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            document.head.appendChild(css);

            let js = document.createElement('script');
            js.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            document.body.appendChild(js);
            js.onload = () => init();
        } else {
            init();
        }

        function init() {
            let map = L.map($refs.map).setView([{{ $latVal }}, {{ $lngVal }}], 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);
            L.marker([{{ $latVal }}, {{ $lngVal }}]).addTo(map);
        }
    ">
        <div x-ref="map" style="width:100%;height:260px;border-radius:10px;overflow:hidden"></div>
    </div>
@else
    <em>Aucune position à afficher.</em>
@endif
