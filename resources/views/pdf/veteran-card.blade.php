<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
@page { margin: 0; size: 85.6mm 54mm; }
body { margin:0; font-family: DejaVu Sans, sans-serif; }

.card { width:100%; height:100%; box-sizing:border-box; padding:3mm 4mm; border:0.35mm solid #111; border-radius:1.2mm; }
.brand { display:flex; align-items:center; gap:3mm; margin-bottom:1.5mm; }
.brand-left,.brand-right { height:4mm; flex:1 1 0; background:#0B3C5D; border-radius:0.7mm; }
.brand-title { white-space:nowrap; font-weight:800; font-size:10pt; letter-spacing:.15pt; }

/* ⬇️ Date dans la carte + N° souligné */
.topline { display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:1.5mm; }
.expires { font-size:7.2pt; color:#222; }
.cardno  { font-size:8pt; color:#222; }
.cardno .num { text-decoration: underline; font-weight:700; }

.tbl { width:100%; border-collapse:collapse; }
.left { width:28mm; vertical-align:top; }
.right { vertical-align:top; padding-left:3mm; }
.photo { width:26mm; height:34mm; object-fit:cover; border:.25mm solid #777; border-radius:1mm; background:#f1f1f1; }
.placeholder { background:linear-gradient(135deg,#eee,#ddd); }
.row { margin-bottom:1.2mm; }
.lbl { font-size:7.2pt; color:#666; }
.val { font-size:9pt; font-weight:700; }
.muted { color:#444; font-weight:600; }

.footer { margin-top:1.5mm; display:flex; align-items:flex-end; justify-content:space-between; }
.qr svg { width:17mm; height:17mm; display:block; }

</style>
</head>
<body>
  @include('cards.card')
</body>
</html>
