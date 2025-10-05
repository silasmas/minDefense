{{-- resources/views/filament/assets/map-field.blade.php --}}
@php
    /** @var \Closure $get */  // fournie par Filament Forms\View field

    // Valeurs initiales sûres (SCALAIRES)
    $lat  = (float) ($get('lat') ?? -4.322447);
    $lng  = (float) ($get('lng') ?? 15.307045);
    $type = (string) ($get('asset_type') ?? 'immobilier');
    $side = (int) ($get('extent_side_m') ?? 0);

    // Chemins par défaut côté Livewire/Filament (adapte si besoin)
    $pathLat  = 'data.lat';
    $pathLng  = 'data.lng';
    $pathSide = 'data.extent_side_m';
    $pathFoot = 'data.footprint';
@endphp

<div
    x-data="assetMap({
        lat: {{ $lat }},
        lng: {{ $lng }},
        type: '{{ $type }}',
        side: {{ $side }},
        pathLat: '{{ $pathLat }}',
        pathLng: '{{ $pathLng }}',
        pathSide:'{{ $pathSide }}',
        pathFoot:'{{ $pathFoot }}'
    })"
    x-init="init()"
    class="w-full"
>
    <div class="flex items-center gap-2 mb-2">
        <div class="text-sm opacity-70">
            Glisse le marqueur ou saisis lat/lng. Clique « Recentrer » pour retourner au point.
        </div>
        <x-filament::button size="sm" x-on:click="recenter()">Recentrer</x-filament::button>
        <x-filament::button size="sm" x-show="type==='immobilier'" x-on:click="rebuildSquare()">Recalculer l’emprise (carré)</x-filament::button>
    </div>

    <div id="asset-leaflet-map" class="rounded-xl border" style="height: 420px;"></div>
</div>

@once
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
function assetMap(config) {
    return {
        map: null, marker: null, square: null,
        lat: config.lat, lng: config.lng, side: config.side, type: config.type,
        pathLat: config.pathLat, pathLng: config.pathLng, pathSide: config.pathSide, pathFoot: config.pathFoot,

        init() {
            this.map = L.map('asset-leaflet-map').setView([this.lat, this.lng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 20, attribution: '&copy; OpenStreetMap' }).addTo(this.map);

            this.marker = L.marker([this.lat, this.lng], { draggable: true }).addTo(this.map);
            this.marker.on('dragend', () => {
                const p = this.marker.getLatLng();
                this.lat = p.lat; this.lng = p.lng;
                this.$wire.set(this.pathLat, this.lat);
                this.$wire.set(this.pathLng, this.lng);
                if (this.type === 'immobilier' && this.side > 0) this.drawSquare();
            });

            if (this.type === 'immobilier' && this.side > 0) this.drawSquare();

            this.$watch('side', () => { if (this.type === 'immobilier') this.drawSquare(); });
        },

        recenter() { this.map.setView([this.lat, this.lng], 16); },

        rebuildSquare() {
            if (this.type !== 'immobilier') return;
            if (!this.side || this.side <= 0) { alert('Renseigne d’abord le côté (m).'); return; }
            this.drawSquare();
        },

        drawSquare() {
            if (this.square) { this.map.removeLayer(this.square); this.square = null; }
            const coords = this.makeSquare(this.lat, this.lng, this.side);
            this.square = L.polygon(coords, {weight: 2}).addTo(this.map);
            this.map.fitBounds(this.square.getBounds(), { maxZoom: 18 });
            this.$wire.set(this.pathFoot, coords.concat([coords[0]])); // ferme la boucle
        },

        makeSquare(lat, lng, sideM) {
            const degLat = sideM / 111320;
            const degLng = sideM / (111320 * Math.max(Math.cos(lat * Math.PI/180), 1e-6));
            const dLat = degLat / 2, dLng = degLng / 2;
            return [
                [lat + dLat, lng - dLng], // NW
                [lat + dLat, lng + dLng], // NE
                [lat - dLat, lng + dLng], // SE
                [lat - dLat, lng - dLng], // SW
            ];
        },
    }
}
</script>
@endonce
