(function() {
	var SafeGlobalAPI = [
		"AbortController",
		"AbortSignal",
		"AggregateError",
		"Array",
		"ArrayBuffer",
		"Atomics",
		"BigInt",
		"BigInt64Array",
		"BigUint64Array",
		"Blob",
		"Boolean",
		"BroadcastChannel",
		"ByteLengthQueuingStrategy",
		"CompressionStream",
		"CountQueuingStrategy",
		"Crypto",
		"CryptoKey",
		"CustomEvent",
		"DOMException",
		"DataView",
		"Date",
		"DecompressionStream",
		"Error",
		"EvalError",
		"Event",
		"EventTarget",
		"File",
		"FinalizationRegistry",
		"Float32Array",
		"Float64Array",
		"FormData",
		"Function",
		"Headers",
		"Infinity",
		"Int16Array",
		"Int32Array",
		"Int8Array",
		"Intl",
		"Iterator",
		"JSON",
		"Map",
		"Math",
		"MessageChannel",
		"MessageEvent",
		"MessagePort",
		"NaN",
		"Navigator",
		"Number",
		"Object",
		"Performance",
		"PerformanceEntry",
		"PerformanceMark",
		"PerformanceMeasure",
		"PerformanceObserver",
		"PerformanceObserverEntryList",
		"PerformanceResourceTiming",
		"Promise",
		"Proxy",
		"RangeError",
		"ReadableByteStreamController",
		"ReadableStream",
		"ReadableStreamBYOBReader",
		"ReadableStreamBYOBRequest",
		"ReadableStreamDefaultController",
		"ReadableStreamDefaultReader",
		"ReferenceError",
		"Reflect",
		"RegExp",
		"Request",
		"Response",
		"Set",
		"String",
		"SubtleCrypto",
		"Symbol",
		"SyntaxError",
		"TextDecoder",
		"TextDecoderStream",
		"TextEncoder",
		"TextEncoderStream",
		"TransformStream",
		"TransformStreamDefaultController",
		"TypeError",
		"URIError",
		"URL",
		"URLSearchParams",
		"Uint16Array",
		"Uint32Array",
		"Uint8Array",
		"Uint8ClampedArray",
		"WeakMap",
		"WeakRef",
		"WeakSet",
		"WebAssembly",
		"WebSocket",
		"WritableStream",
		"WritableStreamDefaultController",
		"WritableStreamDefaultWriter",
		"atob",
		"btoa",
		"clearInterval",
		"clearTimeout",
		"console",
		"crypto",
		"decodeURI",
		"decodeURIComponent",
		"encodeURI",
		"encodeURIComponent",
		"escape",
		"eval",
		"fetch",
		"globalThis",
		"isFinite",
		"isNaN",
		"navigator",
		"parseFloat",
		"parseInt",
		"performance",
		"queueMicrotask",
		"setInterval",
		"setTimeout",
		"structuredClone",
		"undefined",
		"unescape",
	];
	SafeGlobalAPI.push('onmessage');
	SafeGlobalAPI.push('postMessage');
	
	/**
	 * 检验变量名
	 * 
	 * @param string str 变量名
	 * @returns 是否合法
	 */
	var isProperVarname = (str) => {
		if(typeof(str) != 'string') return false;
		if(str == '') return false;
		var alpha_list = "QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm_$";
		var num_list = "0123456789";
		for(let i=0;i<str.length;i++) {
			if(i == 0) {
				if(alpha_list.indexOf(str[i]) == -1) {
					return false;
				}
			} else {
				if(alpha_list.indexOf(str[i]) == -1) {
					if(num_list.indexOf(str[i]) == -1) {
						return false;
					}
				}
			}
		}
		return true;
	}

	/**
	 * 在 Worker 中《安全》执行内容
	 * 
	 * @param {string[]} arglist 参数列表
	 * @param {string} definition 函数主体代码
	 * @param {string} args 参数值（JSON stringifiable）
	 * @param {number} timeLimit 运行时限
	 * @returns 执行结果
	 * -> {type: 'fail', reason: 'argument_list_mismatch'|'argument_list_invalid'}
	 * -> {type: 'success', data: result}
	 * -> {type: 'error', data: [lineNumber, message], code: code}
	 * -> {type: 'tle'}
	 */
	var runInWorker = async (arglist,definition,args=[],preDefine="",timeLimit=3000) => {
		if(arglist.length != args.length) {
			return {type:'fail',reason:'argument_list_mismatch'};
		}
		for(let i=0;i<arglist.length;i++) {
			if(!isProperVarname(arglist[i])) {
				return {type:'fail',reason:'argument_list_invalid'};
			}
		}

		var secCheckList = [];
		for(let api of Object.getOwnPropertyNames(window)) {
			if(SafeGlobalAPI.indexOf(api) == -1) {
				secCheckList.push(api)
			}
		}

		var hd = 'for(let t of ' + JSON.stringify(secCheckList) + '){';
		hd += 'if(t in self) {self[t]=undefined;delete self[t];}';
		hd += '}';
		var code = 'var __target_func = function(';
		for(let i=0;i<arglist.length;i++) {
			if(i>0) code += ',';
			code += arglist[i];
		}
		code += ') {';
		code += definition + "\n";
		code += '};' + "\n";
		code += "\n";
		code += "self.onmessage = (e) => {" + "\n";
		code += "\t"+"self.postMessage(__target_func.apply(self,e.data));" + "\n";
		code += "};\n";
		code += preDefine;

		return await new Promise((resolve,reject) => {
			var worker = new Worker('data:text/javascript,' + encodeURIComponent(hd + code));

			worker.onmessage = (e) => {
				worker.terminate();
				resolve({type:'success',data:e.data});
			}

			worker.onerror = (e) => {
				worker.terminate();
				resolve({type:'error',data:[e.lineno,e.message], code: code});
				e.preventDefault();
			}

			worker.postMessage(args);

			if(timeLimit > 0) setTimeout(function() {
				worker.terminate();
				resolve({type:'tle'});
			},timeLimit);
		});
	};

	window.runInWorker = runInWorker;
	window.isProperVarname = isProperVarname;
})();
