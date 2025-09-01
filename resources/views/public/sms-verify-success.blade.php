<!doctype html><html><head><meta charset="utf-8"><style>
 body{font-family:system-ui, sans-serif;max-width:680px;margin:24px auto;padding:0 16px}
 .ok{border:1px solid #86efac;background:#dcfce7;color:#14532d;padding:12px;border-radius:8px}
</style></head><body>
<h2>Merci</h2>
<div class="ok">Votre vérification est enregistrée. Statut : <strong>{{ $map[$v->status] ?? $v->status }}</strong>.</div>
</body></html>
