import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.heat';
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';
import 'leaflet-draw';
import 'leaflet-draw/dist/leaflet.draw.css';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png'
});

/* ── Risk styling ── */
const RISK = {
  kritikal: { color: '#ff1744', glow: 'rgba(255,23,68,0.6)', label: 'Kritikal', ringColor: 'rgba(255,23,68,0.15)' },
  pemantauan: { color: '#ff9100', glow: 'rgba(255,145,0,0.5)', label: 'Pemantauan', ringColor: 'rgba(255,145,0,0.10)' },
  selamat: { color: '#00e676', glow: 'rgba(0,230,118,0.4)', label: 'Selamat', ringColor: 'rgba(0,230,118,0.08)' }
};
function riskMeta(risk) {
  return RISK[risk] || RISK.selamat;
}

/* ── Custom pulsing icon via DivIcon ── */
function pulseIcon(risk) {
  const r = riskMeta(risk);
  const isCritical = risk === 'kritikal';
  return L.divIcon({
    className: 'gis-pulse-marker',
    html: `<div class="gis-marker-ring ${isCritical ? 'gis-pulse' : ''}" style="--ring-color:${r.glow}"></div>
           <div class="gis-marker-dot" style="background:${r.color};box-shadow:0 0 12px 4px ${r.glow}"></div>`,
    iconSize: [32, 32],
    iconAnchor: [16, 16],
    popupAnchor: [0, -18]
  });
}

/* ── Basemap tiles ── */
const basemaps = {
  'Gelap': L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    maxZoom: 20,
    attribution: '&copy; <a href="https://carto.com/">CARTO</a> &copy; <a href="https://osm.org/copyright">OSM</a>'
  }),
  'Satelit': L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    maxZoom: 19,
    attribution: '&copy; Esri, Maxar, Earthstar Geographics'
  }),
  'Standard': L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap'
  }),
  'Topo': L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
    maxZoom: 20,
    attribution: '&copy; CARTO &copy; OSM'
  })
};

/* ── Legend control ── */
const LegendControl = L.Control.extend({
  options: { position: 'bottomright' },
  onAdd() {
    const div = L.DomUtil.create('div', 'gis-legend');
    div.innerHTML = `
      <div class="gis-legend-title">Tahap Risiko</div>
      <div class="gis-legend-item"><span class="gis-legend-dot" style="background:#ff1744"></span> Kritikal</div>
      <div class="gis-legend-item"><span class="gis-legend-dot" style="background:#ff9100"></span> Pemantauan</div>
      <div class="gis-legend-item"><span class="gis-legend-dot" style="background:#00e676"></span> Selamat</div>
      <div class="gis-legend-sep"></div>
      <div class="gis-legend-title">Heatmap</div>
      <div class="gis-legend-gradient"></div>
      <div class="gis-legend-labels"><span>Rendah</span><span>Tinggi</span></div>
    `;
    L.DomEvent.disableClickPropagation(div);
    return div;
  }
});

/* ── Stats overlay control ── */
const StatsControl = L.Control.extend({
  options: { position: 'topleft' },
  onAdd() {
    const div = L.DomUtil.create('div', 'gis-stats-overlay');
    div.id = 'gisStatsOverlay';
    div.innerHTML = '<div class="gis-stats-loading">Memuatkan data…</div>';
    L.DomEvent.disableClickPropagation(div);
    return div;
  }
});

document.addEventListener('DOMContentLoaded', () => {
  const mapEl = document.getElementById('gisMainMap');
  if (!mapEl) return;

  const opts = window.gisMapOptions || {};
  const isHeatmap = opts.heatmap;

  // Use dark basemap as default for everything
  const defaultBase = basemaps['Gelap'];
  const map = L.map(mapEl, { layers: [defaultBase], zoomControl: false }).setView([3.107, 101.607], 13);

  // Zoom control on the right side
  L.control.zoom({ position: 'topright' }).addTo(map);

  // Layer control
  L.control.layers(basemaps, null, { position: 'topright', collapsed: true }).addTo(map);

  // Legend
  new LegendControl().addTo(map);

  // Stats overlay
  new StatsControl().addTo(map);

  const markers = L.markerClusterGroup({
    maxClusterRadius: 45,
    spiderfyOnMaxZoom: true,
    iconCreateFunction(cluster) {
      const count = cluster.getChildCount();
      let clsName = 'gis-cluster-small';
      if (count > 10) clsName = 'gis-cluster-large';
      else if (count > 5) clsName = 'gis-cluster-medium';
      return L.divIcon({
        html: `<div class="gis-cluster ${clsName}"><span>${count}</span></div>`,
        className: '',
        iconSize: [44, 44]
      });
    }
  });

  let heatLayer = null;
  const drawnItems = new L.FeatureGroup();
  map.addLayer(drawnItems);

  if (L.Control.Draw) {
    map.addControl(
      new L.Control.Draw({
        position: 'topleft',
        edit: { featureGroup: drawnItems },
        draw: { polygon: true, polyline: true, rectangle: false, circle: false, marker: true }
      })
    );
    map.on(L.Draw.Event.CREATED, (e) => {
      drawnItems.addLayer(e.layer);
    });
  }

  const markerIndex = [];
  const criticalRings = L.layerGroup();

  fetch(opts.geoJsonUrl || '/api/incidents/geojson')
    .then((r) => r.json())
    .then((geojson) => {
      const heatPoints = [];
      let countKritikal = 0, countPemantauan = 0, countSelamat = 0;

      (geojson.features || []).forEach((f) => {
        if (!f.geometry || f.geometry.type !== 'Point') return;
        const [lng, lat] = f.geometry.coordinates;
        const p = f.properties || {};
        const rm = riskMeta(p.risk_level);

        // Count stats
        if (p.risk_level === 'kritikal') countKritikal++;
        else if (p.risk_level === 'pemantauan') countPemantauan++;
        else countSelamat++;

        // Create marker with custom pulsing icon
        const m = L.marker([lat, lng], { icon: pulseIcon(p.risk_level) });
        m.feature = f;
        m.bindPopup(
          `<div class="gis-popup">
            <div class="gis-popup-header" style="border-left:4px solid ${rm.color}">
              <strong>${p.incident_number || ''}</strong>
              <span class="gis-popup-badge" style="background:${rm.color}">${rm.label}</span>
            </div>
            <div class="gis-popup-body">
              <div class="gis-popup-row"><i class="ti tabler-map-pin"></i> ${p.address || '—'}</div>
              <div class="gis-popup-row"><i class="ti tabler-activity"></i> ${p.status || '—'}</div>
              <div class="gis-popup-row"><i class="ti tabler-category"></i> ${p.category || '—'}</div>
            </div>
            <div class="gis-popup-actions">
              <button type="button" class="btn btn-sm btn-primary gis-open-offcanvas" data-props="${encodeURIComponent(JSON.stringify(p))}">Lihat penuh</button>
              <a class="btn btn-sm btn-outline-light" href="/incidents/${p.id}">Buka halaman</a>
            </div>
          </div>`,
          { maxWidth: 320, className: 'gis-custom-popup' }
        );
        markers.addLayer(m);
        markerIndex.push({ layer: m, props: p });

        // Heat intensity based on risk
        const intensity = p.risk_level === 'kritikal' ? 1.0 : p.risk_level === 'pemantauan' ? 0.6 : 0.25;
        heatPoints.push([lat, lng, intensity]);

        // Concentric rings for critical incidents
        if (p.risk_level === 'kritikal') {
          [300, 600, 1000].forEach((r, i) => {
            criticalRings.addLayer(
              L.circle([lat, lng], {
                radius: r,
                color: `rgba(255,23,68,${0.5 - i * 0.15})`,
                weight: 1.5,
                fillColor: `rgba(255,23,68,${0.12 - i * 0.03})`,
                fillOpacity: 1,
                dashArray: i > 0 ? '6 4' : ''
              })
            );
          });
        }
      });

      map.addLayer(markers);

      // Add concentric risk rings
      if (criticalRings.getLayers().length) {
        criticalRings.addTo(map);
      }

      // Heatmap layer
      if (heatPoints.length && typeof L.heatLayer === 'function') {
        heatLayer = L.heatLayer(heatPoints, {
          radius: 55,
          blur: 30,
          maxZoom: 18,
          minOpacity: 0.55,
          max: 1.0,
          gradient: {
            0.0: 'rgba(0,0,40,0)',
            0.15: '#0d47a1',
            0.3: '#1565c0',
            0.4: '#00bcd4',
            0.5: '#4caf50',
            0.6: '#cddc39',
            0.7: '#ffeb3b',
            0.8: '#ff9800',
            0.9: '#f44336',
            1.0: '#b71c1c'
          }
        });
        // Don't add by default if normal mode
        if (isHeatmap) map.addLayer(heatLayer);
      }

      /* ── Mode Toggle ── */
      const btns = document.querySelectorAll('.gis-mode-btn');
      btns.forEach(btn => {
        btn.addEventListener('click', () => {
          const mode = btn.dataset.mode;
          if (mode === 'heatmap') {
            if (heatLayer) map.addLayer(heatLayer);
            map.removeLayer(markers);
            document.getElementById('btnModeHeatmap').className = 'btn btn-sm btn-primary gis-mode-btn';
            document.getElementById('btnModeHeatmap').style.color = '';
            document.getElementById('btnModeNormal').className = 'btn btn-sm btn-outline-light gis-mode-btn';
            document.getElementById('btnModeNormal').style.color = '#94a3b8';
          } else {
            if (heatLayer) map.removeLayer(heatLayer);
            map.addLayer(markers);
            document.getElementById('btnModeNormal').className = 'btn btn-sm btn-primary gis-mode-btn';
            document.getElementById('btnModeNormal').style.color = '';
            document.getElementById('btnModeHeatmap').className = 'btn btn-sm btn-outline-light gis-mode-btn';
            document.getElementById('btnModeHeatmap').style.color = '#94a3b8';
          }
        });
      });

      // Update stats overlay
      const statsEl = document.getElementById('gisStatsOverlay');
      if (statsEl) {
        const total = countKritikal + countPemantauan + countSelamat;
        statsEl.innerHTML = `
          <div class="gis-stats-title">Ringkasan Insiden</div>
          <div class="gis-stats-row">
            <span class="gis-stats-dot" style="background:#ff1744"></span>
            <span>Kritikal</span>
            <strong>${countKritikal}</strong>
          </div>
          <div class="gis-stats-row">
            <span class="gis-stats-dot" style="background:#ff9100"></span>
            <span>Pemantauan</span>
            <strong>${countPemantauan}</strong>
          </div>
          <div class="gis-stats-row">
            <span class="gis-stats-dot" style="background:#00e676"></span>
            <span>Selamat</span>
            <strong>${countSelamat}</strong>
          </div>
          <div class="gis-stats-total">Jumlah: <strong>${total}</strong></div>
        `;
      }

      try {
        if (markers.getBounds().isValid()) map.fitBounds(markers.getBounds(), { padding: [50, 50], maxZoom: 14 });
      } catch (e) {
        /* empty */
      }
    });

  /* ── Offcanvas handler ── */
  mapEl.addEventListener('click', (e) => {
    const btn = e.target.closest('.gis-open-offcanvas');
    if (!btn || !btn.dataset.props) return;
    e.preventDefault();
    let props;
    try {
      props = JSON.parse(decodeURIComponent(btn.dataset.props));
    } catch (err) {
      return;
    }
    const body = document.getElementById('gisIncidentOffcanvasBody');
    if (!body) return;
    const rm = riskMeta(props.risk_level);
    body.innerHTML = `
      <div class="mb-3 p-3 rounded" style="background:${rm.color}15;border-left:4px solid ${rm.color}">
        <h6 class="mb-1">${props.incident_number || ''}</h6>
        <span class="badge" style="background:${rm.color};color:#fff">${rm.label}</span>
      </div>
      <dl class="row small mb-0">
        <dt class="col-sm-4">Kategori</dt><dd class="col-sm-8">${props.category || ''}</dd>
        <dt class="col-sm-4">Status</dt><dd class="col-sm-8">${props.status || ''}</dd>
        <dt class="col-sm-4">Alamat</dt><dd class="col-sm-8">${props.address || '-'}</dd>
      </dl>
      <a class="btn btn-primary w-100 mt-3" href="/incidents/${props.id}">Buka halaman insiden</a>
    `;
    const el = document.getElementById('gisIncidentOffcanvas');
    if (el && window.bootstrap?.Offcanvas) {
      window.bootstrap.Offcanvas.getOrCreateInstance(el).show();
    }
  });

  /* ── Search ── */
  const search = document.getElementById('gisMapSearch');
  if (search) {
    search.addEventListener('keydown', (ev) => {
      if (ev.key !== 'Enter') return;
      const q = search.value.trim().toLowerCase();
      if (!q) return;
      markerIndex.forEach(({ layer, props }) => {
        const num = String(props.incident_number || '').toLowerCase();
        const addr = String(props.address || '').toLowerCase();
        if (num.includes(q) || addr.includes(q)) {
          map.setView(layer.getLatLng(), 16);
          layer.openPopup();
        }
      });
    });
  }

  setTimeout(() => map.invalidateSize(), 400);
});
