<!doctype html><html><head><meta charset="utf-8"><title>Mot de passe oublié</title>
<style>body{font-family:system-ui;margin:40px auto;max-width:520px}input,button{padding:10px;border:1px solid #ccc;border-radius:8px;width:100%}button{background:#0ea5e9;color:#fff;border:none;margin-top:8px}</style>
</head><body>
<h2>Demander un lien de réinitialisation</h2>
@if(session('status')) <div style="background:#dcfce7;padding:10px;border-radius:8px">{{ session('status') }}</div>@endif
<form method="post" action="{{ route('password.email') }}">
  @csrf
  <label>Email</label>
  <input type="email" name="email" required value="{{ old('email') }}">
  @error('email')<div style="color:#b91c1c">{{ $message }}</div>@enderror
  <button type="submit">Envoyer le lien</button>
</form>
</body></html>
