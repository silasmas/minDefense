<div class="card card-back">
  <div class="qr-big">{!! $qrSvg !!}</div>
  <div class="note">
    Carte n° <strong>{{ $v->card_number ?? '—' }}</strong> • Valide jusqu’au <strong>{{ $expires_at->format('d/m/Y') }}</strong>
  </div>
</div>
