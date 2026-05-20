/**
 * Isolated map for ReportMapPicker Livewire child — not affected by parent form typing.
 */
import './gis-map-picker.js';
import { bootSurveyReportMap } from './survey-report-map-boot.js';

function mapPickerWire() {
  const host = document.querySelector('.report-map-picker-root')?.closest('[wire\\:id]');
  const id = host?.getAttribute('wire:id');
  if (id && typeof Livewire !== 'undefined' && Livewire.find) {
    return Livewire.find(id);
  }
  return null;
}

let pendingMapInit = null;

function paintLayers(layers) {
  if (!window.gisMapPicker?.getMap?.()) return;
  window.gisMapPicker.clearSurveyLayers?.();
  if (!Array.isArray(layers)) return;
  let i = 0;
  layers.forEach((layer) => {
    if (layer.parse_status === 'ok' && layer.parsed_data && layer.parsed_data.type !== '1d') {
      window.gisMapPicker.addSurveyLayer(layer.parsed_data, layer.file_name ?? '', i > 0);
      i += 1;
    }
  });
  window.gisMapPicker.refreshMapLayout?.();
}

function applyMapInit(payload) {
  const p = payload?.[0] ?? payload ?? {};
  if (p.latitude != null && p.longitude != null) {
    window.gisMapPicker?.setAnchor?.(p.latitude, p.longitude, p.locationLabel ?? '');
  }
  paintLayers(p.surveyLayers ?? []);
}

function bootMap() {
  const wire = mapPickerWire();
  if (!document.getElementById('gis-map')) return;

  bootSurveyReportMap({
    mapElementId: 'gis-map',
    initialLat: null,
    initialLng: null,
    initialZoom: 14,
    initialGisData: null,
    initialLocationLabel: '',
    livewire: wire,
    onCoordinatesChange(lat, lng, label) {
      wire?.call?.('updateCoordinates', lat, lng, label ?? null);
    },
    onGisDataChange(data) {
      wire?.call?.('updateGisData', data ?? null);
    },
  });

  if (pendingMapInit) {
    const init = pendingMapInit;
    pendingMapInit = null;
    setTimeout(() => applyMapInit(init), 100);
  }
}

function registerListeners() {
  if (window.__reportMapPickerListeners) return;
  window.__reportMapPickerListeners = true;

  Livewire.on('preview-survey-layers', (payload) => {
    const layers = payload?.layers ?? payload?.[0]?.layers ?? payload;
    paintLayers(layers);
  });

  Livewire.on('report-map-init', (payload) => {
    if (window.gisMapPicker?.getMap?.()) {
      applyMapInit(payload);
    } else {
      pendingMapInit = payload;
    }
  });
}

function tryBoot() {
  if (!document.querySelector('.report-map-picker-root')) return;
  registerListeners();
  bootMap();
}

document.addEventListener('livewire:init', tryBoot);
document.addEventListener('livewire:navigated', tryBoot);
document.addEventListener('DOMContentLoaded', () => {
  if (typeof Livewire === 'undefined') tryBoot();
});

let attempts = 0;
const timer = setInterval(() => {
  attempts += 1;
  tryBoot();
  if (attempts > 40) clearInterval(timer);
}, 150);
