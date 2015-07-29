/**
 *   JQuery Plugin exnote
 *   -------------------------------------------
 *   jquery.exnote.js
 *   
 *   Copyright (c) 2009 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 2012-11
 *   modified : 
 *   
 *   テキストエリアに様々な拡張機能を付けます。
 *   現在のカーソル位置や選択範囲を監視し、その位置に任意の文字列を挿入可能です。
 *   
 *   Usage :
 *     初期化：
 *     $(el).exnote(options);
 *
 *     各種機能呼び出し：
 *     $(el).exnote(functionName, param);
 *
 *   @require jQuery
 *   @require bootstrap
 */

!function($){

	/** ExpansionNote Class difinition */
	var ExpansionNote = function(element, options) {
		var $$ = $(element), self = this;
		
		options = options || {};
		options.autogrow = options.autogrow || true;
		options.useHistory = options.useHistory || true;
		
		for (var key in options) {
			this[key] = options[key];
		}
		
		this.element = element;
		this.isTextInput = $$.is("input:text");

		//style
		if (typeof options.css !== "undefined" && $.isPlainObject(options.css)) {
			$$.css(options.css);
		}
		
		//set event

		if (options.autogrow) {
			this.autoGrow();
		}
		if (options.useHistory) {
			$$.on("insert.exnote focus.exnote mousedown.exnote keydown.exnote", function(){$$.exnote("setHistory")});
			$$.on("keydown.exnote", function(e){
				if (e.which == 90) {
					if (e.shiftKey && e.metaKey) {
						self.redo();
						return false;
					}
					else if (e.metaKey) {
						self.undo();
						return false;
					}
				}

			});
			this.setHistory();
		}
		

		ExpansionNote.total = ++ExpansionNote.total;

		return;
	}
	
	/** static field */
	var statics = {
	
		total: 0,//total number of instance
		browser: 2,//browser type (1: IE 2:modern)

		getBrowser: function() {
			return ExpansionNote.browser;
		}
		
	};
	
	for (var key in statics) {
		ExpansionNote[key] = statics[key];
	}
	
	
	/** instance method */
	ExpansionNote.prototype = {
		
		constructor: ExpansionNote,
		
		element: null,
		isTextInput: false,
		
		//replace selection when text insert
		insertMode: "replace", //replace | insert
		
		history: [],
		currentHistoryIndex: 0,
		maxHistoryLength: 100,

		selectLength: 0,
		endLength: 0,
		startLength: 0,
		startLength2: 0,
		scrollPos: 0,

		//監視する入力リスト
		watchList: {},
		
		agWrapperClass: "exnote-agwrapper",
		agPreClass: "exnote-agshadow",
		autoGrowing: false,

		setValue: function(value){
			$(this.element).val(value);
		},
		getValue: function(){
			return $(this.element).val();
		},
		
		setRange: function(position, selectLength){
			var value = this.getValue();
			this.startLength = position;
			this.selectLength = selectLength;
			this.endLength = value.length - (position + selectLength);
		},
		getRange: function(){
			return {position: this.startLength, length: this.selectLength};
		},

		attachFocus: function(ln, slen){
			slen = slen || 0;
			var el = this.element;
			var browser = ExpansionNote.getBrowser();
			if (browser == 1) {
				//TODO: できればslen使って選択状態に
				var range  = el.createTextRange();
				var tx = el.value.substr(0, ln);
				var pl = tx.split(/\n/);
				range.collapse(true);
				range.moveStart("character",ln-pl.length+1);
				range.text = range.text+"";
				range.collapse(false);
				range.select();
				el.focus();
			} else if (browser == 2) {
				setTimeout(function(){
					el.setSelectionRange(ln, ln + slen);
					el.focus();
					el.scrollTop = this.scrollPos;
				}, 25);
			}
			
		},
		
		/**
		 * Go to line-head at current position
		 */
		moveToLinehead: function(){
			var value = this.getValue(),
				range = this.getRange();
			
			if (value.length > 0) {
				while (range.position > 0 && value.substr(range.position - 1, 1) !== "\n") {
					range.position--;
					if (range.length > 0) {
						range.length++;
					}
				}
				this.setRange(range.position, range.length);
			}
		},
		
		/**
		 * Go to next line at current position
		 */
		moveToNextLine: function(){
			var value = this.getValue(),
				maxLength = value.length,
				range = this.getRange();
			
			if (value.length > 0) {
				while (range.position < maxLength && value.substr(range.position - 1, 1) !== "\n") {
					range.position++;
					if (range.length > 0) {
						range.length--;
					}
				}
				if (range.position === maxLength) {
					this.setValue(value += "\n");
					range.position++;
					range.length = 0;
				}
				this.setRange(range.position, range.length);
			}
			
		},
		
		setHistory: function(){
			var value = this.getValue(),
				last = this.history.length > 0 ? this.history[this.currentHistoryIndex].value : false;
			
			if (last !== false && value == last) {
				return false;
			}
		
			var diff = this.history.length - this.currentHistoryIndex;
			if (diff > 1) {
				this.history.splice(this.currentHistoryIndex, diff);
			}
		
			this.history.push({
				value: value,
				caret: this.getRange()
			});
			
			//はみ出たら古いものを消す
			diff = this.history.length - this.maxHistoryLength;
			if (diff > 0) {
				this.history.splice(0, diff);
			}
			
			//update current index
			this.currentHistoryIndex = this.history.length - 1;
			
			return true;
		},
		applyHistory: function(i){
			var lastIndex = this.history.length - 1;
			i = i > lastIndex ? lastIndex : i;
			i = i >= 0 ? i : 0;
			
			if (lastIndex >= 0) {
				var hist = this.history[i];
				
				this.currentHistoryIndex = i;
				this.setValue(hist.value);
				this.setRange(hist.caret.position, hist.caret.length);
				this.attachFocus(hist.caret.position, hist.caret.length);
				return true;
			}
			return false;
		},
		undo: function(){
			if ( ! this.undoable()) return;
			if (this.currentHistoryIndex + 1 === this.history.length) {
				this.setHistory();
			}
			this.applyHistory(this.currentHistoryIndex-1);
		},
		undoable: function(){
			return this.currentHistoryIndex > 0;
		},
		redo: function(){
			if ( ! this.redoable()) return;
			this.applyHistory(this.currentHistoryIndex+1);
		},
		redoable: function(){
			return this.currentHistoryIndex + 1 < this.history.length;
		},
		
		adjustSelection: function(){
			var value = this.getValue(),
				maxLength = value.length;
			
			var range = this.getRange();
			if (value.length > 0) {
				if (value.substr(range.position + range.length - 1, 1) == "\n" && value.substr(range.position, 1) !== "\n" && value.length !== range.position) {
					range.position++;
				}
				while (range.position > 0 && value.substr(range.position - 1, 1) !== "\n") {
					range.position--;
					range.length++;
				}
				
				if (value.substr(range.position+range.length - 1, 1) !== "\n") {
					while (range.position + range.length < maxLength && value.substr(range.position + range.length, 1) !== "\n") {
						range.length++;
					}
				}
			}
			
			this.setRange(range.position, range.length);
		},
		
		getPos: function() {
			var el = this.element;
			var ret = 0,
				browser = ExpansionNote.getBrowser();
			if (browser == 1) {
				if (this.isTextInput) {
					var range = document.selection.createRange();
					range.moveEnd("textedit");
					this.selectLength = range.text.length;
					return;
				}
			
				var srange = document.selection.createRange();
				this.selectLength = srange.text.length;
				var range = el.createTextRange();

				var all = range.text.length;
				var all2 = el.value.length;
				var ol = srange.offsetLeft, ot = srange.offsetTop;

				try {
					range.moveToPoint(ol, ot);
				} catch(e) {
					range.move('textedit');
				}
				range.moveEnd("textedit");
		
				this.endLength    = range.text.length;
				this.startLength  = all - this.endLength;
				this.startLength2 = all2 - this.endLength;
				
			}
			else if (browser==2) {
				this.startLength  = el.selectionStart;
				this.endLength    = el.value.length - el.selectionEnd;
				this.selectLength = el.selectionEnd - this.startLength;
			}
		},
		
		trigger: function(event){
			var key = String.fromCharCode(event.which);
			if (typeof this.watchList[key] !== "undefined") {
				this.watchList[key].call(this, event);
			}
		},
		setWatch: function(watch){
			watch.character = watch.character.substr(0,1);
			watch.func = watch.func || function(){};
			this.watchList[watch.character] = watch.func;
		},
		
		insert: function(value, caret) {
			caret = $.extend({offset: 0, length: 0}, caret);
			if (this.insertMode === "replace") {
				this.removeSelection();
			}
		
			var el = this.element;
			var browser = ExpansionNote.getBrowser();
		
			if (browser == 2) {
				this.scrollPos = el.scrollTop;
			}
			
			var itext = el.value;
			var slen = 0;
		
			if (browser == 1 && isTextInput){
									
				var range = el.createTextRange();
				range.collapse();
				range.moveStart("character", el.value.length - this.selectLength);
				range.text = value;
				return;
			
			}
			else if (browser) {
				var len = this.startLength2 == itext.length? this.startLength2: this.startLength;
				var click_s=itext.substr(0, len);
				var click_m=itext.substr(this.startLength, this.selectLength);
				var click_e=itext.substr(this.startLength+ this.selectLength, this.endLength);
				if (click_s == '' && click_m == '' && click_e == '') {
					click_e = itext;
				}
				el.value=click_s + value + click_m + click_e;

				// for IE　最後の改行挿入対応
				if ('v'=='\v') {
					var sarr = value.split('\n');
					if ((sarr.length - 1) > 0) {
						slen = sarr.length;
						slen = (sarr[slen - 1] == '') ? slen - 2 : slen - 1;
					}
				}
			}

			this.attachFocus(value.length + slen + len + this.selectLength + caret.offset, caret.length);

			$(el).triggerHandler("insert");
		},
		
		getSelectedText: function(){
			
			if (this.selectLength > 0) {
				return $(this.element).val().substr(this.startLength, this.selectLength);
			}
			else {
				return "";
			}
			
		},
		
		removeSelection: function(){
			
			if (this.selectLength > 0) {
				var value = $(this.element).val();
				var newvalue = value.substr(0, this.startLength) + value.substr(this.startLength + this.selectLength, this.endLength);
				$(this.element).val(newvalue);
				
				this.selectLength = 0;
				
			}
			return;			
		},

		
		autoGrow: function(grow) {
			grow = grow || true;
			
			//autoGrow
			if (grow) {

				if (this.autoGrowing) {
					return;
				}
				
				
				var d = $(this.element).wrap('<div class="'+ this.agWrapperClass +'"></div>');
		        var p = $('<pre class="'+ this.agPreClass +'"></pre>').text(d.val()).insertAfter(d);
		        d.on('insert.extnote keyup.exnote change.exnote click.exnote focus.exnote',function(e){
		            p.text(d.val());
		        });

				if (this.css) {
					var css = $.extend({}, this.css);
					css.minHeight = css.height;
					delete css.height;
					p.css(css);
				}
		        
			}
			//remove autoGrow
			else {
			
				var $$ = $(this.element),
					$wrapper = $$.parent();
				
				$wrapper.after($$).remove();

			}

	        this.autoGrowing = grow;
		},
		
		//TODO: exnote解除メソッド
		
	};


    $.fn.exnote = function(options) {
    	var param = null;
    	if (arguments.length > 1) {
	    	param = arguments[1];
    	}
		return this.each(function(){
			var $$ = $(this)
				, data = $$.data('exnote');
			if ( ! data || typeof data == "string") {
				data = new ExpansionNote(this, options);
				$$.data('exnote', data);
			}
			if (typeof options == 'string') {
				data[options].call(data, param);
			}
		
		});
	};

	/* !on ready */

	$(function(){
	
		//ブラウザを判定する
		var browser = 2;
		var $textarea = $("textarea[id]");
		var id;
		if ($textarea.length > 0) {
			id = $textarea.eq(0).attr("id");
			if (document.getElementById(id).setSelectionRange) {
				
			} else if (document.selection.createRange) {
				browser = 1;//IE
			}
		}	
		ExpansionNote.browser = browser;
		
		$("[data-exnote=onready]").exnote();
	});


}(window.jQuery);
