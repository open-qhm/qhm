/**
 *   jQuery Plugin: Searchable
 *   -------------------------------------------
 *   jquery.searchable.js
 *   
 *   Copyright (c) 2010 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 2010-03-01
 *   modified : 2010-03-04 bug fixed
 *   
 *   This Makes table or list searchable by text input field.
 *   
 *   Usage :
 *     $(input:text).searchable(option);
 *   
 */

(function() {

    jQuery.fn.searchable = function(selector, option) {
		var $input = this,
			$t = $(selector);
        
        option = jQuery.extend({
        	id: '', //id for multi searchable to same table
        	selector: "td",
        	bind: "keyup",
        	show: function(){this.show()},
        	hide: function(){this.hide()},
        	numbering: 0 //numbering row
        }, option);
        
        //compare funcs
        var cmp = {
        	'=': function(value){
        		return parseFloat($(option.selector, this).text()) == value;
        	},
        	'>': function(value){
        		return parseFloat($(option.selector, this).text()) > value;
        	},
        	'>=': function(value){
        		return parseFloat($(option.selector, this).text()) >= value;
        	},
        	'<': function(value){
        		return parseFloat($(option.selector, this).text()) < value;
        	},
        	'<=': function(value){
        		return parseFloat($(option.selector, this).text()) <= value;
        	}
        };
        
        //data key
        var dataKey = "rowCache"+option.id+".searchable";
        //make cache
        $t.each(function(){
        	var $tr = $(this);
			//search for
			var $target = $(option.selector, $tr),
				t_str = '';
			if ($target.length > 1) {
				for (var i = 0, len = $target.length; i < len; i++) {
					t_str += $target.eq(i).text() + ' ';
				}
			} else {
				t_str = $target.text();
			}
			
			$tr.data(dataKey, t_str);
        });

		function search() {
			var query = $input.val()
				.replace(/^\s+|\s+$/g, '')
				.replace(/\s+/g, ' ')
				.split(' ');

			if (query[0] == '') {
				reset();
				return;
			}
			var pqlen = $t.data("qlen.searchable") || 0,//previous query length
				cqlen = $input.val().length;//current query length
			$t.data("qlen.searchable", cqlen);
			
			var ncnt = 1;
			$t.each(function(){
				var $tr = $(this),
					t_str = $tr.data(dataKey);
				if (typeof cmp[query[0]] != 'undefined') {
					if (cmp[query[0]].apply($tr, [query[1]])) {
						$("td:nth-child("+option.numbering+")", $tr).text(ncnt++);
						option.show.apply($tr);
					} else {
						option.hide.apply($tr);
					}
					return;
				}


				if (pqlen < cqlen && !$tr.is(":visible")) {
					return;
				}
				
				for (var i = 0, len = query.length; i < len; i++) {
					if (!t_str.match(new RegExp(query[i], 'i'))) {
						option.hide.apply($tr);
						return;
					}
				}
				if (option.numbering > 0) {
					$("td:nth-child("+option.numbering+")", $tr).text(ncnt++);
				}
				option.show.apply($tr);
			});
		}
		
		function reset() {
			if (option.numbering > 0) {
				var ncnt = 1;
				$("td:nth-child("+option.numbering+")", $t).each(function(){
					$(this).text(ncnt++);
				});
			}
			option.show.apply($t);
		}
		
		
		$input.bind(option.bind + ".searchable", search);
		
		if ($input.is("[type=search]")) {
			$input.bind("click.searchable", function(){
				if ($input.val().length == 0) {
					reset();
				}
			});
		}
		
		if ($input.val().length > 0) {
			search();
		}
		
		return this;
    };
})(jQuery);