@php
    /** @var array $center */   $center    = $center    ?? ['lat'=>-2.88,'lng'=>23.65,'zoom'=>5];
    /** @var string $searchUrl */$searchUrl = $searchUrl ?? url('/admin/api/assets');
    /** @var string $mapId */    $mapId     = $mapId     ?? 'state-assets-leaflet';
@endphp

<div
    wire:key="assets-map-widget"
    x-data="window.AssetsMap.make({
        center: @js($center),
        searchUrl: @js($searchUrl),
        mapId: @js($mapId)
    })"
    x-init="$nextTick(() => init())"
    class="relative w-full"
>
    {{-- Barre outils (recherche + filtres) --}}
    <div class="map-toolbar flex items-center gap-2">
        <div class="bg-white border rounded-xl shadow p-2 flex items-center gap-2">
            <input x-ref="query"
                   x-model.debounce.400ms="state.q"
                   @keydown.enter.prevent="reload(true)"
                   placeholder="Rechercher: nom ou matricule"
                   class="border rounded-lg px-3 py-2 w-72 text-sm outline-none" />
            <button @click="reload(true)" class="border rounded-lg px-3 py-2 text-sm hover:bg-gray-50">Rechercher</button>
        </div>
        <div class="bg-white border rounded-xl shadow p-2 text-sm">
            <label class="mr-3"><input type="checkbox" x-model="state.showImmobilier" /> Immobilier</label>
            <label class="mr-3"><input type="checkbox" x-model="state.showMateriel" /> Matériel</label>
            <label><input type="checkbox" x-model="state.cluster" @change="toggleCluster()" /> Cluster</label>
        </div>
    </div>

    {{-- Légende --}}
    <div class="map-legend">
        <h4>Légende</h4>
        <div class="row"><span class="chip chip-imm"></span> Immobilier (polygone / carré)</div>
        <div class="row"><span class="chip chip-mat"></span> Matériel (marqueur)</div>
    </div>

    {{-- Carte --}}
    <div class="rounded-xl border overflow-hidden">
        <div id="{{ $mapId }}" wire:ignore style="height: calc(100vh - 240px); min-height:560px; width:100%"></div>
    </div>
    <div class="text-xs text-gray-500 px-2 py-1">API: {{ $searchUrl }}</div>
</div>

@script
window.AssetsMap = {
    const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
if (!res.ok) {
  console.error('API error', res.status, await res.text());
  return;
}
const json = await res.json();
  make(cfg){
    return {
      // === état ===
      map:null, immLayer:null, matLayer:null, clusterGroup:null,
      center: cfg.center, searchUrl: cfg.searchUrl, mapId: cfg.mapId,
      state: { q: '', showImmobilier:true, showMateriel:true, cluster:true },

      init(){
        this.waitFor(
          () => typeof L !== 'undefined' && this.sized(),
          () => {
            const el = document.getElementById(this.mapId);
            this.map = L.map(el, { zoomControl: true })
              .setView([this.center.lat, this.center.lng], this.center.zoom);

            // Fond de plan
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
              { maxZoom: 20, attribution: '&copy; OpenStreetMap' }).addTo(this.map);

            // Couches
            this.immLayer = L.layerGroup().addTo(this.map); // polygones/rectangles
            this.matLayer = L.layerGroup().addTo(this.map); // marqueurs simples
            this.clusterGroup = L.markerClusterGroup({ maxClusterRadius: 50 });

            // 1er chargement
            this.reload(true);

            // Redessiner à la volée quand filtres changent
            this.$watch('state.showImmobilier', () => this.applyVisibility());
            this.$watch('state.showMateriel',   () => this.applyVisibility());

            // Resize safe
            addEventListener('resize', ()=>this.invalidate());
            document.addEventListener('livewire:navigated', ()=>this.invalidate());
          }
        );
      },

      sized(){
        const el = document.getElementById(this.mapId);
        if (!el) return false; const r = el.getBoundingClientRect();
        return r.width>0 && r.height>0;
      },
      waitFor(test,done,i=0){ if(test()) return done(); if(i>100) return; setTimeout(()=>this.waitFor(test,done,i+1),50); },
      invalidate(){ try{ this.map.invalidateSize(); }catch(e){} },

      async reload(fit=false){
        const url = new URL(this.searchUrl, window.location.origin);
        if (this.state.q) url.searchParams.set('q', this.state.q);

        // Tu peux aussi envoyer l’emprise courante (BBOX) pour du “lazy load par zone”
        // const b = this.map.getBounds();
        // url.searchParams.set('bbox', [b.getSouthWest().lat,b.getSouthWest().lng,b.getNorthEast().lat,b.getNorthEast().lng].join(','));

        const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
        const json = await res.json();

        this.render(json.items || [], fit);
      },

      toggleCluster(){
        // Re-render pour basculer cluster ON/OFF
        this.reload(true);
      },

      render(items, fit=false){
        // Reset layers
        this.immLayer.clearLayers();
        this.matLayer.clearLayers();
        this.clusterGroup && this.map.removeLayer(this.clusterGroup);

        const bounds = [];

        // Si cluster, on ajoutera tous les markers dedans
        const addMarker = (latlng, icon, popupHtml) => {
          const m = L.marker(latlng, icon ? { icon } : undefined).bindPopup(popupHtml);
          if (this.state.cluster) {
            this.clusterGroup.addLayer(m);
          } else {
            this.matLayer.addLayer(m);
          }
          bounds.push(m.getLatLng());
        };

        if (this.state.cluster) {
          this.clusterGroup = L.markerClusterGroup({ maxClusterRadius: 50 });
          this.map.addLayer(this.clusterGroup);
        }

        for (const it of items) {
          if (typeof it.lat !== 'number' || typeof it.lng !== 'number') continue;

          const latlng = [it.lat, it.lng];
          const popup = this.popup(it);

          if (it.type === 'immobilier') {
            // Polygone ou carré approximatif selon les données
            let poly = null;
            if (Array.isArray(it.footprint) && it.footprint.length >= 3) {
              poly = L.polygon(it.footprint, { weight:2, color:'#059669', fillColor:'#10b981', fillOpacity:.2 });
            } else if (it.extent && it.extent > 0) {
              const coords = this.square(it.lat, it.lng, it.extent);
              poly = L.polygon(coords, { weight:2, color:'#059669', fillColor:'#10b981', fillOpacity:.2 });
            }
            if (poly) {
              poly.bindPopup(popup).addTo(this.immLayer);
              try { bounds.push(...poly.getLatLngs()[0]); } catch(e) {}
            }
            // Petit point au centre
            const dot = L.circleMarker(latlng, { radius:3, color:'#047857', fillOpacity:1 });
            dot.bindPopup(popup).addTo(this.immLayer);
          } else {
            // Matériel = marker (avec icône si dispo)
            let icon = null;
            if (it.icon) {
              icon = L.icon({ iconUrl: it.icon, iconSize:[28,28], iconAnchor:[14,14], className:'rounded-full border' });
            } else {
              icon = L.divIcon({
                className:'', html:'<div style="width:14px;height:14px;border-radius:9999px;background:#38bdf8;border:1px solid #0284c7"></div>',
                iconSize:[14,14]
              });
            }
            addMarker(latlng, icon, popup);
          }
        }

        this.applyVisibility();

        if (fit && bounds.length) {
          const g = L.featureGroup(bounds.map(p => L.marker(p.lat ? [p.lat, p.lng] : p)));
          try { this.map.fitBounds(g.getBounds(), { padding:[20,20], maxZoom: 16 }); } catch(e) {}
        }
      },

      applyVisibility(){
        if (this.state.showImmobilier) {
          this.map.addLayer(this.immLayer);
        } else {
          this.map.removeLayer(this.immLayer);
        }
        if (this.state.showMateriel) {
          if (this.state.cluster) this.map.addLayer(this.clusterGroup);
          else this.map.addLayer(this.matLayer);
        } else {
          this.map.removeLayer(this.clusterGroup);
          this.map.removeLayer(this.matLayer);
        }
      },

      popup(it){
        const loc = [it.city, it.province].filter(Boolean).join(' • ');
        const code = [it.code, it.matricule].filter(Boolean).join(' — ');
        return `
          <div style="min-width:240px">
            <div style="font-weight:600">${it.title ?? '—'}</div>
            <div style="font-size:12px;opacity:.75">${it.type ?? ''}${loc ? ' — '+loc : ''}</div>
            ${code ? `<div style="margin-top:6px;font-size:12px">Ref: ${code}</div>` : ''}
            <div style="margin-top:8px"><a href="${it.url ?? '#'}" class="underline" target="_blank">Ouvrir la fiche</a></div>
          </div>`;
      },

      // carré approx centré (lat,lng), côté en mètres
      square(lat,lng,m){
        const dLat = (m/111320)/2;
        const dLng = (m/(111320*Math.max(Math.cos(lat*Math.PI/180),1e-6)))/2;
        return [[lat+dLat,lng-dLng],[lat+dLat,lng+dLng],[lat-dLat,lng+dLng],[lat-dLat,lng-dLng]];
      },
    }
  }
}
@endScript
