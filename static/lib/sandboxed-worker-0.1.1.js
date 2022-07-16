(() => {
	if(typeof(window.$) != 'function') {
		console.error('爱上一匹野马，可你的家里没有草原…');
		throw new Error('SandboxedWorker requires jQuery!');
	}

	var worker_map = [];

	addEventListener('message',(e) => {
		var k = e.data;

		if(k.purpose == 'SandboxedWorker') {
			if(worker_map[k.token]) {
				var obj = worker_map[k.token];
				obj.handleCallback(k);
			}
		}
	});

	var SandboxedWorker = function(options) {
		var get_token = () => {
			var txt = '0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
			var len = 64;
			var ret = '';
			for(var i=0;i<len;i++) {
				ret += txt[Math.floor(Math.random() * txt.length)];
			}
			return ret;
		};
		var init_callback = (att) => {
			if(typeof(this[att]) != 'function') {
				this[att] = () => {};
			}
		};

		// Token
		this.token = get_token();
		while(typeof(worker_map[this.token]) == 'object') {
			this.token = get_token();
		}

		// Reaction container
		this.container = options.container;
		if(typeof(this.container) != 'object') {
			var $container = $('<iframe style="display:none;" srcdoc=""></iframe>');
			$('body').append($container);
			this.container = $container[0];
		}
		$(this.container).attr('sandbox','allow-scripts'); // prevent window.open

		// Message callback
		// * passes original data
		this.cb_message = options.onmessage;
		init_callback('cb_message');

		// End callback
		this.cb_end = options.onend;
		init_callback('cb_end');

		// Start callback
		this.cb_start = options.onstart;
		init_callback('cb_start');

		// Console callback
		// * including info,log,warn,error,debug
		// * passes %type indicating the type and an array %data
		this.cb_console = options.onconsole;
		init_callback('cb_console');

		// Error callback
		// * When an error is really uncaught
		// * Passes error info
		this.cb_error = options.onerror;
		init_callback('cb_error');

		// Interaction callback
		// including alert, prompt and confirm
		// * passes %type indicating the type and a string %data
		this.cb_interaction = options.oninteraction;
		init_callback('cb_interaction');

		// is running
		this.isRunning = false;

		// Action!
		this.codeTemplate = "<script>(() => {addEventListener(\'error\',(e) => {try {parent.postMessage({purpose:\'SandboxedWorker\',token:\'__TOKEN__\',type:\'error\',data:{text: e.error.toString(),filename: e.filename.toString(),line: e.lineno}},\'*\');} catch(err) {}e.preventDefault();});window.message = (x) => {try {parent.postMessage({purpose:\'SandboxedWorker\',token:\'__TOKEN__\',type:\'message\',data:x},\'*\');} catch(err) {}};var console_message = (t,x) => {try {parent.postMessage({purpose:\'SandboxedWorker\',token:\'__TOKEN__\',type:\'console\',data:{type: t,data: x}},\'*\');} catch(err) {}};window.console = {};console.log = (...x) => {console_message(\'log\',x);};console.debug = (...x) => {console_message(\'debug\',x);};console.info = (...x) => {console_message(\'info\',x);};console.warn = (...x) => {console_message(\'warn\',x);};console.error = (...x) => {console_message(\'error\',x);};var interact_message = (t,x) => {try {parent.postMessage({purpose:\'SandboxedWorker\',token:\'__TOKEN__\',type:\'interaction\',data:{type: t,data: x.toString()}},\'*\');} catch(err) {}};window.alert = (x) => {interact_message(\'alert\',x);};window.prompt = (x) => {interact_message(\'prompt\',x);};window.confirm = (x) => {interact_message(\'confirm\',x);};})();</script><script>try {parent.postMessage({purpose:\'SandboxedWorker\',token:\'__TOKEN__\',type:\'start\'},\'*\');} catch(err) {}</script>__HTML_CODE__<script>try {parent.postMessage({purpose:\'SandboxedWorker\',token:\'__TOKEN__\',type:\'end\'},\'*\');} catch(err) {}</script>";
		this.run = (code,isJS = true) => {
			if(isJS && code.indexOf('</script>') != -1) {
				throw new Error('[SandboxedWorker] isJS code may not contain </script>.');
			}

			var final = this.codeTemplate.replace(/__TOKEN__/g,this.token);
			final = final.replace(/__HTML_CODE__/g,isJS ? ('<script>' + code + '</script>') : code);
			$(this.container).attr('srcdoc',final);
		};

		// Bind
		worker_map[this.token] = this;
		this.destroy = () => {
			delete worker_map[this.token];
		};

		// Callback
		this.handleCallback = (k) => {
			if(k.type == 'error') {
				this.cb_error(k.data);
			} else if(k.type == 'message') {
				this.cb_message(k.data);
			} else if(k.type == 'console') {
				this.cb_console(k.data);
			} else if(k.type == 'interaction') {
				this.cb_interaction(k.data);
			} else if(k.type == 'start') {
				this.cb_start();
				this.isRunning = true;
			} else if(k.type == 'end') {
				this.cb_end();
				this.isRunning = false;
			} else {
				console.error('[SandboxedWorker] Unknown message ' + k.type + ' posted to ' + this.token);
			}
		};

		return this;
	}

	window.SandboxedWorker = SandboxedWorker;
})();
