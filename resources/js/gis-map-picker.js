import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet-draw';
import 'leaflet-draw/dist/leaflet.draw.css';

import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

import { displacementColor } from './survey-parse-utils';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
});

const MBPJ = [3.1073, 101.6067];
const NOMINATIM_URL = 'https://nominatim.openstreetmap.org/search';
const PJ_VIEWBOX = '101.45,3.02,101.75,3.18'; // west,south,east,north

let map = null;
let siteMarker = null;
let drawnItems = null;
let surveyLayerGroup = null;
let onCoordinatesChange = null;
let onGisDataChange = null;
let currentSurvey2dDay = null;
let searchAbort = null;
let searchDebounce = null;

const satelliteLayer = L.tileLayer(
  'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
  { attribution: 'Tiles &copy; Esri', maxZoom: 19 }
);
const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; OpenStreetMap',
  maxZoom: 19,
});

function getAnchor() {
  const latEl = document.getElementById('report-anchor-lat');
  const lngEl = document.getElementById('report-anchor-lng');
  const lat = latEl ? parseFloat(latEl.value) : NaN;
  const lng = lngEl ? parseFloat(lngEl.value) : NaN;
  if (Number.isFinite(lat) && Number.isFinite(lng)) return { lat, lng };
  return null;
}

function updateAnchorInputs(lat, lng) {
  const latEl = document.getElementById('report-anchor-lat');
  const lngEl = document.getElementById('report-anchor-lng');
  if (latEl) {
    latEl.value = lat;
    latEl.dispatchEvent(new Event('input', { bubbles: true }));
  }
  if (lngEl) {
    lngEl.value = lng;
    lngEl.dispatchEvent(new Event('input', { bubbles: true }));
  }
}

function setAnchor(lat, lng, label = null) {
  if (!map) return;
  setSiteMarker(lat, lng);
  map.setView([lat, lng], Math.max(map.getZoom(), 16));
  updateAnchorInputs(lat, lng);
  if (onCoordinatesChange) onCoordinatesChange(lat, lng, label);
  const searchInput = document.getElementById('gis-location-search');
  if (searchInput && label) searchInput.value = label;
  hideSearchResults();
}

function clearSurveyLayers() {
  if (surveyLayerGroup) {
    surveyLayerGroup.clearLayers();
  }
}

function renderSurveyData(parsed, label = '', append = false) {
  if (!map || !surveyLayerGroup || !parsed) return;

  if (!append) {
    clearSurveyLayers();
  }

  if (parsed.type === '3d' && parsed.points?.length) {
    parsed.points.forEach((p) => {
      if (p.lat == null || p.lng == null) return;
      const m = L.circleMarker([p.lat, p.lng], {
        radius: 4,
        color: '#fff',
        weight: 1,
        fillColor: '#3498db',
        fillOpacity: 0.85,
      });
      m.bindPopup(
        `<strong>${p.id}</strong><br>Xb: ${p.xb}<br>Yb: ${p.yb}<br>Zb: ${p.zb} m${label ? `<br><small>${label}</small>` : ''}`
      );
      surveyLayerGroup.addLayer(m);
    });
    const bounds = L.latLngBounds(parsed.points.filter((p) => p.lat != null).map((p) => [p.lat, p.lng]));
    if (bounds.isValid()) map.fitBounds(bounds, { padding: [30, 30], maxZoom: 18 });
    return;
  }

  if (parsed.type === '2d' && parsed.records?.length) {
    const day = currentSurvey2dDay ?? parsed.days?.[parsed.days.length - 1] ?? 1;
    const dayRecords = parsed.records.filter((r) => r.day === day);
    dayRecords.forEach((r) => {
      if (r.lat == null || r.lng == null) return;
      const color = displacementColor(r.dxy_mm ?? 0);
      const m = L.circleMarker([r.lat, r.lng], {
        radius: 5,
        color: '#fff',
        weight: 1,
        fillColor: color,
        fillOpacity: 0.9,
      });
      m.bindPopup(
        `<strong>${r.point}</strong> (Hari ${r.day})<br>DXY: ${r.dxy_mm} mm<br>DZ: ${r.dz_mm} mm${label ? `<br><small>${label}</small>` : ''}`
      );
      surveyLayerGroup.addLayer(m);
    });
    const bounds = L.latLngBounds(dayRecords.filter((r) => r.lat != null).map((r) => [r.lat, r.lng]));
    if (bounds.isValid()) map.fitBounds(bounds, { padding: [30, 30], maxZoom: 18 });
  }
}

function setSiteMarker(lat, lng) {
  if (!map) return;
  if (siteMarker) map.removeLayer(siteMarker);
  siteMarker = L.marker([lat, lng]).addTo(map);
}

function injectLocationSearchStyles() {
  if (document.getElementById('gis-location-search-styles')) return;
  const style = document.createElement('style');
  style.id = 'gis-location-search-styles';
  style.textContent = `
    .gis-location-search-wrap { position: relative; z-index: 1050; }
    .gis-location-search-wrap.gis-search-active { z-index: 1060; }
    .gis-location-search-wrap.gis-search-active ~ #survey-anchor-warning { display: none !important; }
    #gis-location-results.gis-location-results {
      top: calc(100% + 4px);
      left: 0;
      right: 0;
      z-index: 1070;
      max-height: 240px;
      overflow-y: auto;
      overflow-x: hidden;
      background-color: #fff !important;
      border: 1px solid #d9dee3;
      border-radius: 0.375rem;
      box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.2);
    }
    #gis-location-results .list-group-item {
      background-color: #fff !important;
      color: #566a7f;
      border-color: #e7e9ed;
    }
    #gis-location-results .list-group-item-action:hover,
    #gis-location-results .list-group-item-action:focus {
      background-color: #f5f5f9 !important;
      color: #566a7f;
    }
    [data-survey-map-card] { position: relative; }
  `;
  document.head.appendChild(style);
}

function setSearchDropdownOpen(open) {
  document.querySelector('.gis-location-search-wrap')?.classList.toggle('gis-search-active', open);
}

function hideSearchResults() {
  const list = document.getElementById('gis-location-results');
  if (list) {
    list.classList.add('d-none');
    list.innerHTML = '';
  }
  setSearchDropdownOpen(false);
}

function showSearchResults(items, onSelect) {
  const list = document.getElementById('gis-location-results');
  if (!list) return;

  setSearchDropdownOpen(true);

  if (!items.length) {
    list.innerHTML = '<li class="list-group-item small text-muted bg-white">Tiada hasil dijumpai.</li>';
    list.classList.remove('d-none');
    return;
  }

  list.innerHTML = items
    .map(
      (item, i) =>
        `<li class="list-group-item list-group-item-action small py-2 gis-search-result" data-index="${i}" role="button">${escapeHtml(item.label)}</li>`
    )
    .join('');
  list.classList.remove('d-none');

  list.querySelectorAll('.gis-search-result').forEach((el) => {
    el.addEventListener('click', () => {
      const idx = parseInt(el.dataset.index, 10);
      if (items[idx]) onSelect(items[idx]);
    });
  });
}

function escapeHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

async function searchLocation(query) {
  const q = query.trim();
  if (q.length < 3) {
    hideSearchResults();
    return;
  }

  if (searchAbort) searchAbort.abort();
  searchAbort = new AbortController();

  const params = new URLSearchParams({
    q: `${q}, Petaling Jaya, Selangor, Malaysia`,
    format: 'json',
    addressdetails: '0',
    limit: '6',
    countrycodes: 'my',
    viewbox: PJ_VIEWBOX,
    bounded: '0',
  });

  try {
    const res = await fetch(`${NOMINATIM_URL}?${params}`, {
      signal: searchAbort.signal,
      headers: {
        Accept: 'application/json',
        'Accept-Language': 'ms,en',
      },
    });
    if (!res.ok) throw new Error('Geocoding failed');
    const data = await res.json();
    const items = data.map((row) => ({
      lat: parseFloat(row.lat),
      lng: parseFloat(row.lon),
      label: row.display_name,
    }));
    showSearchResults(items, (item) => setAnchor(item.lat, item.lng, item.label));
  } catch (err) {
    if (err.name !== 'AbortError') {
      showSearchResults([], () => {});
    }
  }
}

function initLocationSearch() {
  injectLocationSearchStyles();

  const input = document.getElementById('gis-location-search');
  const btn = document.getElementById('gis-location-search-btn');
  if (!input || input.dataset.searchBound) return;
  input.dataset.searchBound = '1';

  const runSearch = () => searchLocation(input.value);

  input.addEventListener('input', () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(runSearch, 450);
  });
  input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      runSearch();
    }
    if (e.key === 'Escape') hideSearchResults();
  });
  btn?.addEventListener('click', runSearch);

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.gis-location-search-wrap')) hideSearchResults();
  });
}

function initMapPicker(options = {}) {
  const mapElId = options.mapElementId || 'gis-map';
  const el = document.getElementById(mapElId);
  if (!el || map) return map;

  onCoordinatesChange = options.onCoordinatesChange || null;
  onGisDataChange = options.onGisDataChange || null;

  const lat = options.initialLat ?? MBPJ[0];
  const lng = options.initialLng ?? MBPJ[1];
  const zoom = options.initialZoom ?? 14;

  map = L.map(el, { center: [lat, lng], zoom, layers: [satelliteLayer] });
  L.control.layers({ Satelit: satelliteLayer, 'Peta Jalan': osmLayer }, {}, { position: 'topright' }).addTo(map);

  surveyLayerGroup = L.layerGroup().addTo(map);
  drawnItems = new L.FeatureGroup().addTo(map);

  if (options.initialGisData?.features?.length) {
    L.geoJSON(options.initialGisData).eachLayer((l) => drawnItems.addLayer(l));
  }

  if (options.initialLat != null && options.initialLng != null) {
    setSiteMarker(lat, lng);
    updateAnchorInputs(lat, lng);
  }

  const drawControl = new L.Control.Draw({
    draw: { polygon: true, polyline: false, rectangle: true, circle: false, circlemarker: false, marker: false },
    edit: { featureGroup: drawnItems },
  });
  map.addControl(drawControl);

  map.on('click', (e) => {
    setAnchor(e.latlng.lat, e.latlng.lng);
  });

  map.on(L.Draw.Event.CREATED, (e) => {
    drawnItems.addLayer(e.layer);
    if (onGisDataChange) {
      const geo = drawnItems.toGeoJSON();
      onGisDataChange(geo.features?.length ? geo : null);
    }
  });
  map.on(L.Draw.Event.DELETED, () => {
    if (onGisDataChange) {
      const geo = drawnItems.toGeoJSON();
      onGisDataChange(geo.features?.length ? geo : null);
    }
  });

  initLocationSearch();

  if (options.initialLocationLabel) {
    const searchInput = document.getElementById('gis-location-search');
    if (searchInput) searchInput.value = options.initialLocationLabel;
  }

  setTimeout(() => map.invalidateSize(), 300);
  return map;
}

function addSurveyLayer(parsed, fileName = '') {
  renderSurveyData(parsed, fileName);
}

function setSurvey2dDay(day) {
  currentSurvey2dDay = parseInt(day, 10);
  const data = window._lastSurvey2dParsed;
  if (data) renderSurveyData(data);
}

function loadStoredSurveys(attachments) {
  if (!attachments?.length) return;
  clearSurveyLayers();
  let i = 0;
  attachments.forEach((att) => {
    if (att.parse_status === 'ok' && att.parsed_data && att.parsed_data.type !== '1d') {
      if (att.parsed_data.type === '2d') {
        window._lastSurvey2dParsed = att.parsed_data;
        const sel = document.getElementById('survey-day-select');
        if (sel && att.parsed_data.days?.length) {
          sel.innerHTML = att.parsed_data.days.map((d) => `<option value="${d}">Hari ${d}</option>`).join('');
          currentSurvey2dDay = att.parsed_data.days[att.parsed_data.days.length - 1];
        }
      }
      renderSurveyData(att.parsed_data, att.file_name, i > 0);
      i += 1;
    }
  });
}

window.gisMapPicker = {
  initMapPicker,
  addSurveyLayer,
  clearSurveyLayers,
  setSurvey2dDay,
  loadStoredSurveys,
  getAnchor,
  setAnchor,
  searchLocation,
  getMap: () => map,
};

export { initMapPicker, addSurveyLayer, clearSurveyLayers, setSurvey2dDay, loadStoredSurveys, getAnchor, setAnchor };
