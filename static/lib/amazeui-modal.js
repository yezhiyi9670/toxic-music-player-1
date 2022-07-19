(function(){
	window.active_modals = 0;

	var get_modal_id = function() {
		var txt = '0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
		var len = 40;
		var ret = '';
		for(var i=0;i<len;i++) {
			ret += txt[Math.floor(Math.random() * txt.length)];
		}
		return ret;
	}
	var open_modal = function($modal) {
		// $modal.modal({'closeViaDimmer':false});
		$modal.modal('open');
		$modal.children().focus();
	}

	window.destroy_modal = function(wid) {
		// $('#modal-' + wid + ' + .am-dimmer').remove();
		$('#modal-' + wid).remove();
		active_modals--;
	}
	window.close_modal = function(wid) {
		$modal = $('#modal-' + wid);
		$modal.modal('close');
		setTimeout(()=>{destroy_modal(wid)},500);
	}

	window.modal_alert = function(title = null, content, cancel = null) {
		if(!title) title = LNG('ui.tips');
		var wid = get_modal_id();
		var $modal = $(
			`<div class="am-modal am-modal-alert" tabindex="-1" id="modal-${wid}">
				<div class="am-modal-dialog" tabindex="0">
					<div class="am-modal-hd">${title}</div>
					<div class="am-modal-bd">
						${content}
					</div>
					<div class="am-modal-footer">
						<span class="am-modal-btn amjs-modal-close">${LNG('ui.confirm')}</span>
					</div>
				</div>
			</div>`
		);
		active_modals++;
		$('body').append($modal);
		$('#modal-' + wid + ' .amjs-modal-close').click(function(){
			if(cancel) cancel();
			setTimeout(function(){destroy_modal(wid)},500);
		});

		$modal.children().on('keypress',function(e) {
			if(e.which == 32 || e.which == 13) {
				$modal.modal('close');
				setTimeout(function(){destroy_modal(wid)},500);
				if(cancel) cancel();
				e.preventDefault();
			}
		});

		$modal.modal({onCancel:()=>{
			setTimeout(function(){destroy_modal(wid)},500);
			if(cancel) cancel();
		},closeViaDimmer:false});
		open_modal($modal);
		return wid;
	}
	window.modal_alert_p = function(title = null, content) {
		if(!title) title = LNG('ui.tips');
		return new Promise((resolve,reject) => {
			modal_alert(title,content,() => {resolve()});
		});
	}

	window.modal_confirm = function(title = null, content, confirm = null, cancel = null,negativeText=null,positiveText=null) {
		if(!title) title = LNG('ui.tips');
		if(!negativeText) negativeText = LNG('ui.cancel');
		if(!positiveText) positiveText = LNG('ui.confirm');
		var wid = get_modal_id();
		var $modal = $(
			`<div class="am-modal am-modal-alert" tabindex="-1" id="modal-${wid}">
				<div class="am-modal-dialog" tabindex="0">
					<div class="am-modal-hd">${title}</div>
					<div class="am-modal-bd">
						${content}
					</div>
					<div class="am-modal-footer">
						<span class="am-modal-btn amjs-modal-close amjs-modal-cancel">${negativeText}</span>
						<span class="am-modal-btn amjs-modal-close amjs-modal-confirm">${positiveText}</span>
					</div>
				</div>
			</div>`
		);
		active_modals++;
		$('body').append($modal);
		$modal.children().on('keypress',function(e){
			if(e.which == 32 || e.which == 13) {
				if(confirm) confirm();
				$modal.modal('close');
				setTimeout(function(){destroy_modal(wid)},500);
				e.preventDefault();
			}
		});
		$('#modal-' + wid + ' .amjs-modal-close').click(function(){
			setTimeout(function(){destroy_modal(wid)},500);
		});
		$('#modal-' + wid + ' .amjs-modal-cancel').click(cancel);
		$('#modal-' + wid + ' .amjs-modal-confirm').click(confirm);
		$modal.modal({onCancel:()=>{
			setTimeout(function(){destroy_modal(wid)},500);
			if(cancel) cancel();
		},closeViaDimmer:false});

		open_modal($modal);
		return wid;
	}
	window.modal_confirm_p = function(title = null, content,negativeText=null,positiveText=null) {
		if(!title) title = LNG('ui.tips');
		if(!negativeText) negativeText = LNG('ui.cancel');
		if(!positiveText) positiveText = LNG('ui.confirm');
		return new Promise((resolve,reject) => {
			modal_confirm(title,content,() => {resolve(true)},() => {resolve(false)},negativeText,positiveText);
		});
	}

	window.modal_prompt = function(title = null, content, prefill = "", confirm = null, cancel = null, type = "text") {
		if(!title) title = LNG('ui.tips');
		var wid = get_modal_id();
		var $modal = $(
			`<div class="am-modal am-modal-alert" tabindex="-1" id="modal-${wid}">
				<div class="am-modal-dialog" tabindex="0">
					<div class="am-modal-hd">${title}</div>
					<div class="am-modal-bd">
						${content}
						<input type="${type}" class="am-modal-prompt-input amjs-modal-input" spellcheck="false">
					</div>
					<div class="am-modal-footer">
						<span class="am-modal-btn amjs-modal-close amjs-modal-cancel">${LNG('ui.cancel')}</span>
						<span class="am-modal-btn amjs-modal-close amjs-modal-confirm">${LNG('ui.confirm')}</span>
					</div>
				</div>
			</div>`
		);
		active_modals++;
		$('body').append($modal);
		$modal.children().on('keypress',function(e){
			if(e.which == 13) {
				confirm($('#modal-' + wid + ' .amjs-modal-input').val());
				$modal.modal('close');
				setTimeout(function(){destroy_modal(wid)},500);
				e.preventDefault();
			}
		});
		$('#modal-' + wid + ' .amjs-modal-close').click(function(){
			setTimeout(function(){destroy_modal(wid)},500);
		});
		$('#modal-' + wid + ' .amjs-modal-cancel').click(cancel);
		$('#modal-' + wid + ' .amjs-modal-confirm').click(function(){
			if(confirm) confirm($('#modal-' + wid + ' .amjs-modal-input').val());
		});
		$modal.modal({onCancel:()=>{
			setTimeout(function(){destroy_modal(wid)},500);
			if(cancel) cancel();
		},closeViaDimmer:false});

		open_modal($modal);
		$('#modal-' + wid + ' .amjs-modal-input').focus();
		$('#modal-' + wid + ' .amjs-modal-input').val(prefill);
		$('#modal-' + wid + ' .amjs-modal-input').select();
		return wid;
	}
	window.modal_prompt_p = function(title = null, content, prefill = "", type = "text") {
		if(!title) title = LNG('ui.tips');
		return new Promise((resolve,reject) => {
			modal_prompt(title,content,prefill,(val) => {resolve(val)},() => {resolve(undefined)},type);
		});
	}
	window.modal_confirm_by_input = async function(action, prom, ans) {
		var inp = await modal_prompt_p(LNG('ui.danger'),LNG('ui.prompt_confirm',action,prom,ans));
		if(inp === undefined) return false;
		if(inp != ans) {
			modal_alert(LNG('ui.prompt_fail'),LNG('ui.prompt_fail.tips'));
			return false;
		}
		return true;
	}

	window.modal_promptarea = function(title = null, content, prefill = "", confirm = null, cancel = null) {
		if(!title) title = LNG('ui.tips');
		var wid = get_modal_id();
		var $modal = $(
			`<div class="am-modal am-modal-alert" tabindex="-1" id="modal-${wid}">
				<div class="am-modal-dialog" tabindex="0">
					<div class="am-modal-hd">${title}</div>
					<div class="am-modal-bd">
						${content}
						<textarea class="am-modal-prompt-input amjs-modal-input" style="width:100%;height:200px;font-family:'Consolas','Source Code Pro','Courier New';" spellcheck="false"></textarea>
					</div>
					<div class="am-modal-footer">
						<span class="am-modal-btn amjs-modal-close amjs-modal-cancel">${LNG('ui.cancel')}</span>
						<span class="am-modal-btn amjs-modal-close amjs-modal-confirm">${LNG('ui.confirm')}</span>
					</div>
				</div>
			</div>`
		);
		active_modals++;
		$('body').append($modal);
		$('#modal-' + wid + ' .amjs-modal-close').click(function(){
			setTimeout(function(){destroy_modal(wid)},500);
		});
		$('#modal-' + wid + ' .amjs-modal-cancel').click(cancel);
		$('#modal-' + wid + ' .amjs-modal-confirm').click(function(){
			if(confirm) confirm($('#modal-' + wid + ' .amjs-modal-input').val());
		});
		$modal.modal({onCancel:()=>{
			setTimeout(function(){destroy_modal(wid)},500);
			if(cancel) cancel();
		},closeViaDimmer:false});

		open_modal($modal);
		$('#modal-' + wid + ' .amjs-modal-input').focus();
		$('#modal-' + wid + ' .amjs-modal-input').val(prefill);
		$('#modal-' + wid + ' .amjs-modal-input').select();
		return wid;
	}
	window.modal_promptarea_p = function(title = null, content, prefill = "") {
		if(!title) title = LNG('ui.tips');
		return new Promise((resolve,reject) => {
			modal_promptarea(title,content,prefill,(val) => {resolve(val)},() => {resolve(undefined)});
		});
	}

	window.modal_loading = function(content = null) {
		if(!content) content = LNG('ui.wait');
		var wid = get_modal_id();
		var $modal = $(
			`<div class="am-modal am-modal-loading am-modal-no-btn" tabindex="-1" id="modal-${wid}">
				<div class="am-modal-dialog" tabindex="0">
					<div class="am-modal-hd">${content}</div>
					<div class="am-modal-bd">
						<span class="am-icon-spinner am-icon-spin"></span>
					</div>
				</div>
			</div>`
		);
		active_modals++;
		$('body').append($modal);

		$modal.modal({onCancel:()=>{
			throw new Error('Cannot close loading modal.');
		},closeViaDimmer:false});
		open_modal($modal);
		return wid;
	}

	window.close_modal = function(wid) {
		$('#modal-' + wid).modal('close');
		setTimeout(function(){
			destroy_modal(wid);
		},500);
	}
})();
