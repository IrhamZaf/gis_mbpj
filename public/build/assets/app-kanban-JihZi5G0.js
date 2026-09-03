(async function(){let e,t=document.querySelector(`.kanban-update-item-sidebar`),n=document.querySelector(`.kanban-wrapper`),r=document.querySelector(`.comment-editor`),i=document.querySelector(`.kanban-add-new-board`),a=[].slice.call(document.querySelectorAll(`.kanban-add-board-input`)),o=document.querySelector(`.kanban-add-board-btn`),s=document.querySelector(`#due-date`),c=$(`.select2`),l=document.querySelector(`html`).getAttribute(`data-assets-path`),u=new bootstrap.Offcanvas(t),d=await fetch(l+`json/kanban.json`);if(d.ok||console.error(`error`,d),e=await d.json(),s&&s.flatpickr({monthSelectorType:`static`,static:!0,altInput:!0,altFormat:`j F, Y`,dateFormat:`Y-m-d`}),c.length){function e(e){return e.id?`<div class='badge `+$(e.element).data(`color`)+`'> `+e.text+`</div>`:e.text}c.each(function(){var t=$(this);t.wrap(`<div class='position-relative'></div>`).select2({placeholder:`Select Label`,dropdownParent:t.parent(),templateResult:e,templateSelection:e,escapeMarkup:function(e){return e}})})}r&&new Quill(r,{modules:{toolbar:`.comment-toolbar`},placeholder:`Write a Comment...`,theme:`snow`});let f=()=>`
  <div class="dropdown">
      <i class="dropdown-toggle icon-base ti tabler-dots-vertical cursor-pointer"
         id="board-dropdown"
         data-bs-toggle="dropdown"
         aria-haspopup="true"
         aria-expanded="false">
      </i>
      <div class="dropdown-menu dropdown-menu-end" aria-labelledby="board-dropdown">
          <a class="dropdown-item delete-board" href="javascript:void(0)">
              <i class="icon-base ti tabler-trash icon-xs"></i>
              <span class="align-middle">Delete</span>
          </a>
          <a class="dropdown-item" href="javascript:void(0)">
              <i class="icon-base ti tabler-edit icon-xs"></i>
              <span class="align-middle">Rename</span>
          </a>
          <a class="dropdown-item" href="javascript:void(0)">
              <i class="icon-base ti tabler-archive icon-xs"></i>
              <span class="align-middle">Archive</span>
          </a>
      </div>
  </div>
`,p=()=>`
<div class="dropdown kanban-tasks-item-dropdown">
    <i class="dropdown-toggle icon-base ti tabler-dots-vertical"
       id="kanban-tasks-item-dropdown"
       data-bs-toggle="dropdown"
       aria-haspopup="true"
       aria-expanded="false">
    </i>
    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="kanban-tasks-item-dropdown">
        <a class="dropdown-item" href="javascript:void(0)">Copy task link</a>
        <a class="dropdown-item" href="javascript:void(0)">Duplicate task</a>
        <a class="dropdown-item delete-task" href="javascript:void(0)">Delete</a>
    </div>
</div>
`,m=(e,t)=>`
<div class="d-flex justify-content-between flex-wrap align-items-center mb-2">
    <div class="item-badges">
        <div class="badge bg-label-${e}">${t}</div>
    </div>
    ${p()}
</div>
`,h=(e=``,t=!1,n=``,r=``,i=``)=>{let a=t?` pull-up`:``,o=n?`avatar-${n}`:``,s=i?i.split(`,`):[];return e?e.split(`,`).map((e,t,n)=>`
            <div class="avatar ${o}${r&&t!==n.length-1?` me-${r}`:``} w-px-26 h-px-26"
                 data-bs-toggle="tooltip"
                 data-bs-placement="top"
                 title="${s[t]||``}">
                <img src="${l}img/avatars/${e}"
                     alt="Avatar"
                     class="rounded-circle${a}">
            </div>
        `).join(``):``},g=(e,t,n,r)=>`
<div class="d-flex justify-content-between align-items-center flex-wrap mt-2">
    <div class="d-flex">
        <span class="d-flex align-items-center me-2">
            <i class="icon-base ti tabler-paperclip me-1"></i>
            <span class="attachments">${e}</span>
        </span>
        <span class="d-flex align-items-center ms-2">
            <i class="icon-base ti tabler-message-2 me-1"></i>
            <span>${t}</span>
        </span>
    </div>
    <div class="avatar-group d-flex align-items-center assigned-avatar">
        ${h(n,!0,`xs`,null,r)}
    </div>
</div>
`,_=new jKanban({element:`.kanban-wrapper`,gutter:`12px`,widthBoard:`250px`,dragItems:!0,boards:e,dragBoards:!0,addItemButton:!0,buttonContent:`+ Add Item`,itemAddOptions:{enabled:!0,content:`+ Add New Item`,class:`kanban-title-button btn btn-default border-none`,footer:!1},click:e=>{let n=e,r=n.getAttribute(`data-eid`)?n.querySelector(`.kanban-text`).textContent:n.textContent,i=n.getAttribute(`data-due-date`),a=new Date,o=a.getFullYear(),s=i?`${i}, ${o}`:`${a.getDate()} ${a.toLocaleString(`en`,{month:`long`})}, ${o}`,l=n.getAttribute(`data-badge-text`),d=n.getAttribute(`data-assigned`);u.show(),t.querySelector(`#title`).value=r,t.querySelector(`#due-date`).nextSibling.value=s,$(`.kanban-update-item-sidebar`).find(c).val(l).trigger(`change`),t.querySelector(`.assigned`).innerHTML=``,t.querySelector(`.assigned`).insertAdjacentHTML(`afterbegin`,`${h(d,!1,`xs`,`1`,e.getAttribute(`data-members`))}
        <div class="avatar avatar-xs ms-1">
            <span class="avatar-initial rounded-circle bg-label-secondary">
                <i class="icon-base ti tabler-plus icon-xs text-heading"></i>
            </span>
        </div>`)},buttonClick:(e,t)=>{let n=document.createElement(`form`);n.setAttribute(`class`,`new-item-form`),n.innerHTML=`
        <div class="mb-4">
            <textarea class="form-control add-new-item" rows="2" placeholder="Add Content" autofocus required></textarea>
        </div>
        <div class="mb-4">
            <button type="submit" class="btn btn-primary btn-sm me-3 waves-effect waves-light">Add</button>
            <button type="button" class="btn btn-label-secondary btn-sm cancel-add-item waves-effect waves-light">Cancel</button>
        </div>
      `,_.addForm(t,n),n.addEventListener(`submit`,e=>{e.preventDefault();let r=Array.from(document.querySelectorAll(`.kanban-board[data-id="${t}"] .kanban-item`));_.addElement(t,{title:`<span class="kanban-text">${e.target[0].value}</span>`,id:`${t}-${r.length+1}`}),Array.from(document.querySelectorAll(`.kanban-board[data-id="${t}"] .kanban-text`)).forEach(e=>{e.insertAdjacentHTML(`beforebegin`,p())}),Array.from(document.querySelectorAll(`.kanban-item .kanban-tasks-item-dropdown`)).forEach(e=>{e.addEventListener(`click`,e=>e.stopPropagation())}),Array.from(document.querySelectorAll(`.kanban-board[data-id="${t}"] .delete-task`)).forEach(e=>{e.addEventListener(`click`,()=>{let t=e.closest(`.kanban-item`).getAttribute(`data-eid`);_.removeElement(t)})}),n.remove()}),n.querySelector(`.cancel-add-item`).addEventListener(`click`,()=>n.remove())}});n&&new PerfectScrollbar(n);let v=document.querySelector(`.kanban-container`),y=Array.from(document.querySelectorAll(`.kanban-title-board`)),b=Array.from(document.querySelectorAll(`.kanban-item`));b.length&&b.forEach(e=>{let t=`<span class="kanban-text">${e.textContent}</span>`,n=``;e.getAttribute(`data-image`)&&(n=`
              <img class="img-fluid rounded mb-2"
                   src="${l}img/elements/${e.getAttribute(`data-image`)}">
          `),e.textContent=``,e.getAttribute(`data-badge`)&&e.getAttribute(`data-badge-text`)&&e.insertAdjacentHTML(`afterbegin`,`${m(e.getAttribute(`data-badge`),e.getAttribute(`data-badge-text`))}${n}${t}`),(e.getAttribute(`data-comments`)||e.getAttribute(`data-due-date`)||e.getAttribute(`data-assigned`))&&e.insertAdjacentHTML(`beforeend`,g(e.getAttribute(`data-attachments`)||0,e.getAttribute(`data-comments`)||0,e.getAttribute(`data-assigned`)||``,e.getAttribute(`data-members`)||``))}),Array.from(document.querySelectorAll(`[data-bs-toggle="tooltip"]`)).forEach(e=>{new bootstrap.Tooltip(e)});let x=Array.from(document.querySelectorAll(`.kanban-tasks-item-dropdown`));x.length&&x.forEach(e=>{e.addEventListener(`click`,e=>{e.stopPropagation()})}),o&&o.addEventListener(`click`,()=>{a.forEach(e=>{e.value=``,e.classList.toggle(`d-none`)})}),v&&v.append(i),y&&y.forEach(e=>{e.addEventListener(`mouseenter`,()=>{e.contentEditable=`true`}),e.insertAdjacentHTML(`afterend`,f())}),Array.from(document.querySelectorAll(`.delete-board`)).forEach(e=>{e.addEventListener(`click`,()=>{let t=e.closest(`.kanban-board`).getAttribute(`data-id`);_.removeBoard(t)})}),Array.from(document.querySelectorAll(`.delete-task`)).forEach(e=>{e.addEventListener(`click`,()=>{let t=e.closest(`.kanban-item`).getAttribute(`data-eid`);_.removeElement(t)})});let S=document.querySelector(`.kanban-add-board-cancel-btn`);S&&S.addEventListener(`click`,()=>{a.forEach(e=>{e.classList.toggle(`d-none`)})}),i&&i.addEventListener(`submit`,e=>{e.preventDefault();let t=e.target.querySelector(`.form-control`).value.trim(),n=t.replace(/\s+/g,`-`).toLowerCase();_.addBoards([{id:n,title:t}]);let r=document.querySelector(`.kanban-board:last-child`);if(r){let e=r.querySelector(`.kanban-title-board`);e.insertAdjacentHTML(`afterend`,f()),e.addEventListener(`mouseenter`,()=>{e.contentEditable=`true`});let t=r.querySelector(`.delete-board`);t&&t.addEventListener(`click`,()=>{let e=t.closest(`.kanban-board`).getAttribute(`data-id`);_.removeBoard(e)})}a.forEach(e=>{e.classList.add(`d-none`)}),v&&v.append(i)}),t.addEventListener(`hidden.bs.offcanvas`,()=>{let e=t.querySelector(`.ql-editor`).firstElementChild;e&&(e.innerHTML=``)}),t&&t.addEventListener(`shown.bs.offcanvas`,()=>{Array.from(t.querySelectorAll(`[data-bs-toggle="tooltip"]`)).forEach(e=>{new bootstrap.Tooltip(e)})})})();