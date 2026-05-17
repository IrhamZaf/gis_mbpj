const METRES_PER_DEGREE_LAT = 111320;

export function detectFromContent(content) {
  if (!content || !content.trim()) return null;

  const sample = content.trimStart().slice(0, 4096);
  if (sample.startsWith('%PDF')) return 'survey_1d';

  const header = parseCsvLine(sample.split(/\r?\n/)[0] || '');
  if (!header.length) return null;

  const upper = header.map((h) => h.toUpperCase());
  const has = (names) => names.some((n) => upper.includes(n));
  const contains = (needle) => header.some((h) => h.toUpperCase().includes(needle));

  if (has(['DAY']) && has(['POINT']) && has(['XB']) && has(['YB'])) {
    return 'survey_2d';
  }

  if (has(['XB']) && has(['YB']) && has(['ZB']) && !has(['DAY'])) {
    return 'survey_3d';
  }

  if (has(['XB']) && has(['YB']) && contains('DXY')) {
    return 'survey_2d';
  }

  return null;
}

function dimensionLabel(documentType) {
  if (documentType === 'survey_3d') return '3D';
  if (documentType === 'survey_2d') return '2D';
  if (documentType === 'survey_1d') return '1D';
  return null;
}

function isProjectCode(code) {
  return /^(CN|SH)\d*/i.test(code);
}

function isPlaceName(code) {
  return /^ATC\d+[A-Z]?$/i.test(code);
}

function defaultDescription(place, dim, source, projectCode = null) {
  const proj = projectCode ? ` (projek ${projectCode})` : '';
  if (dim === '3D') return `Data titik survei 3D (Xb, Yb, Zb) untuk lokasi ${place}${proj}. Sumber: ${source}.`;
  if (dim === '2D') return `Data pemantauan sesaran 2D mengikut hari (DXY/DZ) untuk lokasi ${place}${proj}. Sumber: ${source}.`;
  if (dim === '1D') return `Laporan graf dan analisis 1D untuk lokasi ${place}${proj}. Sumber: ${source}.`;
  return `Dokumen survei untuk lokasi ${place}${proj}. Sumber: ${source}.`;
}

function defaultTitle(place, dim) {
  const label = dim ? `Survei ${dim} ILAPXYZ` : 'Survei ILAPXYZ';
  return `${label} — ${place}`;
}

function isDataHeaderLine(line) {
  const upper = line.toUpperCase();
  return (upper.includes('XB') && upper.includes('YB')) || (upper.includes('DAY') && upper.includes('POINT'));
}

export function categorySlugFromSiteCode(siteCode) {
  if (!siteCode) return null;
  const upper = siteCode.toUpperCase();
  if (upper.startsWith('CN')) return 'cerun-tanah-runtuh';
  if (upper.startsWith('SH')) return 'sinkhole';
  return null;
}

export function guessSiteCodeFromFilename(filename) {
  const base = filename.replace(/\.[^.]+$/, '');
  const cn = base.match(/\b(CN\d*[A-Z0-9]*)\b/i);
  if (cn) return cn[1].toUpperCase();
  const sh = base.match(/\b(SH\d*[A-Z0-9]*)\b/i);
  if (sh) return sh[1].toUpperCase();
  return null;
}

export function guessPlaceFromFilename(filename) {
  const base = filename.replace(/\.[^.]+$/, '');
  const atc = base.match(/\b(ATC\d+[A-Z]?)\b/i);
  if (atc) return atc[1].toUpperCase();
  return null;
}

function extractFromFilename(filename, documentType) {
  const base = filename.replace(/\.[^.]+$/, '');
  const meta = { site_code: null, place_name: null, survey_dimension: dimensionLabel(documentType) };
  const mbpj = base.match(/^([A-Z0-9]+)[\s_]+([A-Z0-9]+)[\s_]+[123]DILAPXYZ/i);
  if (mbpj) {
    const first = mbpj[1].toUpperCase();
    const second = mbpj[2].toUpperCase();
    if (isProjectCode(first)) {
      meta.site_code = first;
      meta.place_name = second;
    } else if (isPlaceName(first)) {
      meta.place_name = first;
      if (isProjectCode(second)) meta.site_code = second;
    }
  } else {
    const single = base.match(/^([A-Z0-9]+)[\s_]+[123]DILAPXYZ/i);
    if (single) {
      const token = single[1].toUpperCase();
      if (isProjectCode(token)) meta.site_code = token;
      else if (isPlaceName(token)) meta.place_name = token;
    }
  }

  const dim = meta.survey_dimension;
  if (meta.place_name) {
    meta.location_name = meta.place_name;
    meta.title = defaultTitle(meta.place_name, dim);
    meta.description = defaultDescription(meta.place_name, dim, filename, meta.site_code);
  } else if (meta.site_code && dim) {
    meta.title = defaultTitle(meta.site_code, dim);
    meta.description = defaultDescription(meta.site_code, dim, filename, null);
  } else if (dim) {
    const clean = base.replace(/[_-]+/g, ' ').replace(/\s+/g, ' ').trim();
    meta.title = `Laporan Survei ${dim} — ${clean}`;
    meta.description = `Data survei ${dim} daripada fail ${filename}.`;
  }

  if (!meta.site_code) meta.site_code = guessSiteCodeFromFilename(filename);
  if (!meta.place_name) {
    meta.place_name = guessPlaceFromFilename(filename);
    if (meta.place_name && !meta.location_name) meta.location_name = meta.place_name;
  }
  meta.category_slug = categorySlugFromSiteCode(meta.site_code);

  return meta;
}

function applyLineMetadata(meta, line) {
  let m = line.match(/^(?:#\s*)?(tajuk\s*laporan|title|project|report)\s*[:=]\s*(.+)$/iu);
  if (m) { meta.title = m[2].trim(); return; }
  m = line.match(/^(?:#\s*)?(keterangan|description|remarks|catatan)\s*[:=]\s*(.+)$/iu);
  if (m) { meta.description = m[2].trim(); return; }
  m = line.match(/^(?:#\s*)?(nama\s*lokasi|location|lokasi|site)\s*[:=]\s*(.+)$/iu);
  if (m) { meta.location_name = m[2].trim(); return; }
  const cols = parseCsvLine(line);
  if (cols.length === 2) {
    const key = cols[0].toLowerCase().trim();
    const val = cols[1].trim();
    const map = {
      tajuk: 'title', title: 'title', project: 'title',
      keterangan: 'description', description: 'description', remarks: 'description',
      lokasi: 'location_name', location: 'location_name', 'nama lokasi': 'location_name', site: 'location_name',
    };
    if (map[key] && val) meta[map[key]] = val;
  }
}

function extractFromContent(content, documentType) {
  const meta = {};
  if (content.trimStart().startsWith('%PDF')) {
    const text = (content.match(/[\x20-\x7E]{4,}/g) || []).join('\n').slice(0, 131072);
    const patterns = [
      ['title', /(?:tajuk\s*laporan|report\s*title|project)\s*[:=]\s*([^\r\n]{5,200})/i],
      ['description', /(?:keterangan|description|remarks)\s*[:=]\s*([^\r\n]{10,500})/i],
      ['location_name', /(?:nama\s*lokasi|location|lokasi|site)\s*[:=]\s*([^\r\n]{3,200})/i],
    ];
    patterns.forEach(([key, re]) => {
      const m = text.match(re);
      if (m) meta[key] = m[1].trim();
    });
    return meta;
  }

  const lines = content.split(/\r?\n/);
  for (const line of lines) {
    const trimmed = line.trim();
    if (!trimmed) continue;
    if (isDataHeaderLine(trimmed)) break;
    applyLineMetadata(meta, trimmed);
  }
  return meta;
}

export function extractReportMetadata(filename, content = null) {
  const documentType = classifySurveyFile(filename, content);
  const fromFilename = extractFromFilename(filename, documentType);
  const fromContent = content ? extractFromContent(content, documentType) : {};
  const siteCode = fromContent.site_code || fromFilename.site_code || guessSiteCodeFromFilename(filename);
  const placeName = fromContent.place_name || fromFilename.place_name || guessPlaceFromFilename(filename);
  return {
    title: fromContent.title || fromFilename.title || null,
    description: fromContent.description || fromFilename.description || null,
    location_name: fromContent.location_name || fromFilename.location_name || placeName || null,
    site_code: siteCode,
    place_name: placeName,
    survey_dimension: fromFilename.survey_dimension || dimensionLabel(documentType),
    category_slug: categorySlugFromSiteCode(siteCode),
  };
}

export function classifySurveyFile(filename, content = null) {
  if (content) {
    const detected = detectFromContent(content);
    if (detected) return detected;
  }

  const name = filename.toUpperCase();
  const ext = filename.split('.').pop()?.toLowerCase() || '';

  if (name.includes('3DILAPXYZ')) return 'survey_3d';
  if (name.includes('2DILAPXYZ')) return 'survey_2d';
  if (name.includes('1DILAPXYZ')) return 'survey_1d';

  if (ext === 'pdf') return 'survey_1d';
  if (ext === 'csv') return 'survey_3d';
  if (ext === 'txt') return 'survey_2d';

  return 'other';
}

export function parse3dCsv(content) {
  const lines = content.trim().split(/\r?\n/);
  if (lines.length < 2) throw new Error('Fail CSV kosong.');
  const header = parseCsvLine(lines.shift());
  const xi = colIndex(header, ['Xb', 'XB', 'X']);
  const yi = colIndex(header, ['Yb', 'YB', 'Y']);
  const zi = colIndex(header, ['Zb', 'ZB', 'Z']);
  if (xi === null || yi === null || zi === null) throw new Error('Lajur Xb, Yb, Zb diperlukan.');

  const points = [];
  let i = 0;
  lines.forEach((line) => {
    if (!line.trim()) return;
    const cols = parseCsvLine(line);
    i += 1;
    points.push({
      id: `P${i}`,
      xb: parseFloat(cols[xi]),
      yb: parseFloat(cols[yi]),
      zb: parseFloat(cols[zi]),
    });
  });
  if (!points.length) throw new Error('Tiada titik dalam CSV.');
  return { type: '3d', points };
}

export function parse2dTxt(content) {
  const lines = content.trim().split(/\r?\n/);
  if (lines.length < 2) throw new Error('Fail TXT kosong.');
  const header = parseCsvLine(lines.shift());
  const map = {
    day: colIndex(header, ['DAY']),
    point: colIndex(header, ['POINT']),
    xb: colIndex(header, ['Xb', 'XB']),
    yb: colIndex(header, ['Yb', 'YB']),
    zb: colIndex(header, ['Zb', 'ZB']),
    dxy_mm: colIndex(header, ['DXY(mm)', 'DXY']),
    dz_mm: colIndex(header, ['DZ(mm)', 'DZ']),
  };
  if (map.day === null || map.point === null || map.xb === null || map.yb === null) {
    throw new Error('Lajur DAY, POINT, Xb, Yb diperlukan.');
  }

  const records = [];
  const daySet = new Set();
  lines.forEach((line) => {
    if (!line.trim()) return;
    const cols = parseCsvLine(line);
    const day = parseInt(cols[map.day], 10);
    daySet.add(day);
    records.push({
      day,
      point: cols[map.point],
      xb: parseFloat(cols[map.xb]),
      yb: parseFloat(cols[map.yb]),
      zb: map.zb !== null ? parseFloat(cols[map.zb]) : null,
      dxy_mm: map.dxy_mm !== null ? parseFloat(cols[map.dxy_mm]) : 0,
      dz_mm: map.dz_mm !== null ? parseFloat(cols[map.dz_mm]) : 0,
    });
  });
  if (!records.length) throw new Error('Tiada rekod dalam TXT.');
  return { type: '2d', days: [...daySet].sort((a, b) => a - b), records };
}

function parseCsvLine(line) {
  const result = [];
  let cur = '';
  let inQ = false;
  for (let i = 0; i < line.length; i++) {
    const c = line[i];
    if (c === '"') {
      inQ = !inQ;
    } else if (c === ',' && !inQ) {
      result.push(cur.trim());
      cur = '';
    } else {
      cur += c;
    }
  }
  result.push(cur.trim());
  return result;
}

function colIndex(header, names) {
  for (const n of names) {
    const i = header.indexOf(n);
    if (i >= 0) return i;
  }
  return null;
}

export function transformToWgs84(parsed, anchorLat, anchorLng) {
  if (!anchorLat || !anchorLng) return parsed;

  if (parsed.type === '3d') {
    const geo = georefPoints(parsed.points, anchorLat, anchorLng);
    return { ...parsed, centroid_x: geo.cx, centroid_y: geo.cy, points: geo.points };
  }

  if (parsed.type === '2d') {
    const byPoint = {};
    parsed.records.forEach((r) => {
      byPoint[r.point] = { xb: r.xb, yb: r.yb, point: r.point };
    });
    const geo = georefPoints(Object.values(byPoint), anchorLat, anchorLng);
    const map = {};
    geo.points.forEach((p) => {
      map[p.point] = { lat: p.lat, lng: p.lng };
    });
    return {
      ...parsed,
      centroid_x: geo.cx,
      centroid_y: geo.cy,
      records: parsed.records.map((r) => ({
        ...r,
        lat: map[r.point]?.lat,
        lng: map[r.point]?.lng,
      })),
    };
  }

  return parsed;
}

function georefPoints(points, anchorLat, anchorLng) {
  let sx = 0;
  let sy = 0;
  points.forEach((p) => {
    sx += p.xb;
    sy += p.yb;
  });
  const cx = sx / points.length;
  const cy = sy / points.length;
  const lngScale = METRES_PER_DEGREE_LAT * Math.cos((anchorLat * Math.PI) / 180);

  const out = points.map((p) => ({
    ...p,
    lat: anchorLat + (p.yb - cy) / METRES_PER_DEGREE_LAT,
    lng: anchorLng + (p.xb - cx) / lngScale,
  }));

  return { cx, cy, points: out };
}

export function displacementColor(dxyMm) {
  const v = Math.min(Math.max(dxyMm, 0), 60);
  const t = v / 60;
  const r = Math.round(49 + t * (215 - 49));
  const g = Math.round(163 - t * 163);
  const b = Math.round(84 - t * 84);
  return `rgb(${r},${g},${b})`;
}
