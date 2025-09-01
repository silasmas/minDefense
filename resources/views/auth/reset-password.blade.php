<!doctype html><html><head><meta charset="utf-8"><title>Réinitialiser le mot de passe</title>
<style>body{font-family:system-ui;margin:40px auto;max-width:520px}input,button{padding:10px;border:1px solid #ccc;border-radius:8px;width:100%}button{background:#16a34a;color:#fff;border:none;margin-top:8px}</style>
</head><body>
<h2>Définir un nouveau mot de passe</h2>
@if ($errors->any()) <div style="background:#fee2e2;padding:10px;border-radius:8px;margin-bottom:8px">
  @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach </div>@endif
<form method="post" action="{{ route('password.update') }}">
  @csrf
  <input type="hidden" name="token" value="{{ $token }}">
  <label>Email</label>
  <input type="email" name="email" required value="{{ old('email', $email) }}">
  <label>Nouveau mot de passe</label>
  <input type="password" name="password" required>
  <label>Confirmer le mot de passe</label>
  <input type="password" name="password_confirmation" required>
  <button type="submit">Réinitialiser</button>
</form>
</body></html>
