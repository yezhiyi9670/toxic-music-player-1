(function(){
	window.active_modals = 0;

	var get_modal_id = function() {
		var txt = '0123456789abcdef';
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

	window.modal_alert = function(title = "提示", content, cancel = null) {
		var wid = get_modal_id();
		var $modal = $(
			`<div class="am-modal am-modal-alert" tabindex="-1" id="modal-${wid}">
				<div class="am-modal-dialog" tabindex="0">
					<div class="am-modal-hd">${title}</div>
					<div class="am-modal-bd">
						${content}
					</div>
					<div class="am-modal-footer">
						<span class="am-modal-btn amjs-modal-close">确定</span>
					</div>
				</div>
			</div>`
		);
		active_modals++;
		$('body').append($modal);
		$('#modal-' + wid + ' .amjs-modal-close').click(function(){
			cancel();
			setTimeout(function(){destroy_modal(wid)},500);
		});

		$modal.children().on('keypress',function(e) {
			if(e.which == 32 || e.which == 13) {
				$modal.modal('close');
				setTimeout(function(){destroy_modal(wid)},500);
				cancel();
			}
		});

		$modal.modal({onCancel:()=>{
			setTimeout(function(){destroy_modal(wid)},500);
			cancel();
		},closeViaDimmer:false});
		open_modal($modal);
		return wid;
	}
	window.modal_alert_p = function(title = "提示", content) {
		return new Promise((resolve,reject) => {
			modal_alert(title,content,() => {resolve()});
		});
	}

	window.modal_confirm = function(title = "确认", content, confirm = null, cancel = null,negativeText="取消",positiveText="确定") {
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
				confirm();
				$modal.modal('close');
				setTimeout(function(){destroy_modal(wid)},500);
			}
		});
		$('#modal-' + wid + ' .amjs-modal-close').click(function(){
			setTimeout(function(){destroy_modal(wid)},500);
		});
		$('#modal-' + wid + ' .amjs-modal-cancel').click(cancel);
		$('#modal-' + wid + ' .amjs-modal-confirm').click(confirm);
		$modal.modal({onCancel:()=>{
			setTimeout(function(){destroy_modal(wid)},500);
			cancel();
		},closeViaDimmer:false});

		open_modal($modal);
		return wid;
	}
	window.modal_confirm_p = function(title = "确认", content,negativeText="取消",positiveText="确定") {
		return new Promise((resolve,reject) => {
			modal_confirm(title,content,() => {resolve(true)},() => {resolve(false)},negativeText,positiveText);
		});
	}

	window.modal_prompt = function(title = "输入", content, prefill = "", confirm = null, cancel = null, type = "text") {
		var wid = get_modal_id();
		var $modal = $(
			`<div class="am-modal am-modal-alert" tabindex="-1" id="modal-${wid}">
				<div class="am-modal-dialog" tabindex="0">
					<div class="am-modal-hd">${title}</div>
					<div class="am-modal-bd">
						${content}
						<input type="${type}" class="am-modal-prompt-input amjs-modal-input" style="width:unset">
					</div>
					<div class="am-modal-footer">
						<span class="am-modal-btn amjs-modal-close amjs-modal-cancel">取消</span>
						<span class="am-modal-btn amjs-modal-close amjs-modal-confirm">确定</span>
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
			}
		});
		$('#modal-' + wid + ' .amjs-modal-close').click(function(){
			setTimeout(function(){destroy_modal(wid)},500);
		});
		$('#modal-' + wid + ' .amjs-modal-cancel').click(cancel);
		$('#modal-' + wid + ' .amjs-modal-confirm').click(function(){
			confirm($('#modal-' + wid + ' .amjs-modal-input').val());
		});
		$modal.modal({onCancel:()=>{
			setTimeout(function(){destroy_modal(wid)},500);
			cancel();
		},closeViaDimmer:false});

		open_modal($modal);
		$('#modal-' + wid + ' .amjs-modal-input').focus();
		$('#modal-' + wid + ' .amjs-modal-input').val(prefill);
		$('#modal-' + wid + ' .amjs-modal-input').select();
		return wid;
	}
	window.modal_prompt_p = function(title = "输入", content, prefill = "", type = "text") {
		return new Promise((resolve,reject) => {
			modal_prompt(title,content,prefill,(val) => {resolve(val)},() => {resolve(undefined)},type);
		});
	}
	window.modal_confirm_by_input = async function(action, prom, ans) {
		var inp = await modal_prompt_p('危',`你即将${action}<br><span class="allow-select">请键入${prom} ` + escapeXml(ans) + '</span>');
		if(inp === undefined) return false;
		if(inp != ans) {
			modal_alert('操作已取消','键入内容错误');
			return false;
		}
		return true;
	}

	window.modal_promptarea = function(title = "输入", content, prefill = "", confirm = null, cancel = null) {
		var wid = get_modal_id();
		var $modal = $(
			`<div class="am-modal am-modal-alert" tabindex="-1" id="modal-${wid}">
				<div class="am-modal-dialog" tabindex="0">
					<div class="am-modal-hd">${title}</div>
					<div class="am-modal-bd">
						${content}
						<textarea class="am-modal-prompt-input amjs-modal-input" style="width:100%;height:200px;font-family:'Consolas','Source Code Pro','Courier New';"></textarea>
					</div>
					<div class="am-modal-footer">
						<span class="am-modal-btn amjs-modal-close amjs-modal-cancel">取消</span>
						<span class="am-modal-btn amjs-modal-close amjs-modal-confirm">确定</span>
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
			confirm($('#modal-' + wid + ' .amjs-modal-input').val());
		});
		$modal.modal({onCancel:()=>{
			setTimeout(function(){destroy_modal(wid)},500);
			cancel();
		},closeViaDimmer:false});

		open_modal($modal);
		$('#modal-' + wid + ' .amjs-modal-input').focus();
		$('#modal-' + wid + ' .amjs-modal-input').val(prefill);
		$('#modal-' + wid + ' .amjs-modal-input').select();
		return wid;
	}
	window.modal_promptarea_p = function(title = "输入", content, prefill = "") {
		return new Promise((resolve,reject) => {
			modal_promptarea(title,content,prefill,(val) => {resolve(val)},() => {resolve(undefined)});
		});
	}

	window.modal_loading = function(content = "加载中") {
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
