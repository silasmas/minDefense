@php $items = collect($getState() ?? []); @endphp
@if($items->isEmpty())
    <em>Aucun paiement programmé.</em>
@else
    <ul class="space-y-1">
        @foreach($items as $p)
            <li class="text-sm">
                {{ $p->period_month?->format('m/Y') ?? ($p->period_start?->format('d/m').'–'.$p->period_end?->format('d/m/Y')) }}
                — <strong>{{ number_format((float)$p->amount,0,' ',' ') }} {{ $p->currency }}</strong>
                — prévu le {{ optional($p->paid_at)->format('d/m/Y H:i') ?: '—' }}
            </li>
        @endforeach
    </ul>
@endif
