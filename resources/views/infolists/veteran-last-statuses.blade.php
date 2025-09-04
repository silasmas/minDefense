@php
    // Récupère la valeur réelle (Collection / array) depuis la closure
    $items = $getState();             // Filament v3 : toujours évaluer la closure
    $items = collect($items ?? []);   // sécurité
@endphp

@if ($items->isEmpty())
    <em>Aucun historique.</em>
@else
    <ul class="space-y-1">
        @foreach ($items as $h)
            <li class="text-sm">
                <span class="text-xs text-gray-500">
                    {{ optional($h->set_at)->format('d/m/Y H:i') }}
                </span>
                —
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                    {{ match($h->status){
                        'recognized' => 'bg-green-100 text-green-800',
                        'draft'      => 'bg-yellow-100 text-yellow-800',
                        'suspended'  => 'bg-red-100 text-red-800',
                        'deceased'   => 'bg-gray-200 text-gray-800',
                        default      => 'bg-gray-100 text-gray-800',
                    } }}">
                    {{ ucfirst($h->status) }}
                </span>

                @if($h->case?->reference)
                    <span class="text-xs text-gray-500"> — Dossier : {{ $h->case->reference }}</span>
                @endif

                @if($h->comment)
                    <div class="text-xs text-gray-600">{{ $h->comment }}</div>
                @endif
            </li>
        @endforeach
    </ul>
@endif
