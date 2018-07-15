/*
 tools
*/

// IE9 で、SWFUの中で画面遷移すると、
// onready function に来た時点で、selectionStart が0になる現象対策

var parentMsgSelectionStart = 0;

/**
* template.htmlの</body>の直前で実行される。
* 見た目の調整などを行っている
*/
function lastscript(){

	//Windowのロックについて
	var l = $.cookie('locking');
	if(l == 'true'){
		$("a#window_lock img")
			.attr({
				src:'images/lock.png',
				title:'ロック解除'
			});
	}
	
	if(parent.document.getElementById('msg')){
	}
	else{
		$("body").append('<style>.editmode{display:none}</style>');
	}
	
	if(parent.document.getElementById('wrapper')){
		$("#qhmtop").css('display','none');
	}
	else{
		$("#swfutop").css('display','none');
	}

	if (parent.document.getElementById('msg').setSelectionRange)
	{
		parentMsgSelectionStart = parent.document.getElementById('msg').selectionStart;
	}
}

function display_buttons() {
	if(parent.document.getElementById('msg')){
	}
	else{
		$("body").append('<style>.editmode{display:none}</style>');
	}
}

function insert_cmd(insert){
	var el = parent.document.getElementById('msg');
	el.focus();

	var l = $.cookie('locking');
	if(l == 'true'){
		insert = insert+"\n";
	}

	//ブラウザを判定する
	var browser = 2;
	if (el.length > 0) {
		if( el.setSelectionRange ){
			
		} else if( parent.document.selection.createRange ){
			browser=1;
		}
	}	


	if (browser == 1) { // IE
		el.focus();
		sel = parent.document.selection.createRange();
		sel.text = insert;
	} else {

		var body = el.value;
		var at = parentMsgSelectionStart;
		var tmp = body.substr(0, at);
		 
		el.value = tmp + insert + body.substr(at, body.length);
		var cursor = insert.length + at;
		el.setSelectionRange(cursor, cursor);
	}
		
	if(l != 'true'){
		self.parent.tb_remove();
	}
	else{
		$('#cmd_msg').show();
		setTimeout(function(){$('#cmd_msg').hide()}, 2000);

	}
}

function insert_val(id){

	var val = document.getElementById(id).value;

	insert_cmd(val);
}

function toggle_lock(){
	
	var l = $.cookie('locking');
	
	if(l == 'true'){
		$("a#window_lock img")
			.attr({
				src:'images/unlock.png',
				title:'ロック'
			});
		$.cookie('locking', 'false');
	}
	else{
		$("a#window_lock img")
			.attr({
				src:'images/lock.png',
				title:'ロック解除'
			});
		$.cookie('locking', 'true');
	}

}

function disp(){
	if(window.confirm('本当に削除しますか？')){
		return true;
	}
	else{
		return false;
	}
}

function confirm_page_chg(msg){
	var el = document.getElementById('change_page_name');
	var npage = el.value;

	if(window.confirm('ページ名を「'+msg+'」から、「'+npage+'」に変更しますか？')){
		return true;
	}
	else{
		return false;
	}
}

function confirm_page_set(){
	var el = document.getElementById('new_page');
	var msg = el.value;
	
	if(window.confirm("ページ名に「"+msg+"」をセットします。\n"+msg+"編集中に切り替えます")){
		return true;
	}
	else{
		return false;
	}
}

// !onload function 
$(function(){
	// !File Upload
    $('#file_upload').fileUploadUI({
        uploadTable: $('#files'),
        downloadTable: $('#files'),
        buildUploadRow: function (files, index) {
            return $('<tr><td>' + files[index].name + '<\/td>' +
                    '<td class="file_upload_progress"><div><\/div><\/td>' +
                    '<td class="file_upload_cancel">' +
                    '<button class="ui-state-default ui-corner-all" title="Cancel">' +
                    '<span class="ui-icon ui-icon-cancel">Cancel<\/span>' +
                    '<\/button><\/td><\/tr>');
        },
        buildDownloadRow: function (file) {
        	var preview = '', link, target;
        	if (file.preview.length > 0) {
        		var x = file.preview.split("x").shift(),
        			y = file.preview.split("x").pop();
        		preview = '<img src="'+ file.path +'" width="'+ x +'" height="'+ y +'" alt="'+ file.text +'" />';
        	}
        	if (typeof file.id != "undefined") {
        		link = "view.php?id=" + file.id;
        		target = "";
        	} else {
        		link = file.path;
        		target = ' target="_blank"';
        	}
        	
        	display_buttons();
        	
            return $('<tr><td class="file_preview">'+ preview +'</td><td><a href="'+ link +'"'+ target +'>' + file.name + '</a><br />'+ file.size +'<\/td><td>'+file.buttons+'</td><\/tr>');
        },
        onDragOver: function() {
        	$("#file_upload > div").text("ファイルのアップロードを開始します");
        },
        onDragLeave: function() {
        	$("#file_upload > div").text("");
        },
        onDrop: function() {
        	$("#file_upload > div").text("");
        }
        
    });
    
    // !triggers
    $("#openSlideshowBox")
    .each(function(){
    	$(this).nextAll("div.slideshowbox").hide();
    })
    .click(function(){
    	var $a = $(this);
    	$a.nextAll("div.slideshowbox:not(:visible)").show("fast", function(){
    		$a.hide();
    	});

    	return false;
    });

    // keyboard shortcut
    shortcut.add("u", function(){
      location.href = $("#upload_link").attr("href");
    }, {
      disable_in_input: true
    });

    $(document).keydown(function(e){
      if (e.keyCode == 27) {
        if (window.parent) {
          window.parent.tb_remove();
        }
      }
    });
});
