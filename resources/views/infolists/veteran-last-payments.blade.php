@php
    $items = collect($getState() ?? []);
@endphp

@if ($items->isEmpty())
    <em>Aucun paiement.</em>
@else
    <table class="min-w-full text-sm">
        <thead>
            <tr class="text-left text-gray-500">
                <th class="py-1 pr-3">Période</th>
                <th class="py-1 pr-3">Montant</th>
                <th class="py-1 pr-3">Statut</th>
                <th class="py-1">Payé le</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($items as $p)
            <tr class="border-t">
                <td class="py-1 pr-3">{{ optional($p->period_month)->format('m/Y') }}</td>
                <td class="py-1 pr-3 font-semibold">
                    {{ number_format((float)$p->amount, 0, ' ', ' ') }} {{ $p->currency ?? 'CDF' }}
                </td>
                <td class="py-1 pr-3">
                    <span class="px-2 py-0.5 rounded text-xs
                        {{ match($p->status){
                            'paid'    => 'bg-green-100 text-green-800',
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'failed'  => 'bg-red-100 text-red-800',
                            default   => 'bg-gray-100 text-gray-800',
                        } }}">
                        {{ $p->status }}
                    </span>
                </td>
                <td class="py-1">{{ optional($p->paid_at)->format('d/m/Y H:i') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
