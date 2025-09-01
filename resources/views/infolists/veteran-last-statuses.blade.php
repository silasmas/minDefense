@php $items = $state ?? collect(); @endphp
<ul style="margin:0;padding-left:16px">
@forelse($items as $it)
  <li><strong>{{ $it->status }}</strong>
      — {{ optional($it->set_at)->format('d/m/Y H:i') }}
      <em>(Dossier {{ $it->case->case_number ?? '—' }})</em>
  </li>
@empty
  <li><em>Aucun historique.</em></li>
@endforelse
</ul>
