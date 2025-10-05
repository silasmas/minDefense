

{{-- resources/views/filament/assets/map-view.blade.php --}}
@php
    /** @var \App\Models\StateAsset $record */
    $record = $getRecord();
    // $lat = $record->lat ?? -4.322447;
    // $lng = $record->lng ?? 15.307045;
    $lat = $record->lat ?? "";
    $lng = $record->lng ?? "";
    $isMateriel = $record->asset_type === 'materiel';
    $footprint = $record->footprint ?? null; // pour immobilier carré/poly
@endphp

<div class="flex gap-4 items-start">
    <div id="asset-leaflet-map-view" class="rounded-xl border" style="height: 420px; width: 100%;"></div>

    @if($isMateriel)
        <div class="shrink-0 flex flex-col items-start gap-2">
            <img
                src="{{ $record->material_image_url }}"
                alt="Matériel"
                class="rounded-lg border max-h-40"
                onerror="this.style.display='none';"
            />
            <div class="text-sm opacity-80">
                <div><strong>Catégorie :</strong> {{ $record->material_category ?? 'n/a' }}</div>
                <div><strong>Localisation :</strong> {{ $record->lat }}, {{ $record->lng }}</div>
            </div>
        </div>
    @endif
</div>

@once
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
@endonce

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const map = L.map('asset-leaflet-map-view').setView([{{ $lat }}, {{ $lng }}], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 20,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        // Toujours un marker
        const marker = L.marker([{{ $lat }}, {{ $lng }}]).addTo(map);

        // Si immobilier et footprint disponible, on trace le polygone + fit bounds
        @if(!$isMateriel && $footprint && is_array($footprint) && count($footprint) >= 4)
            const poly = L.polygon(@json($footprint), {weight: 2}).addTo(map);
            map.fitBounds(poly.getBounds(), { maxZoom: 18 });
        @endif
    });
</script>
