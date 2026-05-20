/**
 * Create the Leaflet map inside the report form.
 */
export function bootSurveyReportMap(config) {
  if (!config?.mapElementId) return;

  window.__surveyReportMapConfig = config;

  const runInit = () => {
    const el = document.getElementById(config.mapElementId);
    if (!el || !window.gisMapPicker) return false;

    if (window.gisMapPicker.mapContainerIsAlive?.(config.mapElementId)) {
      window.gisMapPicker.refreshMapLayout?.();
      return true;
    }

    window.gisMapPicker.destroyMapInstance?.();
    window.surveyReportLivewire = config.livewire ?? window.surveyReportLivewire ?? null;
    window.gisMapPicker.initMapPicker(config);

    setTimeout(() => window.gisMapPicker?.refreshMapLayout?.(), 150);
    setTimeout(() => window.gisMapPicker?.refreshMapLayout?.(), 400);
    setTimeout(() => window.gisMapPicker?.refreshMapLayout?.(), 900);

    return !!window.gisMapPicker.getMap?.();
  };

  if (runInit()) return;

  let tries = 0;
  const tick = () => {
    tries += 1;
    if (runInit() || tries > 40) return;
    requestAnimationFrame(tick);
  };
  requestAnimationFrame(tick);
}
