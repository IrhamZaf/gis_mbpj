<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
    h1 { font-size: 16px; color: #1B4F72; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
    th { background: #f0f4f8; }
  </style>
</head>
<body>
  <h1>Laporan insiden — {{ $incident->incident_number }}</h1>
  <p><strong>Majlis Bandaraya Petaling Jaya (MBPJ)</strong><br>Sistem Web GIS</p>
  <table>
    <tr><th>No. rujukan</th><td>{{ $incident->incident_number }}</td></tr>
    <tr><th>Kategori</th><td>{{ $incident->category }}</td></tr>
    <tr><th>Tarikh</th><td>{{ $incident->date_reported->format('d/m/Y') }}</td></tr>
    <tr><th>Koordinat</th><td>{{ $incident->latitude }}, {{ $incident->longitude }}</td></tr>
    <tr><th>Alamat</th><td>{{ $incident->address }}</td></tr>
    <tr><th>Risiko</th><td>{{ $incident->risk_level }}</td></tr>
    <tr><th>Status</th><td>{{ $incident->status }}</td></tr>
    <tr><th>Pelapor</th><td>{{ $incident->reporter?->name }}</td></tr>
    <tr><th>Jurutera</th><td>{{ $incident->engineer?->name ?? '—' }}</td></tr>
    <tr><th>Keterangan</th><td>{{ $incident->description }}</td></tr>
  </table>
  @if($incident->reviews->isNotEmpty())
  <h2 style="margin-top: 18px; font-size: 13px;">Semakan jurutera</h2>
  @foreach ($incident->reviews as $rev)
  <p><strong>{{ $rev->created_at->format('d/m/Y') }}</strong> — {{ $rev->engineer?->name }}<br>
    {{ $rev->risk_assessment }}<br>
    <em>Cadangan:</em> {{ $rev->recommendation }}<br>
    <em>Diluluskan:</em> {{ $rev->is_approved ? 'Ya' : 'Tidak' }}</p>
  @endforeach
  @endif
</body>
</html>
