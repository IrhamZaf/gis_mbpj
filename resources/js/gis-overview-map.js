import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';

import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
});

const MBPJ_CENTER = [3.1073, 101.6067];
const DEFAULT_ZOOM = 13;

const HEAT_OPTIONS = {
  radius: 30,
  blur: 20,
  maxZoom: 17,
  max: 1.0,
  minOpacity: 0.4,
  gradient: {
    0.0: '#313695',
    0.25: '#4575b4',
    0.45: '#74add1',
    0.55: '#abd9e9',
    0.65: '#fee090',
    0.8: '#fdae61',
    0.9: '#f46d43',
    1.0: '#d73027',
  },
};

let map = null;
let markerCluster = null;
let geoJsonLayer = null;
let heatLayer = null;
let viewMode = 'both';
let mapReady = false;
let heatPluginLoaded = false;

const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
  maxZoom: 19,
});

const grayscaleLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
  maxZoom: 19,
  className: 'gis-map-tiles-grayscale',
});

const satelliteLayer = L.tileLayer(
  'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
  {
    attribution: 'Tiles &copy; Esri',
    maxZoom: 19,
  }
);

async function loadHeatPlugin() {
  if (heatPluginLoaded) return;
  window.L = L;
  await import('leaflet.heat/dist/leaflet-heat.js');
  heatPluginLoaded = true;
}

function coloredIcon(color) {
  return L.divIcon({
    className: 'gis-marker-icon',
    html: `<span style="background:${color};width:18px;height:18px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.55);display:block;"></span>`,
    iconSize: [18, 18],
    iconAnchor: [9, 9],
    popupAnchor: [0, -10],
  });
}

function buildPopup(marker) {
  const link = marker.url
    ? `<a href="${marker.url}" class="btn btn-sm btn-primary mt-2">Lihat Laporan</a>`
    : '';
  return `
    <div class="gis-popup">
      <strong>${escapeHtml(marker.title)}</strong><br>
      <small class="text-muted">${escapeHtml(marker.report_number)}</small><br>
      <span class="badge bg-label-secondary">${escapeHtml(marker.category)}</span>
      <span class="badge bg-label-${marker.status === 'submitted' ? 'success' : 'warning'}">${escapeHtml(marker.status_label)}</span>
      ${marker.location_name ? `<br><small>${escapeHtml(marker.location_name)}</small>` : ''}
      ${link}
    </div>
  `;
}

function escapeHtml(text) {
  const el = document.createElement('div');
  el.textContent = text ?? '';
  return el.innerHTML;
}

function toHeatPoints(markers) {
  return markers.map((m) => [
    m.latitude,
    m.longitude,
    m.status === 'submitted' ? 1.0 : 0.55,
  ]);
}

function syncViewToggleButtons() {
  document.querySelectorAll('[data-map-view]').forEach((btn) => {
    btn.classList.toggle('active', btn.dataset.mapView === viewMode);
  });
}

function applyViewMode() {
  if (!map) return;

  if (map.hasLayer(markerCluster)) {
    map.removeLayer(markerCluster);
  }
  if (heatLayer && map.hasLayer(heatLayer)) {
    map.removeLayer(heatLayer);
  }

  const hasData = markerCluster.getLayers().length > 0;

  if (hasData && (viewMode === 'markers' || viewMode === 'both')) {
    map.addLayer(markerCluster);
  }
  if (hasData && heatLayer && (viewMode === 'heatmap' || viewMode === 'both')) {
    map.addLayer(heatLayer);
  }

  const legend = document.getElementById('heatmap-legend');
  if (legend) {
    legend.classList.toggle('d-none', viewMode === 'markers');
  }
}

function setViewMode(mode) {
  viewMode = mode;
  syncViewToggleButtons();
  applyViewMode();
}

function clearLayers() {
  if (markerCluster) {
    markerCluster.clearLayers();
  }
  if (geoJsonLayer) {
    map.removeLayer(geoJsonLayer);
    geoJsonLayer = null;
  }
  if (heatLayer) {
    heatLayer.setLatLngs([]);
  }
}

function updateMarkers(markers) {
  if (!mapReady || !map) return;

  clearLayers();

  const emptyEl = document.getElementById('map-empty-state');
  if (!markers || markers.length === 0) {
    if (emptyEl) emptyEl.classList.remove('d-none');
    map.setView(MBPJ_CENTER, DEFAULT_ZOOM);
    applyViewMode();
    return;
  }

  if (emptyEl) emptyEl.classList.add('d-none');

  const bounds = L.latLngBounds([]);
  const geoFeatures = [];
  const heatPoints = toHeatPoints(markers);

  markers.forEach((marker) => {
    const latLng = [marker.latitude, marker.longitude];
    bounds.extend(latLng);

    const m = L.marker(latLng, { icon: coloredIcon(marker.category_color) });
    m.bindPopup(buildPopup(marker));
    markerCluster.addLayer(m);

    if (marker.gis_data?.features?.length) {
      geoFeatures.push(...marker.gis_data.features);
    }
  });

  if (!heatLayer) {
    heatLayer = L.heatLayer(heatPoints, HEAT_OPTIONS);
  } else {
    heatLayer.setLatLngs(heatPoints);
  }

  if (geoFeatures.length) {
    geoJsonLayer = L.geoJSON(
      { type: 'FeatureCollection', features: geoFeatures },
      { style: { color: '#e74c3c', weight: 2, fillOpacity: 0.15 } }
    ).addTo(map);
  }

  map.fitBounds(bounds, { padding: [40, 40], maxZoom: 16 });
  applyViewMode();
}

function initMap() {
  const el = document.getElementById('gis-overview-map');
  if (!el || map) return;

  map = L.map(el, { center: MBPJ_CENTER, zoom: DEFAULT_ZOOM, layers: [satelliteLayer] });

  L.control.layers(
    {
      'Satelit (lalai)': satelliteLayer,
      Kelabu: grayscaleLayer,
      'Peta Berwarna': osmLayer,
    },
    {},
    { position: 'topright' }
  ).addTo(map);

  markerCluster = L.markerClusterGroup({
    iconCreateFunction(cluster) {
      const count = cluster.getChildCount();
      return L.divIcon({
        html: `<span class="gis-cluster-count">${count}</span>`,
        className: 'gis-cluster-icon',
        iconSize: L.point(40, 40),
      });
    },
  });

  const initial = window.reportMarkers ?? [];
  mapReady = true;
  updateMarkers(initial);
  syncViewToggleButtons();

  setTimeout(() => map.invalidateSize(), 300);
}

async function bootstrap() {
  await loadHeatPlugin();
  initMap();
}

document.addEventListener('DOMContentLoaded', () => {
  bootstrap();
});

document.addEventListener('livewire:init', () => {
  Livewire.on('map-markers-updated', (event) => {
    updateMarkers(event.markers ?? event);
  });
});

window.gisOverviewMap = { initMap: bootstrap, updateMarkers, setViewMode };
