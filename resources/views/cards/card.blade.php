@php
  // Traductions statut (fallback)
  $map = $statusMap ?? [
    'draft'=>'Brouillon','recognized'=>'Reconnu','suspended'=>'Suspendu','deceased'=>'Décédé'
  ];
@endphp

<div class="card">
  <div class="brand">
    <div class="brand-left"></div>
    <div class="brand-title">CARTE D'ANCIEN COMBATTANT</div>
    <div class="brand-right"></div>
  </div>
  {{-- TOPLINE : date à gauche, N° souligné à droite --}}
  <div class="topline" style="margin-bottom: 20px">
    {{-- <div class="expires">Valide jusqu’au : <strong>{{ $expires_at->format('d/m/Y') }}</strong></div>
    <div class="cardno">N° : <span class="num">{{ $v->card_number ?? '—' }}</span></div> --}}
  </div>


  <table class="tbl" >
    <tr>
      <td class="left">
        @if(!empty($photoData))
          <img class="photo" src="{{ $photoData }}" alt="photo"  style="margin-left: 10px">
        @else
          <div class="photo placeholder"></div>
        @endif
      </td>
      <td class="right">
        <div class="row"><div class="lbl">Nom & Prénom</div>
          <div class="val">{{ $v->lastname }} {{ $v->firstname }}</div></div>

        <div class="row"><div class="lbl">Matricule</div>
          <div class="val muted">{{ $v->service_number }}</div></div>

        <div class="row"><div class="lbl">Branche / Grade</div>
          <div class="val muted">{{ $v->branch }}{{ $v->rank ? ' • '.$v->rank : '' }}</div></div>

        <div class="row"><div class="lbl">Statut</div>
          <div class="val">{{ $map[$v->status] ?? $v->status }}</div></div>
      </td>
    </tr>
  </table>

  <div class="footer">
    <div class="exp"></div>
    {{-- <div class="qr">{!! $qrSvg !!}</div> --}}
  </div>
</div>
