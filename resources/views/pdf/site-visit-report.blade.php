<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="utf-8">
  <title>Laporan Lawatan Tapak — {{ $report->file_number }}</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
    h1 { font-size: 16px; margin: 0 0 4px; text-align: center; }
    h2 { font-size: 13px; margin: 0 0 12px; text-align: center; font-weight: normal; }
    .meta { font-size: 10px; text-align: center; margin-bottom: 16px; }
    table.info { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    table.info td { border: 1px solid #333; padding: 6px 8px; vertical-align: top; }
    table.info td.label { width: 28%; background: #f3f3f3; font-weight: bold; }
    .section { border: 1px solid #333; margin-bottom: 14px; }
    .section-title { background: #e8e8e8; padding: 6px 8px; font-weight: bold; border-bottom: 1px solid #333; }
    .section-body { padding: 10px 8px; min-height: 60px; white-space: pre-wrap; }
    .sign { width: 100%; margin-top: 8px; }
    .sign td { width: 33%; text-align: center; padding-top: 24px; font-size: 10px; }
    .line { border-top: 1px solid #333; margin: 0 20px 6px; }
  </style>
</head>
<body>
  <div style="text-align:center;margin-bottom:12px;">
    <img src="{{ public_path('assets/img/branding/mbsj-logo.png') }}" alt="MBSJ" style="height:72px;width:auto;">
  </div>
  <h1>LAPORAN LAWATAN TAPAK</h1>
  <h2>JABATAN KEJURUTERAAN<br>MAJLIS BANDARAYA SUBANG JAYA</h2>
  <div class="meta">
    MBSJ.SPB.PT.PPP(KEJ)-01.RK(01) &nbsp;|&nbsp; PINDAAN : 05 &nbsp;|&nbsp; TARIKH KUAT KUASA : 15 MEI 2026
  </div>

  <table class="info">
    <tr>
      <td class="label">RUJUKAN</td>
      <td>{{ $report->siteVisit?->reference ?? 'MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)' }}</td>
      <td class="label">TARIKH</td>
      <td>{{ $report->siteVisit?->visit_date?->format('d/m/Y') ?? '-' }}</td>
    </tr>
    <tr>
      <td class="label">NO. FAIL</td>
      <td colspan="3">{{ $report->file_number ?? $report->report_number }}</td>
    </tr>
    <tr>
      <td class="label">TAJUK KERJA</td>
      <td colspan="3">{{ $report->title }}</td>
    </tr>
    <tr>
      <td class="label">UNIT</td>
      <td>{{ $report->unit->name ?? '-' }}</td>
      <td class="label">LOKASI</td>
      <td>{{ $report->location_name ?? '-' }}</td>
    </tr>
  </table>

  <div class="section">
    <div class="section-title">LAPORAN PJ/PJK</div>
    <div class="section-body">{{ $report->siteVisit?->laporan_pj_pjk ?: '—' }}</div>
    <table class="sign">
      <tr>
        <td>
          <div class="line"></div>
          {{ $report->siteVisit?->ta_signature ?: ($report->siteVisit?->ta?->name ?? 'TA') }}<br>
          {{ $report->siteVisit?->ta_designation ?? 'Pembantu Teknik' }}<br>
          {{ $report->siteVisit?->submitted_at?->format('d/m/Y H:i') }}
        </td>
        <td></td>
        <td></td>
      </tr>
    </table>
  </div>

  <div class="section">
    <div class="section-title">ULASAN PP/PPK/TPKJ</div>
    <div class="section-body">{{ $report->latestEngineerVerification?->remarks ?: '—' }}</div>
    <table class="sign">
      <tr>
        <td></td>
        <td>
          <div class="line"></div>
          {{ $report->latestEngineerVerification?->signature ?: ($report->latestEngineerVerification?->engineer?->name ?? 'Engineer') }}<br>
          {{ $report->latestEngineerVerification?->designation ?? 'Jurutera' }}<br>
          {{ $report->latestEngineerVerification?->verified_at?->format('d/m/Y H:i') }}
        </td>
        <td></td>
      </tr>
    </table>
  </div>

  <div class="section">
    <div class="section-title">KEPUTUSAN PKJ</div>
    <div class="section-body">
      Keputusan: {{ $report->latestDirectorApproval?->decision === 'approved' ? 'DILULUSKAN' : ($report->latestDirectorApproval?->decision === 'rejected' ? 'DITOLAK' : '—') }}

{{ $report->latestDirectorApproval?->remarks ?: '' }}
    </div>
    <table class="sign">
      <tr>
        <td></td>
        <td></td>
        <td>
          <div class="line"></div>
          {{ $report->latestDirectorApproval?->signature ?: ($report->latestDirectorApproval?->director?->name ?? 'Pengarah') }}<br>
          {{ $report->latestDirectorApproval?->designation ?? 'Pengarah Kejuruteraan' }}<br>
          {{ $report->latestDirectorApproval?->approved_at?->format('d/m/Y H:i') }}
        </td>
      </tr>
    </table>
  </div>
</body>
</html>
