<!doctype html><html><head><meta charset="utf-8"><style>
 body{font-family:system-ui, sans-serif;max-width:680px;margin:24px auto;padding:0 16px}
 .warn{border:1px solid #fca5a5;background:#fee2e2;color:#7f1d1d;padding:12px;border-radius:8px}
</style></head><body>
<h2>Merci</h2>
<div class="warn">
  Votre retour a été enregistré. Un agent vérifiera vos informations.
  @if(!empty($reason))<div style="margin-top:6px"><strong>Votre remarque :</strong> {{ $reason }}</div>@endif
</div>
</body></html>
