import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

function riskMeta(risk) {
  const map = {
    kritikal: { color: '#ff1744', glow: 'rgba(255,23,68,0.6)' },
    pemantauan: { color: '#ff9100', glow: 'rgba(255,145,0,0.5)' },
    selamat: { color: '#00e676', glow: 'rgba(0,230,118,0.4)' }
  };
  return map[risk] || map.selamat;
}

function pulseIcon(risk) {
  const r = riskMeta(risk);
  const isCritical = risk === 'kritikal';
  return L.divIcon({
    className: 'gis-pulse-marker',
    html: `<div class="gis-marker-ring ${isCritical ? 'gis-pulse' : ''}" style="--ring-color:${r.glow}"></div>
           <div class="gis-marker-dot" style="background:${r.color};box-shadow:0 0 12px 4px ${r.glow}"></div>`,
    iconSize: [28, 28],
    iconAnchor: [14, 14]
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const cfg = window.gisEngineerReviewMap;
  const el = document.getElementById('gisEngineerReviewMap');
  
  if (el && cfg) {
    const satelliteTile = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
      maxZoom: 19,
      attribution: '&copy; Esri, Maxar'
    });
    
    const map = L.map(el, { layers: [satelliteTile] }).setView([cfg.lat, cfg.lng], 16);
    
    // Add pulsing marker for the incident under review
    L.marker([cfg.lat, cfg.lng], { icon: pulseIcon('kritikal') }).addTo(map);
    
    setTimeout(() => map.invalidateSize(), 400);
  }

  const range = document.getElementById('gisEngCompareRange');
  const clip = document.getElementById('gisEngBeforeClip');
  if (range && clip) {
    range.addEventListener('input', () => {
      clip.style.width = `${range.value}%`;
    });
  }
});
