/**
 * GIS MBPJ dashboard: mini map, chart, datatable
 */
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
  const el = document.getElementById('gisDashboardMiniMap');
  if (el) {
    const darkTile = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
      maxZoom: 20,
      attribution: '&copy; CARTO &copy; OSM'
    });
    const map = L.map(el, { zoomControl: true, layers: [darkTile] }).setView([3.107, 101.607], 12);
    fetch('/api/incidents/geojson')
      .then((r) => r.json())
      .then((geojson) => {
        const layer = L.geoJSON(geojson, {
          pointToLayer(feature, latlng) {
            const risk = feature.properties?.risk_level;
            return L.marker(latlng, { icon: pulseIcon(risk) });
          }
        }).addTo(map);
        if (layer.getBounds().isValid()) {
          map.fitBounds(layer.getBounds(), { padding: [24, 24], maxZoom: 14 });
        }
      })
      .catch(() => {});
    setTimeout(() => map.invalidateSize(), 400);
  }

  const stats = window.gisDashboardStats || { monthly: [] };
  const categories = stats.monthly.map((m) => m.ym);
  const series = stats.monthly.map((m) => m.c);
  const chartEl = document.querySelector('#gisIncidentTrendChart');
  if (chartEl && window.ApexCharts) {
    const chart = new window.ApexCharts(chartEl, {
      chart: { 
        type: 'area', 
        height: 320, 
        toolbar: { show: false },
        zoom: { enabled: false },
        dropShadow: {
          enabled: true,
          top: 10,
          left: 0,
          blur: 10,
          opacity: 0.1
        }
      },
      dataLabels: { enabled: false },
      stroke: { curve: 'smooth', width: 3 },
      series: [{ name: 'Insiden', data: series }],
      xaxis: { 
        categories,
        axisBorder: { show: false },
        axisTicks: { show: false }
      },
      yaxis: { tickAmount: 4 },
      grid: {
        borderColor: 'rgba(0,0,0,0.05)',
        padding: { top: 0, bottom: 0, left: 10, right: 10 }
      },
      colors: ['#7367f0'],
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.5,
          opacityTo: 0.1,
          stops: [0, 90, 100]
        }
      },
      tooltip: {
        theme: 'dark'
      }
    });
    chart.render();
  }

  const table = document.getElementById('gisDashboardIncidentsTable');
  if (table && window.jQuery && window.jQuery.fn.DataTable) {
    window.jQuery(table).DataTable({
      ordering: true,
      pageLength: 5,
      language: { search: '', searchPlaceholder: 'Cari...' }
    });
  }
});
