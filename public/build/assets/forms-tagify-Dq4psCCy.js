(function(){let e=document.querySelector(`#TagifyBasic`);e&&new Tagify(e);let t=document.querySelector(`#TagifyReadonly`);t&&new Tagify(t);let n=document.querySelector(`#TagifyCustomInlineSuggestion`),r=document.querySelector(`#TagifyCustomListSuggestion`),i=`A# .NET,A# (Axiom),A-0 System,A+,A++,ABAP,ABC,ABC ALGOL,ABSET,ABSYS,ACC,Accent,Ace DASL,ACL2,Avicsoft,ACT-III,Action!,ActionScript,Ada,Adenine,Agda,Agilent VEE,Agora,AIMMS,Alef,ALF,ALGOL 58,ALGOL 60,ALGOL 68,ALGOL W,Alice,Alma-0,AmbientTalk,Amiga E,AMOS,AMPL,Apex (Salesforce.com),APL,AppleScript,Arc,ARexx,Argus,AspectJ,Assembly language,ATS,Ateji PX,AutoHotkey,Autocoder,AutoIt,AutoLISP / Visual LISP,Averest,AWK,Axum,Active Server Pages,ASP.NET`.split(`,`);n&&new Tagify(n,{whitelist:i,maxTags:10,dropdown:{maxItems:20,classname:`tags-inline`,enabled:0,closeOnSelect:!1}}),r&&new Tagify(r,{whitelist:i,maxTags:10,dropdown:{maxItems:20,classname:``,enabled:0,closeOnSelect:!1}});let a=document.querySelector(`#TagifyUserList`),o=[{value:1,name:`Justinian Hattersley`,avatar:`https://i.pravatar.cc/80?img=1`,email:`jhattersley0@ucsd.edu`},{value:2,name:`Antons Esson`,avatar:`https://i.pravatar.cc/80?img=2`,email:`aesson1@ning.com`},{value:3,name:`Ardeen Batisse`,avatar:`https://i.pravatar.cc/80?img=3`,email:`abatisse2@nih.gov`},{value:4,name:`Graeme Yellowley`,avatar:`https://i.pravatar.cc/80?img=4`,email:`gyellowley3@behance.net`},{value:5,name:`Dido Wilford`,avatar:`https://i.pravatar.cc/80?img=5`,email:`dwilford4@jugem.jp`},{value:6,name:`Celesta Orwin`,avatar:`https://i.pravatar.cc/80?img=6`,email:`corwin5@meetup.com`},{value:7,name:`Sally Main`,avatar:`https://i.pravatar.cc/80?img=7`,email:`smain6@techcrunch.com`},{value:8,name:`Grethel Haysman`,avatar:`https://i.pravatar.cc/80?img=8`,email:`ghaysman7@mashable.com`},{value:9,name:`Marvin Mandrake`,avatar:`https://i.pravatar.cc/80?img=9`,email:`mmandrake8@sourceforge.net`},{value:10,name:`Corrie Tidey`,avatar:`https://i.pravatar.cc/80?img=10`,email:`ctidey9@youtube.com`}];function s(e){return`
    <tag title="${e.title||e.email}"
      contenteditable='false'
      spellcheck='false'
      tabIndex="-1"
      class="${this.settings.classNames.tag} ${e.class||``}"
      ${this.getAttributes(e)}
    >
      <x title='' class='tagify__tag__removeBtn' role='button' aria-label='remove tag'></x>
      <div>
        <div class='tagify__tag__avatar-wrap'>
          <img onerror="this.style.visibility='hidden'" src="${e.avatar}">
        </div>
        <span class='tagify__tag-text'>${e.name}</span>
      </div>
    </tag>
  `}function c(e){return`
    <div ${this.getAttributes(e)}
      class='tagify__dropdown__item align-items-center ${e.class||``}'
      tabindex="0"
      role="option"
    >
      ${e.avatar?`<div class='tagify__dropdown__item__avatar-wrap'>
        <img onerror="this.style.visibility='hidden'" src="${e.avatar}">
      </div>`:``}
      <div class="fw-medium">${e.name}</div>
      <span>${e.email}</span>
    </div>
  `}function l(e){return`
        <div class="${this.settings.classNames.dropdownItem} ${this.settings.classNames.dropdownItem}__addAll">
            <strong>${this.value.length?`Add remaining`:`Add All`}</strong>
            <span>${e.length} members</span>
        </div>
    `}if(a){let e=new Tagify(a,{tagTextProp:`name`,enforceWhitelist:!0,skipInvalid:!0,dropdown:{closeOnSelect:!1,enabled:0,classname:`users-list`,searchKeys:[`name`,`email`]},templates:{tag:s,dropdownItem:c,dropdownHeader:l},whitelist:o});e.on(`dropdown:select`,t).on(`edit:start`,n);function t(t){t.detail.elm.classList.contains(`${e.settings.classNames.dropdownItem}__addAll`)&&e.dropdown.selectAll()}function n({detail:{tag:t,data:n}}){e.setTagTextNode(t,`${n.name} <${n.email}>`)}}let u=document.querySelector(`#TagifyEmailList`);if(u){let e=Array.from({length:100},()=>Array.from({length:Math.floor(Math.random()*10+3)},()=>String.fromCharCode(Math.random()*26+97)).join(``)+`@gmail.com`),t=new Tagify(u,{pattern:/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,whitelist:e,callbacks:{invalid:n},dropdown:{position:`text`,enabled:1}});u.nextElementSibling.addEventListener(`click`,()=>t.addEmptyTag());function n(e){console.log(`invalid`,e.detail)}}})();