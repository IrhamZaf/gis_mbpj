import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

document.addEventListener('DOMContentLoaded', () => {
  const cfg = window.gisIncidentShow;
  const el = document.getElementById('gisIncidentShowMap');
  if (el && cfg) {
    const map = L.map(el).setView([cfg.lat, cfg.lng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
    L.marker([cfg.lat, cfg.lng]).addTo(map);
    setTimeout(() => map.invalidateSize(), 400);
  }

  const range = document.getElementById('gisCompareRange');
  const clip = document.getElementById('gisBeforeClip');
  if (range && clip) {
    range.addEventListener('input', () => {
      clip.style.width = `${range.value}%`;
    });
  }
});
