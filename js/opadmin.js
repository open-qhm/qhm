//------------------------------------------------------------------------
//[Plugin: add user on site] copyright(c) 2007 Hokuken lab.
//------------------------------------------------------------------------
function selectUser(id, data){
	if (document.getElementById) {
		myobj = document.getElementById(id);
	}
	else if (document.all) {
		myobj = document.all[id];
	}
	else if (document.layers){
		myobj = document.layers[id];
	}
	myobj.value = data;
}
function deleteUser(username){
	if (username == '') {
		return false;
	}
	if(window.confirm('削除してよろしいですか？\n\nユーザー名：'+username)){ 
		document.frm_users.user_op.value = 'delete_user';
		document.frm_users.target_user.value = username;		
		document.frm_users.submit();
	}
}
function deletePattern(username, num){
//	if (username == '') {
//		return false;
//	}

	if(window.confirm('削除してよろしいですか？\n\nユーザー名：'+username)){ 
		document.frm_users.pattern_op.value = 'delete_pattern';
		document.frm_users.delno.value = num;
		document.frm_users.submit();
	}
}
function rewritePasswd(username){
	var newpass = prompt("新しいパスワードを入力してくださ","");
	
	if(newpass == null){	
		return null;
	}

	document.frm_users.user_op.value = 'rewrite_password';
	document.frm_users.target_user.value = username;		
	document.frm_users.op_passwd.value= newpass;
	document.frm_users.submit();

}
function hightlight(target, color) {
	target.style.backgroundColor = color;
}
