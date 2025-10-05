{{-- resources/views/widgets/state-assets-map.blade.php --}}
@php /** @var array $points */ /** @var array $center */ @endphp

{{-- <div
    wire:ignore
    x-data="stateAssetsMap({ points: @js($points), center: @js($center) })"
    x-init="$nextTick(() => boot())"
    class="w-full"
>
    <div class="rounded-xl border overflow-hidden">
        <div id="state-assets-leaflet" style="height:560px;min-height:560px;width:100%"></div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
@endpush --}}
<div wire:ignore x-data="stateAssetsMap({ points:@js($points), center:@js($center) })" x-init="$nextTick(()=>boot())">
  <div id="state-assets-leaflet" style="height:560px;min-height:560px;width:100%"></div>
</div>
@push('styles') <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/> @endpush
@push('scripts') <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script> … @endpush

@push('scripts')
<script>
function stateAssetsMap(cfg){
  return {
    map:null, markers:null, polys:null,
    items: cfg.points || [],
    center: cfg.center || {lat:-2.88,lng:23.65,zoom:5},

    boot(){
      this.waitFor(() => typeof L !== 'undefined' && this.containerSized(), () => {
        this.initMap();
        window.addEventListener('resize', ()=>this.invalidate());
        document.addEventListener('livewire:navigated', ()=>this.invalidate());
      });
    },
    containerSized(){
      const el = document.getElementById('state-assets-leaflet');
      if (!el) return false; const r = el.getBoundingClientRect();
      return r.width>0 && r.height>0;
    },
    waitFor(test,done,i=0){ if(test()) return done(); if(i>60) return; setTimeout(()=>this.waitFor(test,done,i+1),50); },

    initMap(){
      const el = document.getElementById('state-assets-leaflet');
      this.map = L.map(el).setView([this.center.lat,this.center.lng], this.center.zoom);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:20,attribution:'&copy; OpenStreetMap'}).addTo(this.map);
      this.markers = L.layerGroup().addTo(this.map);
      this.polys   = L.layerGroup().addTo(this.map);
      setTimeout(()=>this.invalidate(), 80);
      this.render(this.items, true);
    },
    invalidate(){ try{ this.map.invalidateSize(); }catch(e){} },

    render(items, fit=false){
      this.markers.clearLayers(); this.polys.clearLayers();
      const bounds = [];
      for(const it of items){
        if (typeof it.lat!=='number' || typeof it.lng!=='number') continue;

        if (it.type==='immobilier'){
          let poly=null;
          if (Array.isArray(it.footprint) && it.footprint.length>=4){
            poly = L.polygon(it.footprint,{weight:2,color:'#059669'}).addTo(this.polys);
          } else if (typeof it.extent==='number' && it.extent>0){
            const coords = this.square(it.lat,it.lng,it.extent);
            poly = L.polygon(coords,{weight:2,color:'#059669'}).addTo(this.polys);
          }
          if (poly){ try{ bounds.push(...poly.getLatLngs()[0]); }catch(e){} }
          const m = L.circleMarker([it.lat,it.lng],{radius:3,color:'#047857',fillOpacity:1}).addTo(this.markers);
          m.bindPopup(this.popup(it)); bounds.push(m.getLatLng());
        } else {
          let icon;
          if (it.icon){
            icon = L.icon({iconUrl: it.icon, iconSize:[28,28], iconAnchor:[14,14], className:'rounded-full border'});
          } else {
            icon = L.divIcon({className:'', html:'<div style="width:14px;height:14px;border-radius:9999px;background:#0369a1;border:1px solid #0c4a6e"></div>', iconSize:[14,14]});
          }
          const m = L.marker([it.lat,it.lng],{icon}).addTo(this.markers);
          m.bindPopup(this.popup(it)); bounds.push(m.getLatLng());
        }
      }
      if (fit && bounds.length){
        try{
          const g = L.featureGroup(bounds.map(p=>L.marker([p.lat??p[0], p.lng??p[1]])));
          this.map.fitBounds(g.getBounds(), {padding:[20,20], maxZoom:17});
        }catch(e){}
      }
    },

    popup(it){
      return `<div style="min-width:220px">
        <div><strong>${it.code ?? '—'}</strong></div>
        <div>${it.title ?? ''}</div>
        <div style="font-size:12px;opacity:.7">${it.type}</div>
        <div style="margin-top:6px"><a href="${it.url ?? '#'}" class="underline" target="_blank">Ouvrir la fiche</a></div>
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
</script>
@endpush
