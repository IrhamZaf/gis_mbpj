/**
 * Template Customizer Initializer
 * Imported by Blade views to initialize the Vuexy template customizer.
 */
import { TemplateCustomizer } from '../vendor/js/template-customizer.js';

// Make it globally available
window.TemplateCustomizer = TemplateCustomizer;

function initCustomizer() {
  const el = document.documentElement;
  const appliedSkin = el.getAttribute('data-skin') || 'default';

  // Read config from data attributes injected by Blade
  const customizerEl = document.getElementById('template-customizer-config');
  if (!customizerEl) return;

  try {
    const cfg = JSON.parse(customizerEl.textContent);

    window.templateCustomizer = new TemplateCustomizer({
      defaultTextDir: cfg.textDir || 'ltr',
      defaultPrimaryColor: cfg.primaryColor || undefined,
      defaultTheme: cfg.theme || 'light',
      defaultSkin: appliedSkin,
      defaultSemiDark: cfg.semiDark || false,
      defaultShowDropdownOnHover: cfg.showDropdownOnHover || true,
      displayCustomizer: cfg.displayCustomizer || true,
      lang: 'en',
      controls: cfg.controls || [],
    });

    if (cfg.primaryColor && window.Helpers && typeof window.Helpers.setColor === 'function') {
      window.Helpers.setColor(cfg.primaryColor, true);
    }
  } catch (error) {
    console.warn('Template customizer initialization error:', error);
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initCustomizer);
} else {
  initCustomizer();
}
