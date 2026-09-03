document.addEventListener(`DOMContentLoaded`,function(e){let t=document.getElementById(`section-block`),n=document.querySelector(`.btn-section-block`),r=document.querySelector(`.btn-section-block-overlay`),i=document.querySelector(`.btn-section-block-spinner`),a=document.querySelector(`.btn-section-block-custom`),o=document.querySelector(`.btn-section-block-multiple`),s=`#section-block`,c=document.querySelector(`#card-block`),l=document.querySelector(`.btn-card-block`),u=document.querySelector(`.btn-card-block-overlay`),d=document.querySelector(`.btn-card-block-spinner`),f=document.querySelector(`.btn-card-block-custom`),p=document.querySelector(`.btn-card-block-multiple`),m=`#card-block`,h=document.querySelector(`.form-block`),g=document.querySelector(`.btn-form-block`),_=document.querySelector(`.btn-form-block-overlay`),v=document.querySelector(`.btn-form-block-spinner`),y=document.querySelector(`.btn-form-block-custom`),b=document.querySelector(`.btn-form-block-multiple`),x=`.form-block`,S=document.querySelector(`#option-block`),C=document.querySelector(`.btn-option-block`),w=document.querySelector(`.btn-option-block-hourglass`),T=document.querySelector(`.btn-option-block-circle`),E=document.querySelector(`.btn-option-block-arrows`),D=document.querySelector(`.btn-option-block-dots`),O=document.querySelector(`.btn-option-block-pulse`),k=`#option-block`,A=document.querySelector(`.btn-page-block`),j=document.querySelector(`.btn-page-block-overlay`),M=document.querySelector(`.btn-page-block-spinner`),N=document.querySelector(`.btn-page-block-custom`),P=document.querySelector(`.btn-page-block-multiple`),F=document.querySelector(`.remove-btn`),I=document.querySelector(`.remove-card-btn`),L=document.querySelector(`.remove-form-btn`),R=document.querySelector(`.remove-option-btn`),z=document.querySelector(`.remove-page-btn`);t&&n&&n.addEventListener(`click`,()=>{Block.circle(s,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`40px`,svgColor:config.colors.white})}),t&&r&&r.addEventListener(`click`,()=>{Block.standard(s,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`0px`});let e=document.createElement(`div`);e.classList.add(`spinner-border`,`text-primary`),e.setAttribute(`role`,`status`),document.querySelector(`#section-block .notiflix-block`).appendChild(e)}),t&&i&&i.addEventListener(`click`,()=>{Block.standard(s,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`0px`});let e=document.querySelector(`#section-block .notiflix-block`);e.innerHTML=`
          <div class="sk-wave mx-auto">
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
          </div>
        `}),t&&a&&a.addEventListener(`click`,()=>{Block.standard(s,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`0px`});let e=document.querySelector(`#section-block .notiflix-block`);e.innerHTML=`
          <div class="d-flex">
              <p class="mb-0 text-white">Please wait...</p>
              <div class="sk-wave m-0">
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
              </div>
          </div>
        `});let B,V,H;t&&o&&o.addEventListener(`click`,()=>{Block.standard(s,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`0px`});let e=document.querySelector(`#section-block .notiflix-block`);e&&(e.innerHTML=`
            <div class="d-flex justify-content-center">
                <p class="mb-0 text-white">Please wait...</p>
                <div class="sk-wave m-0">
                    <div class="sk-rect sk-wave-rect"></div>
                    <div class="sk-rect sk-wave-rect"></div>
                    <div class="sk-rect sk-wave-rect"></div>
                    <div class="sk-rect sk-wave-rect"></div>
                    <div class="sk-rect sk-wave-rect"></div>
                </div>
            </div>
        `),Block.remove(s,1e3),B=setTimeout(()=>{Block.standard(s,`Almost Done...`,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,messageFontSize:`15px`,svgSize:`0px`,messageColor:config.colors.white}),Block.remove(s,1e3),V=setTimeout(()=>{Block.standard(s,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`});let e=document.querySelector(`#section-block .notiflix-block`);e&&(e.innerHTML=`<div class="px-12 py-3 bg-success text-white">Success</div>`),H=setTimeout(()=>{Block.remove(s),setTimeout(()=>{n.classList.remove(`disabled`),r.classList.remove(`disabled`),i.classList.remove(`disabled`),a.classList.remove(`disabled`),o.classList.remove(`disabled`)},500)},1810)},1810)},1610)});let U=[`.btn-section-block`,`.btn-section-block-overlay`,`.btn-section-block-spinner`,`.btn-section-block-custom`,`.btn-section-block-multiple`].map(e=>document.querySelector(e));U.forEach(e=>{e&&e.addEventListener(`click`,()=>{U.forEach(e=>{e&&e.classList.add(`disabled`)})})}),F&&F.addEventListener(`click`,()=>{setTimeout(()=>{document.querySelector(`${s} .notiflix-block`)?Block.remove(s):alert(`No active block to remove.`)},400),clearTimeout(B),clearTimeout(V),clearTimeout(H),setTimeout(()=>{n.classList.remove(`disabled`),r.classList.remove(`disabled`),i.classList.remove(`disabled`),a.classList.remove(`disabled`),o.classList.remove(`disabled`)},500)}),c&&l&&l.addEventListener(`click`,()=>{Block.circle(m,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`40px`,svgColor:config.colors.white})}),c&&u&&u.addEventListener(`click`,()=>{Block.standard(m,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`0px`});let e=document.createElement(`div`);e.classList.add(`spinner-border`,`text-primary`),e.setAttribute(`role`,`status`),document.querySelector(`#card-block .notiflix-block`).appendChild(e)}),c&&d&&d.addEventListener(`click`,()=>{Block.standard(m,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`0px`});let e=document.querySelector(`#card-block .notiflix-block`);e.innerHTML=`
          <div class="sk-wave mx-auto">
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
          </div>
        `}),c&&f&&f.addEventListener(`click`,()=>{Block.standard(m,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`0px`});let e=document.querySelector(`#card-block .notiflix-block`);e.innerHTML=`
          <div class="d-flex">
              <p class="mb-0 text-white">Please wait...</p>
              <div class="sk-wave m-0">
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
              </div>
          </div>
        `});let W,G,K;c&&p&&p.addEventListener(`click`,()=>{Block.standard(m,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`0px`});let e=document.querySelector(`#card-block .notiflix-block`);e&&(e.innerHTML=`
            <div class="d-flex justify-content-center">
              <p class="mb-0 text-white">Please wait...</p>
              <div class="sk-wave m-0">
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
              </div>
            </div>
          `),Block.remove(m,1e3),W=setTimeout(()=>{Block.standard(m,`Almost Done...`,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,messageFontSize:`15px`,svgSize:`0px`,messageColor:config.colors.white}),Block.remove(m,1e3),G=setTimeout(()=>{Block.standard(m,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`});let e=document.querySelector(`#card-block .notiflix-block`);e&&(e.innerHTML=`<div class="px-12 py-3 bg-success text-white">Success</div>`),K=setTimeout(()=>{Block.remove(m)},1610)},1610)},1610)}),[`.btn-card-block`,`.btn-card-block-overlay`,`.btn-card-block-spinner`,`.btn-card-block-custom`,`.btn-card-block-multiple`].map(e=>document.querySelector(e)).forEach(e=>{e&&e.addEventListener(`click`,()=>{I.style.position=`relative`,I.style.pointerEvents=`auto`,I.style.zIndex=1074})}),I&&I.addEventListener(`click`,()=>{setTimeout(()=>{document.querySelector(`${m} .notiflix-block`)?Block.remove(m):alert(`No active block to remove.`)},400),clearTimeout(W),clearTimeout(G),clearTimeout(K)}),S&&C&&C.addEventListener(`click`,()=>{Block.standard(k,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`40px`,svgColor:config.colors.white})}),S&&w&&w.addEventListener(`click`,()=>{Block.hourglass(k,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`40px`,svgColor:config.colors.white})}),S&&T&&T.addEventListener(`click`,()=>{Block.circle(k,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`40px`,svgColor:config.colors.white})}),S&&E&&E.addEventListener(`click`,()=>{Block.arrows(k,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`40px`,svgColor:config.colors.white})}),S&&D&&D.addEventListener(`click`,()=>{Block.dots(k,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`40px`,svgColor:config.colors.white})}),S&&O&&O.addEventListener(`click`,()=>{Block.pulse(k,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`40px`,svgColor:config.colors.white})}),[`.btn-option-block`,`.btn-option-block-overlay`,`.btn-option-block-spinner`,`.btn-option-block-custom`,`.btn-option-block-multiple`].map(e=>document.querySelector(e)).forEach(e=>{e&&e.addEventListener(`click`,()=>{R.style.position=`relative`,R.style.pointerEvents=`auto`,R.style.zIndex=1074})}),R&&R.addEventListener(`click`,()=>{document.querySelector(`${k} .notiflix-block`)?Block.remove(k):alert(`No active block to remove.`)}),A&&A.addEventListener(`click`,()=>{Loading.circle({backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`40px`,svgColor:config.colors.white})}),j&&j.addEventListener(`click`,()=>{Loading.standard({backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`0px`});let e=document.createElement(`div`);e.classList.add(`spinner-border`,`text-primary`),e.setAttribute(`role`,`status`),document.querySelector(`.notiflix-loading`).appendChild(e)}),M&&M.addEventListener(`click`,()=>{Loading.standard({backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`0px`});let e=document.querySelector(`.notiflix-loading`);e.innerHTML=`
          <div class="sk-wave mx-auto">
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
          </div>
        `}),N&&N.addEventListener(`click`,()=>{Loading.standard({backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`0px`});let e=document.querySelector(`.notiflix-loading`);e.innerHTML=`
          <div class="d-flex">
              <p class="mb-0 text-white">Please wait...</p>
              <div class="sk-wave m-0">
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
              </div>
          </div>
        `});let q,J,Y;P&&P.addEventListener(`click`,()=>{Loading.standard({backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`0px`});let e=document.querySelector(`.notiflix-loading`);e&&(e.innerHTML=`
            <div class="d-flex justify-content-center">
              <p class="mb-0 text-white">Please wait...</p>
              <div class="sk-wave m-0">
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
              </div>
            </div>
          `),Loading.remove(1e3),q=setTimeout(()=>{Loading.standard(`Almost Done...`,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,messageFontSize:`15px`,svgSize:`0px`,messageColor:config.colors.white}),Loading.remove(1e3),J=setTimeout(()=>{Loading.standard({backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`});let e=document.querySelector(`.notiflix-loading`);e&&(e.innerHTML=`<div class="px-12 py-3 bg-success text-white">Success</div>`),Y=setTimeout(()=>{Loading.remove()},1610)},1610)},1610)}),[`.btn-page-block`,`.btn-page-block-overlay`,`.btn-page-block-spinner`,`.btn-page-block-custom`,`.btn-page-block-multiple`].map(e=>document.querySelector(e)).forEach(e=>{e&&e.addEventListener(`click`,()=>{z.style.position=`relative`,z.style.pointerEvents=`auto`,z.style.zIndex=9999})}),z&&z.addEventListener(`click`,()=>{document.querySelector(`.notiflix-loading`)?Loading.remove():alert(`No active loading to remove.`),clearTimeout(q),clearTimeout(J),clearTimeout(Y)}),h&&g&&g.addEventListener(`click`,()=>{Block.circle(x,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`40px`,svgColor:config.colors.white})}),h&&_&&_.addEventListener(`click`,()=>{Block.standard(x,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`0px`});let e=document.createElement(`div`);e.classList.add(`spinner-border`,`text-primary`),e.setAttribute(`role`,`status`),document.querySelector(`.form-block .notiflix-block`).appendChild(e)}),h&&v&&v.addEventListener(`click`,()=>{Block.standard(x,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`0px`});let e=document.querySelector(`.form-block .notiflix-block`);e.innerHTML=`
          <div class="sk-wave mx-auto">
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
          </div>
        `}),h&&y&&y.addEventListener(`click`,()=>{Block.standard(x,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`0px`});let e=document.querySelector(`.form-block .notiflix-block`);e.innerHTML=`
          <div class="d-flex">
              <p class="mb-0 text-white">Please wait...</p>
              <div class="sk-wave m-0">
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
              </div>
          </div>
        `});let X,Z,Q;h&&b&&b.addEventListener(`click`,()=>{Block.standard(x,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,svgSize:`0px`});let e=document.querySelector(`.form-block .notiflix-block`);e&&(e.innerHTML=`
            <div class="d-flex justify-content-center">
              <p class="mb-0 text-white">Please wait...</p>
              <div class="sk-wave m-0">
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
              </div>
            </div>
          `),Block.remove(x,1e3),X=setTimeout(()=>{Block.standard(x,`Almost Done...`,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`,messageFontSize:`15px`,svgSize:`0px`,messageColor:config.colors.white}),Block.remove(x,1e3),Z=setTimeout(()=>{Block.standard(x,{backgroundColor:`rgba(`+window.Helpers.getCssVar(`black-rgb`)+`, 0.5)`});let e=document.querySelector(`.form-block .notiflix-block`);e&&(e.innerHTML=`<div class="px-12 py-3 bg-success text-white">Success</div>`),Q=setTimeout(()=>{Block.remove(x),setTimeout(()=>{g.classList.remove(`disabled`),_.classList.remove(`disabled`),v.classList.remove(`disabled`),y.classList.remove(`disabled`),b.classList.remove(`disabled`)},500)},1810)},1810)},1610)});let $=[`.btn-form-block`,`.btn-form-block-overlay`,`.btn-form-block-spinner`,`.btn-form-block-custom`,`.btn-form-block-multiple`].map(e=>document.querySelector(e));$.forEach(e=>{e&&e.addEventListener(`click`,()=>{$.forEach(e=>{e&&e.classList.add(`disabled`)})})}),L&&L.addEventListener(`click`,()=>{setTimeout(()=>{document.querySelector(`${x} .notiflix-block`)?Block.remove(x):alert(`No active block to remove.`)},450),clearTimeout(X),clearTimeout(Z),clearTimeout(Q),setTimeout(()=>{g.classList.remove(`disabled`),_.classList.remove(`disabled`),v.classList.remove(`disabled`),y.classList.remove(`disabled`),b.classList.remove(`disabled`)},500)})});