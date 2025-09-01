@php $pp = $state ?? collect(); @endphp
<ul style="margin:0;padding-left:16px">
@forelse($pp as $p)
  <li>{{ optional($p->paid_at)->format('d/m/Y') ?? '—' }} —
      <strong>{{ number_format((float)$p->amount, 0, ' ', ' ') }} {{ $p->currency }}</strong>
      ({{ $p->payment_type }})
  </li>
@empty
  <li><em>Aucun paiement.</em></li>
@endforelse
</ul>
