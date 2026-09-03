$(function(){var e=$(`#document-Status`),t=$(`#document-Pause`),n=$(`#document-Resume`),r=$(`#document-Elapsed`),i=$(`#document-Destroy`),a=$(`#document-Init`);if(e.length){var o=5e3;$(document).on(`idle.idleTimer`,function(t,n,r){e.val(function(e,t){return t+`Idle @ `+moment().format()+` 
`}).removeClass(`alert-success`).addClass(`alert-warning`)}),$(document).on(`active.idleTimer`,function(t,n,r,i){e.val(function(e,t){return t+`Active [`+i.type+`] [`+i.target.nodeName+`] @ `+moment().format()+` 
`}).addClass(`alert-success`).removeClass(`alert-warning`)}),t.on(`click`,function(){return $(document).idleTimer(`pause`),e.val(function(e,t){return t+`Paused @ `+moment().format()+` 
`}),$(this).blur(),!1}),n.on(`click`,function(){return $(document).idleTimer(`resume`),e.val(function(e,t){return t+`Resumed @ `+moment().format()+` 
`}),$(this).blur(),!1}),r.on(`click`,function(){return e.val(function(e,t){return t+`Elapsed (since becoming active): `+$(document).idleTimer(`getElapsedTime`)+` 
`}),$(this).blur(),!1}),i.on(`click`,function(){return $(document).idleTimer(`destroy`),e.val(function(e,t){return t+`Destroyed: @ `+moment().format()+` 
`}).removeClass(`alert-success`).removeClass(`alert-warning`),$(this).blur(),!1}),a.on(`click`,function(){return $(document).idleTimer({timeout:o}),e.val(function(e,t){return t+`Init: @ `+moment().format()+` 
`}),$(document).idleTimer(`isIdle`)?e.removeClass(`alert-success`).addClass(`alert-warning`):e.addClass(`alert-success`).removeClass(`alert-warning`),$(this).blur(),!1}),e.val(``),$(document).idleTimer(o),$(document).idleTimer(`isIdle`)?e.val(function(e,t){return t+`Initial Idle State @ `+moment().format()+` 
`}).removeClass(`alert-success`).addClass(`alert-warning`):e.val(function(e,t){return t+`Initial Active State @ `+moment().format()+` 
`}).addClass(`alert-success`).removeClass(`alert-warning`)}var s=$(`#element-Status`),c=$(`#element-Reset`),l=$(`#element-Remaining`),u=$(`#element-LastActive`),d=$(`#element-State`);s.length&&(s.on(`idle.idleTimer`,function(e,t,n){e.stopPropagation(),s.val(function(e,t){return t+`Idle @ `+moment().format()+` 
`}).removeClass(`alert-success`).addClass(`alert-warning`)}),s.on(`active.idleTimer`,function(e){e.stopPropagation(),s.val(function(e,t){return t+`Active @ `+moment().format()+` 
`}).addClass(`alert-success`).removeClass(`alert-warning`)}),c.on(`click`,function(){return s.idleTimer(`reset`).val(function(e,t){return t+`Reset @ `+moment().format()+` 
`}),$(`#element-Status`).idleTimer(`isIdle`)?s.removeClass(`alert-success`).addClass(`alert-warning`):s.addClass(`alert-success`).removeClass(`alert-warning`),$(this).blur(),!1}),l.on(`click`,function(){return s.val(function(e,t){return t+`Remaining: `+s.idleTimer(`getRemainingTime`)+` 
`}),$(this).blur(),!1}),u.on(`click`,function(){return s.val(function(e,t){return t+`LastActive: `+s.idleTimer(`getLastActiveTime`)+` 
`}),$(this).blur(),!1}),d.on(`click`,function(){return s.val(function(e,t){return t+`State: `+($(`#element-Status`).idleTimer(`isIdle`)?`idle`:`active`)+` 
`}),$(this).blur(),!1}),s.val(``).idleTimer(3e3),s.idleTimer(`isIdle`)?s.val(function(e,t){return t+`Initial Idle @ `+moment().format()+` 
`}).removeClass(`alert-success`).addClass(`alert-warning`):s.val(function(e,t){return t+`Initial Active @ `+moment().format()+` 
`}).addClass(`alert-success`).removeClass(`alert-warning`))});