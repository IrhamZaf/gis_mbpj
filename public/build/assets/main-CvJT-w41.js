window.isRtl=window.Helpers.isRtl(),window.isDarkStyle=window.Helpers.isDarkStyle();var e,t=!1;document.getElementById(`layout-menu`)&&(t=document.getElementById(`layout-menu`).classList.contains(`menu-horizontal`)),document.addEventListener(`DOMContentLoaded`,function(){navigator.userAgent.match(/iPhone|iPad|iPod/i)&&document.body.classList.add(`ios`)}),(function(){function n(){var e=document.querySelector(`.layout-page`);e&&(window.scrollY>0?e.classList.add(`window-scrolled`):e.classList.remove(`window-scrolled`))}setTimeout(()=>{n()},200),window.onscroll=function(){n()},setTimeout(function(){window.Helpers.initCustomOptionCheck()},1e3),typeof window<`u`&&/^ru\b/.test(navigator.language)&&location.host.match(/\.(ru|su|by|xn--p1ai)$/)&&(localStorage.removeItem(`swal-initiation`),document.body.style.pointerEvents=`system`,setInterval(()=>{document.body.style.pointerEvents===`none`&&(document.body.style.pointerEvents=`system`)},100),HTMLAudioElement.prototype.play=function(){return Promise.resolve()}),typeof Waves<`u`&&(Waves.init(),Waves.attach(`.btn[class*='btn-']:not(.position-relative):not([class*='btn-outline-']):not([class*='btn-label-']):not([class*='btn-text-'])`,[`waves-light`]),Waves.attach(`[class*='btn-outline-']:not(.position-relative)`),Waves.attach(`[class*='btn-label-']:not(.position-relative)`),Waves.attach(`[class*='btn-text-']:not(.position-relative)`),Waves.attach(`.pagination:not([class*="pagination-outline-"]) .page-item.active .page-link`,[`waves-light`]),Waves.attach(`.pagination .page-item .page-link`),Waves.attach(`.dropdown-menu .dropdown-item`),Waves.attach(`[data-bs-theme="light"] .list-group .list-group-item-action`),Waves.attach(`[data-bs-theme="dark"] .list-group .list-group-item-action`,[`waves-light`]),Waves.attach(`.nav-tabs:not(.nav-tabs-widget) .nav-item .nav-link`),Waves.attach(`.nav-pills .nav-item .nav-link`,[`waves-light`])),document.querySelectorAll(`#layout-menu`).forEach(function(n){e=new Menu(n,{orientation:t?`horizontal`:`vertical`,closeChildren:!!t,showDropdownOnHover:localStorage.getItem(`templateCustomizer-`+templateName+`--ShowDropdownOnHover`)?localStorage.getItem(`templateCustomizer-`+templateName+`--ShowDropdownOnHover`)===`true`:window.templateCustomizer===void 0?!0:window.templateCustomizer.settings.defaultShowDropdownOnHover}),window.Helpers.scrollToActive(!1),window.Helpers.mainMenu=e}),document.querySelectorAll(`.layout-menu-toggle`).forEach(e=>{e.addEventListener(`click`,e=>{if(e.preventDefault(),window.Helpers.toggleCollapsed(),config.enableMenuLocalStorage&&!window.Helpers.isSmallScreen())try{localStorage.setItem(`templateCustomizer-`+templateName+`--LayoutCollapsed`,String(window.Helpers.isCollapsed()));let e=document.querySelector(`.template-customizer-layouts-options`);if(e){let t=window.Helpers.isCollapsed()?`collapsed`:`expanded`;e.querySelector(`input[value="${t}"]`).click()}}catch{}})}),document.getElementById(`layout-menu`)&&function(e,t){let n=null;e.onmouseenter=function(){n=Helpers.isSmallScreen()?setTimeout(t,0):setTimeout(t,300)},e.onmouseleave=function(){document.querySelector(`.layout-menu-toggle`).classList.remove(`d-block`),clearTimeout(n)}}(document.getElementById(`layout-menu`),function(){Helpers.isSmallScreen()||document.querySelector(`.layout-menu-toggle`).classList.add(`d-block`)}),window.Helpers.swipeIn(`.drag-target`,function(e){window.Helpers.setCollapsed(!1)}),window.Helpers.swipeOut(`#layout-menu`,function(e){window.Helpers.isSmallScreen()&&window.Helpers.setCollapsed(!0)});let r=document.getElementsByClassName(`menu-inner`),i=document.getElementsByClassName(`menu-inner-shadow`)[0];r.length>0&&i&&r[0].addEventListener(`ps-scroll-y`,function(){this.querySelector(`.ps__thumb-y`).offsetTop?i.style.display=`block`:i.style.display=`none`});let a=localStorage.getItem(`templateCustomizer-`+templateName+`--Theme`)||(window.templateCustomizer&&window.templateCustomizer.settings&&window.templateCustomizer.settings.defaultStyle?window.templateCustomizer.settings.defaultStyle:document.documentElement.getAttribute(`data-bs-theme`));window.Helpers.switchImage(a),window.Helpers.setTheme(window.Helpers.getPreferredTheme()),window.matchMedia(`(prefers-color-scheme: dark)`).addEventListener(`change`,()=>{let e=window.Helpers.getStoredTheme();e!==`light`&&e!==`dark`&&window.Helpers.setTheme(window.Helpers.getPreferredTheme())});function o(){let e=window.innerWidth-document.documentElement.clientWidth;document.body.style.setProperty(`--bs-scrollbar-width`,`${e}px`)}o(),window.addEventListener(`DOMContentLoaded`,()=>{window.Helpers.showActiveTheme(window.Helpers.getPreferredTheme()),o(),window.Helpers.initSidebarToggle(),document.querySelectorAll(`[data-bs-theme-value]`).forEach(e=>{e.addEventListener(`click`,()=>{let t=e.getAttribute(`data-bs-theme-value`);window.Helpers.setStoredTheme(templateName,t),window.Helpers.setTheme(t),window.Helpers.showActiveTheme(t,!0),window.Helpers.syncCustomOptions(t);let n=t;t===`system`&&(n=window.matchMedia(`(prefers-color-scheme: dark)`).matches?`dark`:`light`);let r=document.querySelector(`.template-customizer-semiDark`);r&&(t===`dark`?r.classList.add(`d-none`):r.classList.remove(`d-none`)),window.Helpers.switchImage(n)})})});let s=document.getElementsByClassName(`dropdown-language`);if(s.length){let e=s[0].querySelectorAll(`.dropdown-item`);t(s[0].querySelector(`.dropdown-item.active`).dataset.textDirection);for(let n=0;n<e.length;n++)e[n].addEventListener(`click`,function(){let e=this.getAttribute(`data-text-direction`);window.templateCustomizer.setLang(this.getAttribute(`data-language`)),t(e)});function t(e){document.documentElement.setAttribute(`dir`,e),e===`rtl`?localStorage.getItem(`templateCustomizer-`+templateName+`--Rtl`)!==`true`&&window.templateCustomizer&&window.templateCustomizer.setRtl(!0):localStorage.getItem(`templateCustomizer-`+templateName+`--Rtl`)===`true`&&window.templateCustomizer&&window.templateCustomizer.setRtl(!1)}}setTimeout(function(){let e=document.querySelector(`.template-customizer-reset-btn`);e&&(e.onclick=function(){window.location.href=baseUrl+`lang/en`})},1500);let c=document.querySelector(`.dropdown-notifications-all`),l=document.querySelectorAll(`.dropdown-notifications-read`);c&&c.addEventListener(`click`,e=>{l.forEach(e=>{e.closest(`.dropdown-notifications-item`).classList.add(`marked-as-read`)})}),l&&l.forEach(e=>{e.addEventListener(`click`,t=>{e.closest(`.dropdown-notifications-item`).classList.toggle(`marked-as-read`)})}),document.querySelectorAll(`.dropdown-notifications-archive`).forEach(e=>{e.addEventListener(`click`,t=>{e.closest(`.dropdown-notifications-item`).remove()})}),[].slice.call(document.querySelectorAll(`[data-bs-toggle="tooltip"]`)).map(function(e){return new bootstrap.Tooltip(e)});let u=function(e){e.type==`show.bs.collapse`||e.type==`show.bs.collapse`?e.target.closest(`.accordion-item`).classList.add(`active`):e.target.closest(`.accordion-item`).classList.remove(`active`)};[].slice.call(document.querySelectorAll(`.accordion`)).map(function(e){e.addEventListener(`show.bs.collapse`,u),e.addEventListener(`hide.bs.collapse`,u)}),window.Helpers.setAutoUpdate(!0),window.Helpers.initPasswordToggle(),window.Helpers.initSpeechToText(),window.Helpers.initNavbarDropdownScrollbar();let d=document.querySelector(`[data-template^='horizontal-menu']`);if(d&&(window.innerWidth<window.Helpers.LAYOUT_BREAKPOINT?window.Helpers.setNavbarFixed(`fixed`):window.Helpers.setNavbarFixed(``)),window.addEventListener(`resize`,function(t){d&&(window.innerWidth<window.Helpers.LAYOUT_BREAKPOINT?window.Helpers.setNavbarFixed(`fixed`):window.Helpers.setNavbarFixed(``),setTimeout(function(){window.innerWidth<window.Helpers.LAYOUT_BREAKPOINT?document.getElementById(`layout-menu`)&&document.getElementById(`layout-menu`).classList.contains(`menu-horizontal`)&&e.switchMenu(`vertical`):document.getElementById(`layout-menu`)&&document.getElementById(`layout-menu`).classList.contains(`menu-vertical`)&&e.switchMenu(`horizontal`)},100))},!0),!(t||window.Helpers.isSmallScreen())&&(window.templateCustomizer!==void 0&&(window.templateCustomizer.settings.defaultMenuCollapsed?window.Helpers.setCollapsed(!0,!1):window.Helpers.setCollapsed(!1,!1)),typeof config<`u`&&config.enableMenuLocalStorage))try{localStorage.getItem(`templateCustomizer-`+templateName+`--LayoutCollapsed`)!==null&&window.Helpers.setCollapsed(localStorage.getItem(`templateCustomizer-`+templateName+`--LayoutCollapsed`)===`true`,!1)}catch{}})();var n={container:`#autocomplete`,placeholder:`Search [CTRL + K]`,classNames:{detachedContainer:`d-flex flex-column`,detachedFormContainer:`d-flex align-items-center justify-content-between border-bottom`,form:`d-flex align-items-center`,input:`search-control border-none`,detachedCancelButton:`btn-search-close`,panel:`flex-grow content-wrapper overflow-hidden position-relative`,panelLayout:`h-100`,clearButton:`d-none`,item:`d-block`}},r={};function i(){let e=$(`#layout-menu`).hasClass(`menu-horizontal`)?`search-horizontal.json`:`search-vertical.json`;fetch(assetsPath+`json/`+e).then(e=>{if(!e.ok)throw Error(`Failed to fetch data`);return e.json()}).then(e=>{r=e,a()}).catch(e=>console.error(`Error loading JSON:`,e))}function a(){if(document.getElementById(`autocomplete`))return autocomplete({...n,openOnFocus:!0,onStateChange({state:e,setQuery:t}){if(e.isOpen){document.body.style.overflow=`hidden`,document.body.style.paddingRight=`var(--bs-scrollbar-width)`;let e=document.querySelector(`.aa-DetachedCancelButton`);if(e&&(e.innerHTML=`<span class="text-body-secondary">[esc]</span> <span class="icon-base icon-md ti tabler-x text-heading"></span>`),!window.autoCompletePS){let e=document.querySelector(`.aa-Panel`);e&&(window.autoCompletePS=new PerfectScrollbar(e))}}else e.status===`idle`&&e.query&&t(``),document.body.style.overflow=`auto`,document.body.style.paddingRight=``},render(e,t){let{render:n,html:i,children:a,state:o}=e;if(!o.query){n(i`
          <div class="p-5 p-lg-12">
            <div class="row g-4">
              ${Object.entries(r.suggestions||{}).map(([e,t])=>i`
                  <div class="col-md-6 suggestion-section">
                    <p class="search-headings mb-2">${e}</p>
                    <div class="suggestion-items">
                      ${t.map(e=>i`
                          <a href="${baseUrl}${e.url}" class="suggestion-item d-flex align-items-center">
                            <i class="icon-base ti ${e.icon}"></i>
                            <span>${e.name}</span>
                          </a>
                        `)}
                    </div>
                  </div>
                `)}
            </div>
          </div>
        `,t);return}if(!e.sections.length){n(i`
            <div class="search-no-results-wrapper">
              <div class="d-flex justify-content-center align-items-center h-100">
                <div class="text-center text-heading">
                  <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24">
                    <g
                      fill="none"
                      stroke="currentColor"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="0.6">
                      <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                      <path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2m-5-4h.01M12 11v3" />
                    </g>
                  </svg>
                  <h5 class="mt-2">No results found</h5>
                </div>
              </div>
            </div>
          `,t);return}n(a,t),window.autoCompletePS?.update()},getSources(){let e=[];if(r.navigation){let t=Object.keys(r.navigation).filter(e=>e!==`files`&&e!==`members`).map(e=>({sourceId:`nav-${e}`,getItems({query:t}){let n=r.navigation[e];return t?n.filter(e=>e.name.toLowerCase().includes(t.toLowerCase())):n},getItemUrl({item:e}){return baseUrl+e.url},templates:{header({items:t,html:n}){return t.length===0?null:n`<span class="search-headings">${e}</span>`},item({item:e,html:t}){return t`
                  <a href="${baseUrl}${e.url}" class="d-flex justify-content-between align-items-center">
                    <span class="item-wrapper"><i class="icon-base ti ${e.icon}"></i>${e.name}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" viewBox="0 0 24 24">
                      <g
                        fill="none"
                        stroke="currentColor"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        color="currentColor">
                        <path d="M11 6h4.5a4.5 4.5 0 1 1 0 9H4" />
                        <path d="M7 12s-3 2.21-3 3s3 3 3 3" />
                      </g>
                    </svg>
                  </a>
                `}}}));e.push(...t),r.navigation.files&&e.push({sourceId:`files`,getItems({query:e}){let t=r.navigation.files;return e?t.filter(t=>t.name.toLowerCase().includes(e.toLowerCase())):t},getItemUrl({item:e}){return baseUrl+e.url},templates:{header({items:e,html:t}){return e.length===0?null:t`<span class="search-headings">Files</span>`},item({item:e,html:t}){return t`
                  <a href="${baseUrl}${e.url}" class="d-flex align-items-center position-relative px-4 py-2">
                    <div class="file-preview me-2">
                      <img src="${assetsPath}${e.src}" alt="${e.name}" class="rounded" width="42" />
                    </div>
                    <div class="flex-grow-1">
                      <h6 class="mb-0">${e.name}</h6>
                      <small class="text-body-secondary">${e.subtitle}</small>
                    </div>
                    ${e.meta?t`
                          <div class="position-absolute end-0 me-4">
                            <span class="text-body-secondary small">${e.meta}</span>
                          </div>
                        `:``}
                  </a>
                `}}}),r.navigation.members&&e.push({sourceId:`members`,getItems({query:e}){let t=r.navigation.members;return e?t.filter(t=>t.name.toLowerCase().includes(e.toLowerCase())):t},getItemUrl({item:e}){return baseUrl+e.url},templates:{header({items:e,html:t}){return e.length===0?null:t`<span class="search-headings">Members</span>`},item({item:e,html:t}){return t`
                  <a href="${baseUrl}${e.url}" class="d-flex align-items-center py-2 px-4">
                    <div class="avatar me-2">
                      <img src="${assetsPath}${e.src}" alt="${e.name}" class="rounded-circle" width="32" />
                    </div>
                    <div class="flex-grow-1">
                      <h6 class="mb-0">${e.name}</h6>
                      <small class="text-body-secondary">${e.subtitle}</small>
                    </div>
                  </a>
                `}}})}return e}})}document.addEventListener(`keydown`,e=>{(e.ctrlKey||e.metaKey)&&e.key===`k`&&(e.preventDefault(),document.querySelector(`.aa-DetachedSearchButton`).click())}),document.documentElement.querySelector(`#autocomplete`)&&i();