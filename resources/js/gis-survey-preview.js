import {
  classifySurveyFile,
  extractReportMetadata,
  parse3dCsv,
  parse2dTxt,
  transformToWgs84,
} from './survey-parse-utils';

const pdfPreviews = new Map();
const processedKeys = new Set();

function fileKey(file) {
  return `${file.name}:${file.size}:${file.lastModified}`;
}

function showPdfPanel(file, blobUrl) {
  let panel = document.getElementById('survey-pdf-panel');
  if (!panel) {
    panel = document.createElement('div');
    panel.id = 'survey-pdf-panel';
    panel.className = 'mt-3';
    panel.innerHTML = `
      <label class="form-label small text-muted">Pratonton PDF (1D Laporan)</label>
      <div id="survey-pdf-list"></div>
    `;
    const mapCard = document.querySelector('[data-survey-map-card]');
    if (mapCard) mapCard.appendChild(panel);
  }
  const list = document.getElementById('survey-pdf-list');
  if (!list) return;

  const item = document.createElement('div');
  item.className = 'mb-2';
  item.dataset.file = file.name;
  item.innerHTML = `
    <div class="d-flex justify-content-between align-items-center mb-1">
      <span class="small">${file.name}</span>
      <button type="button" class="btn btn-sm btn-icon btn-text-danger survey-pdf-remove"><i class="ti tabler-x"></i></button>
    </div>
    <iframe src="${blobUrl}" style="width:100%;height:320px;border:1px solid var(--bs-border-color);border-radius:8px;"></iframe>
  `;
  item.querySelector('.survey-pdf-remove')?.addEventListener('click', () => {
    URL.revokeObjectURL(blobUrl);
    pdfPreviews.delete(file.name);
    item.remove();
    if (!list.children.length) panel.classList.add('d-none');
    window.gisMapPicker?.refreshMapLayout?.();
  });
  list.appendChild(item);
  panel.classList.remove('d-none');
}

function updateDayControl(parsed) {
  let wrap = document.getElementById('survey-day-control');
  if (!wrap) {
    wrap = document.createElement('div');
    wrap.id = 'survey-day-control';
    wrap.className = 'mt-2';
    wrap.innerHTML = `
      <label class="form-label small text-muted mb-1">Hari (2D)</label>
      <select id="survey-day-select" class="form-select form-select-sm"></select>
    `;
    const mapCol = document.querySelector('[data-survey-map-card]');
    if (mapCol) mapCol.appendChild(wrap);
  }

  const sel = document.getElementById('survey-day-select');
  if (!sel || parsed.type !== '2d') {
    if (wrap) wrap.classList.add('d-none');
    return;
  }

  wrap.classList.remove('d-none');
  sel.innerHTML = parsed.days.map((d) => `<option value="${d}">Hari ${d}</option>`).join('');
  sel.value = parsed.days[parsed.days.length - 1];
  sel.onchange = () => window.gisMapPicker?.setSurvey2dDay(sel.value);
  window._lastSurvey2dParsed = parsed;
  window.gisMapPicker?.setSurvey2dDay(sel.value);
}

function pushMetadataToForm(meta) {
  if (!meta || (!meta.title && !meta.description && !meta.location_name)) return;
  const wire = window.surveyReportLivewire;
  if (!wire) return;
  if (typeof wire.applySurveyMetadata === 'function') {
    wire.applySurveyMetadata(
      meta.title || null,
      meta.description || null,
      meta.location_name || null,
      meta.category_slug || null,
    );
  } else {
    wire.call(
      'applySurveyMetadata',
      meta.title || null,
      meta.description || null,
      meta.location_name || null,
      meta.category_slug || null,
    );
  }
}

function showMetadataHint() {
  let el = document.getElementById('survey-metadata-hint');
  if (!el) {
    const formCol = document.querySelector('[data-survey-form-fields]');
    if (!formCol) return;
    el = document.createElement('div');
    el.id = 'survey-metadata-hint';
    el.className = 'alert alert-info py-2 small mb-3';
    el.textContent =
      'Kategori (CN/SH), tajuk, keterangan dan nama lokasi diisi automatik daripada fail survei. Anda boleh sunting sebelum hantar.';
    formCol.prepend(el);
  }
  el.classList.remove('d-none');
}

function updateFileBadge(fileName, type, error = null) {
  const listItem = [...document.querySelectorAll('.list-group-item')].find((li) =>
    li.textContent.includes(fileName),
  );
  if (!listItem) return;

  let badge = listItem.querySelector('.survey-type-badge');
  if (!badge) {
    badge = document.createElement('span');
    badge.className = 'survey-type-badge badge ms-2';
    listItem.querySelector('span')?.appendChild(badge);
  }
  const labels = { survey_3d: '3D', survey_2d: '2D', survey_1d: '1D', other: 'Fail' };
  const classes = {
    survey_3d: 'bg-label-info',
    survey_2d: 'bg-label-warning',
    survey_1d: 'bg-label-danger',
    other: 'bg-label-secondary',
  };
  badge.textContent = error ? 'Ralat' : labels[type] || 'Fail';
  badge.className = `survey-type-badge badge ms-2 ${error ? 'bg-label-danger' : classes[type] || 'bg-label-secondary'}`;

  let errEl = listItem.querySelector('.survey-parse-error');
  if (error) {
    if (!errEl) {
      errEl = document.createElement('small');
      errEl.className = 'survey-parse-error text-danger d-block';
      listItem.appendChild(errEl);
    }
    errEl.textContent = error;
  } else if (errEl) {
    errEl.remove();
  }
}

async function handleSurveyFile(file, retries = 0) {
  const key = fileKey(file);
  if (processedKeys.has(key)) return;

  if (!window.gisMapPicker?.getMap?.()) {
    if (retries < 25) {
      setTimeout(() => handleSurveyFile(file, retries + 1), 120);
    }
    return;
  }

  const ext = file.name.split('.').pop()?.toLowerCase();
  const isPdf = ext === 'pdf' || file.type === 'application/pdf';

  let type;
  let text = null;

  if (isPdf) {
    type = 'survey_1d';
  } else {
    text = await file.text();
    type = classifySurveyFile(file.name, text);
  }

  if (type === 'other') return;

  processedKeys.add(key);

  if (type === 'survey_1d') {
    const pdfSample = await file.slice(0, 65536).text();
    const url = URL.createObjectURL(file);
    pdfPreviews.set(file.name, url);
    showPdfPanel(file, url);
    updateFileBadge(file.name, type);
    window.gisMapPicker?.refreshMapLayout?.();
    setTimeout(() => {
      pushMetadataToForm(extractReportMetadata(file.name, pdfSample));
      showMetadataHint();
    }, 0);
    return;
  }

  try {
    if (!text) text = await file.text();
    const anchor = window.gisMapPicker?.ensureAnchor?.();
    if (!anchor) throw new Error('Peta belum sedia. Muat semula halaman.');

    let parsed = type === 'survey_3d' ? parse3dCsv(text) : parse2dTxt(text);
    parsed = transformToWgs84(parsed, anchor.lat, anchor.lng);
    window.gisMapPicker?.addSurveyLayer(parsed, file.name);
    if (type === 'survey_2d') updateDayControl(parsed);
    updateFileBadge(file.name, type);
    window.gisMapPicker?.refreshMapLayout?.();
    setTimeout(() => {
      pushMetadataToForm(extractReportMetadata(file.name, text));
      showMetadataHint();
    }, 0);
  } catch (e) {
    processedKeys.delete(key);
    updateFileBadge(file.name, type, e.message);
  }
}

export function bindSurveyFilePreview() {
  document.querySelectorAll('[data-survey-files]').forEach((input) => {
    if (input.dataset.surveyBound) return;
    input.dataset.surveyBound = '1';
    input.addEventListener('change', () => {
      const picked = Array.from(input.files || []);
      picked.forEach((f) => handleSurveyFile(f));
    });
  });
}
