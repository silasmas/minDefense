@php
    /** @var \App\Models\StateAsset $record */
    $record = $getRecord();
    $centerLat = $record->lat ?? -4.322447;
    $centerLng = $record->lng ?? 15.307045;
    $searchUrl = route('admin.api.state-assets.index');
@endphp

<div
    wire:ignore
    x-data="assetExplorer({
        center: [{{ $centerLat }}, {{ $centerLng }}],
        searchUrl: '{{ $searchUrl }}'
    })"
    x-init="$nextTick(() => init())"
    class="w-full"
>
    <div class="grid grid-cols-12 gap-4 items-start">
        {{-- Colonne résultats --}}
        <div class="col-span-12 lg:col-span-4">
            <div class="rounded-xl border p-3">
                <div class="text-sm font-medium mb-2">Recherche (nom ou code inventaire)</div>
                <div class="relative">
                    <input type="text"
                           x-model.debounce.400ms="q"
                           @keydown.enter.prevent="search()"
                           class="w-full rounded-lg border px-3 py-2"
                           placeholder="Ex: ETAT-2025-000123, Hilux, Parcelle..." />
                    <button class="absolute right-1 top-1 h-8 px-3 rounded-md border"
                            @click="search()">Chercher</button>
                </div>

                <div class="mt-3 max-h-[420px] overflow-auto divide-y">
                    <template x-if="loading">
                        <div class="py-3 text-sm opacity-60">Recherche en cours…</div>
                    </template>

                    <template x-if="!loading && items.length === 0">
                        <div class="py-3 text-sm opacity-60">
                            Aucun résultat pour « <span x-text="q"></span> ».
                        </div>
                    </template>

                    <template x-for="it in items" :key="it.id">
                        <div class="py-2 flex items-start gap-2 cursor-pointer hover:bg-gray-50 rounded-md px-2"
                             @click="focusItem(it)">
                            <div class="mt-1 w-2 h-2 rounded-full"
                                 :class="it.type === 'immobilier' ? 'bg-emerald-600' : 'bg-sky-600'"></div>
                            <div class="text-sm">
                                <div class="font-medium" x-text="it.code ?? '—'"></div>
                                <div class="opacity-80" x-text="it.title ?? '—'"></div>
                                <div class="text-xs opacity-60" x-text="it.type"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Colonne carte (rectangulaire) --}}
        <div class="col-span-12 lg:col-span-8">
            <div class="rounded-xl border overflow-hidden">
                <div id="asset-leaflet-explorer" style="height: 560px; width: 100%;"></div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
function assetExplorer(config) {
    return {
        map: null,
        markersLayer: null,
        polyLayer: null,
        q: '',
        items: [],
        loading: false,
        center: config.center,
        searchUrl: config.searchUrl,

        init() {
            if (typeof L === 'undefined') { console.error('Leaflet non chargé'); return; }

            this.map = L.map('asset-leaflet-explorer').setView(this.center, 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 20, attribution: '&copy; OpenStreetMap'
            }).addTo(this.map);

            // si rendu async -> corrige la taille
            setTimeout(() => this.map.invalidateSize(), 50);

            this.markersLayer = L.layerGroup().addTo(this.map);
            this.polyLayer    = L.layerGroup().addTo(this.map);

            // Marqueur du record courant
            const currentIcon = L.divIcon({className:'', html:'<div style="width:10px;height:10px;background:#111;border-radius:9999px"></div>', iconSize:[10,10]});
            L.marker(this.center, {icon: currentIcon}).addTo(this.markersLayer);

            // Emprise du record si immobilier
            @if($record->asset_type === 'immobilier' && is_array($record->footprint) && count($record->footprint) >= 4)
                const recPoly = L.polygon(@json($record->footprint), {weight: 2, color:'#059669'}).addTo(this.polyLayer);
                this.map.fitBounds(recPoly.getBounds(), {maxZoom: 18});
            @endif
        },

        async search() {
            const q = this.q.trim();
            this.loading = true;
            try {
                const url = new URL(this.searchUrl, window.location.origin);
                if (q.length > 0) url.searchParams.set('q', q);
                url.searchParams.set('limit', 30);

                const res = await fetch(url.toString(), {headers:{'Accept': 'application/json'}});
                const data = await res.json();
                this.items = data.items ?? [];
                this.refreshResultsOnMap();
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },

        refreshResultsOnMap() {
            this.markersLayer.clearLayers();
            this.polyLayer.clearLayers();

            const currentIcon = L.divIcon({className:'', html:'<div style="width:10px;height:10px;background:#111;border-radius:9999px"></div>', iconSize:[10,10]});
            const cur = L.marker(this.center, {icon: currentIcon}).addTo(this.markersLayer);

            let bounds = [cur.getLatLng()];

            for (const it of this.items) {
                if (typeof it.lat !== 'number' || typeof it.lng !== 'number') continue;

                const color = it.type === 'immobilier' ? '#059669' : '#0369a1';
                const icon = L.divIcon({ className:'', html:`<div style="width:10px;height:10px;background:${color};border-radius:9999px"></div>`, iconSize:[10,10] });

                const m = L.marker([it.lat, it.lng], {icon}).addTo(this.markersLayer);
                m.bindPopup(`<div style="min-width:180px">
                    <div><strong>${it.code ?? '—'}</strong></div>
                    <div>${it.title ?? ''}</div>
                    <div style="font-size:12px;opacity:.7">${it.type}</div>
                </div>`);
                bounds.push(m.getLatLng());

                if (it.type === 'immobilier' && Array.isArray(it.footprint) && it.footprint.length >= 4) {
                    try {
                        const poly = L.polygon(it.footprint, {weight:2, color}).addTo(this.polyLayer);
                        bounds = bounds.concat(poly.getLatLngs()[0]);
                    } catch (_) {}
                }
            }

            if (bounds.length >= 2) {
                const group = L.featureGroup(bounds.map(p => L.marker([p.lat ?? p[0], p.lng ?? p[1]])));
                try { this.map.fitBounds(group.getBounds(), {padding:[20,20], maxZoom: 17}); } catch (_) {}
            } else {
                this.map.setView(this.center, 15);
            }
        },

        focusItem(it) {
            if (typeof it.lat === 'number' && typeof it.lng === 'number') {
                this.map.setView([it.lat, it.lng], 17);
            }
            this.markersLayer.eachLayer(layer => {
                const ll = layer.getLatLng?.();
                if (ll && Math.abs(ll.lat - it.lat) < 1e-6 && Math.abs(ll.lng - it.lng) < 1e-6) {
                    layer.openPopup();
                }
            });
        }
    }
}
</script>
@endpush
