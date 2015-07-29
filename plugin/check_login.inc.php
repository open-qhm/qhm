<?php
/**
 *   QHM Check Login Plugin
 *   -------------------------------------------
 *   check_login.inc.php
 *   
 *   Copyright (c) 2010 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 2010-12-15
 *   modified :
 *   
 *   Description
 *   
 *   Usage :
 *   
 */

function plugin_check_login_action() {
	global $vars, $script, $auth_users;
	
	$qt = get_qt();
	
	//Ajax
	if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
	
		$mode = isset($vars['mode'])? $vars['mode']: 'check';

		$res = array(
			'status' => 0, //0: logout, 1: ok, 2: error
			'message' => '',
			'data' => null,
		);
		
		//チェック
		if ($mode == 'check') {
			
			// login OK
			if(isset($_SESSION['usr']) && array_key_exists($_SESSION['usr'], $auth_users)){
				$res['status'] = 1;
				$res['message'] = 'login';
			}
			// logout
			else {
				$res['status'] = 0;
				$res['message'] = 'logout';
			}
			
		}
		//ログイン
		else if($mode == 'auth') {
		
			$username = isset($vars['username'])? $vars['username']: '';
			$password = isset($vars['password'])? $vars['password']: '';
			
			//OK
			if (isset($auth_users[$username]) && $auth_users[$username] == pkwk_hash_compute($password)) {
			
				$_SESSION['usr'] = $username;
				if (ss_admin_check()) {
					$d = dir(CACHEQHM_DIR);
					while (false !== ($entry = $d->read())) {
						if($entry!='.' && $entry!='..') {
							$entry = CACHEQHM_DIR. $entry;
							if(file_exists($entry)) {
								// cacheqhmディレクトリにある3日前の一時ファイルを削除
								if (mktime(date("H"),date("i"),date("s"),date("n"),date("j")-3,date("Y")) > time(fileatime($entry)) ) {
									unlink($entry);
								}
							}
						}
					}
				   $d->close();
				}
				
				$res['status'] = 1;
				$res['message'] = 'Login Success';
			
			}
			//NG
			else {
				$res['status'] = 2;
				$res['message'] = 'Invalid Username or Password';
			}
			
		
		}
		//ログアウト
		else if ($mode == 'destroy') {
			ss_auth_logout();
			$res['status'] = 0;
			$res['message'] = 'logout';
		}
		else {
			$res['status'] = 2;
			$res['message'] = 'request error';
			$res['data'] = $vars;
		}
	
		header("Content-Type: application/json; charset=UTF-8");
		$json = json_encode($res);
		echo $json;
		exit;
	
	}
	//Browser Access: redirect cmd=qhmauth
	else {
		$to = $script. '?cmd=qhmauth';
		header("Location: $to");
		exit;
	}
	
}

function plugin_check_login_convert() {
	global $vars, $script, $check_login;
	
	//$check_login が初期化されていない場合、1 を代入
	if ( ! isset($check_login))
	{
		$check_login = 1;
	}
	
	if ( ! $check_login)
	{
		return '';
	}
	
	//一般アクセス時には実行しない
	if (!check_editable($vars['page'], false, false)) {
		return;
	}

	$qm = get_qm();
	$qt = get_qt();

	if (!$qt->is_appended('plugin_check_login')) {
	
		// !javascript
		$interval = '10000';
		$slidespeed = '3000';
		$addjs = '
<script type="text/javascript">
window.qhm_check_login = '. $check_login .';
$(function(){

	var $loginForm = $("#loginForm"),
		$loginOL = $("#loginOverlay"),
		$loginMsg = $("#loginMessage"),
		$logoutNtfr = $("#logoutNotifier"),
		$usrInput = $("input:text[name=username]", $loginForm),
		$pwInput = $("input:password[name=password]", $loginForm),
		$loginBtn = $("div.submit_login a", $loginForm),
		$cancelBtn = $("div.cancel_login a", $loginForm);

	//loginForm init

	//position
	var top = (($(window).height() / 2) - ($loginForm.outerHeight() / 2)) + 0;
	var left = (($(window).width() / 2) - ($loginForm.outerWidth() / 2)) + 0;
	if( top < 0 ) top = 0;
	if( left < 0 ) left = 0;
	$loginForm.css({
		top: top + "px",
		left: left + "px"
	});


	//loginOverlay init
	$loginOL
	.css({
		height: $(document).height()
	});


	function checkLogin(callback) {
		if (window.qhm_check_login == 2) {
			setTimeout(checkLogin, '. $interval. ');
		}
		
		callback = callback || function(){};
		
		if ($loginOL.is(":visible")) return;

		var is_login = true;
		$.ajax({
			url: "'. $script. '",
			global: false,
			data: {cmd: "check_login", mode: "check"},
			dataType: "json",
			type: "GET",
			success: function(res, result){
				var mode = "";
				//切れていればlogout notifier 表示
				if (res.status == 0) {
					if (window.qhm_check_login == 1) {
						showLoginForm();
						$("#loginMessage")
						.text("'. $qm->m['plg_check_login']['logout2']. '")
						.css("color", "red");
					}
					else {
						var $ln = $logoutNtfr.slideDown('.$slidespeed.');
					}
					is_login = false;
				}
			},
			complete: function(){callback.apply(this, [is_login])}
		});
	}
	
	if (window.qhm_check_login == 1) {
		$("#edit_form_main").bind("submit.check_login", function(e){
			checkLogin(function(is_login){
				if (is_login) {
					var $form = $("#edit_form_main"),
						target = $form.data("clicked") || "preview";
					$form.unbind("submit.check_login")
						.find("input:submit[name="+target+"]").click();
				}
			});
			return false;
		})
			.find("input:submit").click(function(){
				var $$ = $(this);
				$$.closest("form").data("clicked", $$.attr("name"));
			});
	}
	if (window.qhm_check_login == 2) {
		checkLogin();
	}
	
	function removeLoginForm() {
		$loginOL.fadeOut();
		$loginForm.fadeOut("normal", function(){
			$loginMsg.text("");
		});
	}
	
	function showLoginForm() {
		//init
		removeLoginForm();
		
		
		//show prompt
		$loginForm.fadeIn();

		$loginOL.fadeIn();
		
		$usrInput.focus().select();
		
	}	


	$logoutNtfr.click(function(){
		//セッションが切れているかチェック
		$.ajax({
			url: "'. $script. '",
			global: false,
			data: {cmd: "check_login", mode: "check"},
			dataType: "json",
			type: "GET",
			success: function(res, result){
				var mode = "";
				//切れていればフォームを表示
				if (res.status == 0) {
					showLoginForm();
				}
				else {
					//$.flash(res.result.message);
				}
			}
		});
		$(this).slideUp(1000);	
		return false;
	});

	//If click overlay, cancel form
	$("#loginOverlay").click(function(){
		$cancelBtn.trigger("click");
		return false;
	});

	//login button click
	$loginBtn.click( function() {
		var data = {
			cmd: "check_login",
			mode: "auth",
			username: $usrInput.val(),
			password: $pwInput.val()
		};
		
		//ログインを試行
		$.ajax({
			url: "'. $script. '",
			global: false,
			data: data,
			dataType: "json",
			type: "POST",
			success: function(res, result){
				//login success
				if (res.status == 1) {
					$loginMsg
					.css({
						color: "#0f0"
					})
					.text(res.message);
					
					removeLoginForm();
				} else {
					$loginMsg
					.css({
						color: "#f00"
					})
					.text(res.message);
					//retry
					$usrInput.focus().select();
				}
			}
		});
		
		// ページの更新、プレビューボタンのロックを解除
		$("#edit_form_main input:submit, #edit_form_cancel input:submit")
			.prop("disabled", false)
			.prev("input:hidden").remove();
		
		return false;
	});
	$cancelBtn.click( function() {
		removeLoginForm();
		$logoutNtfr.slideDown(1000);
		return false;
	});
	$("input", $loginForm).keypress( function(e) {
		if( e.keyCode == 13 ) $loginBtn.trigger("click");
		if( e.keyCode == 27 ) $cancelBtn.trigger("click");
	});

});
</script>
';

		// !CSS
		$addstyle = '
<style type="text/css">
#logoutNotifier {
	border: #999 6px solid;
	border-bottom: none;
	background-color: black;
	position: fixed;
	bottom: 0;
	left: 50%;
	width: 600px;
	height: 50px;
	line-height: 50px;
	text-align: center;
	-moz-border-radius-topleft: 6px;
	-moz-border-radius-topright: 6px;
	-webkit-border-top-left-radius: 6px;
	-webkit-border-top-right-radius: 6px;
	border-top-left-radius: 6px;
	border-top-right-radius: 6px;
	color: white;
	margin-left: -300px;
	opacity: 0.75;
	display: none;
	cursor: pointer;
	z-index: 9000;
}

#logoutNotifier:hover {
	background-color: #666;
}
#logoutNotifier a{
	color: white;
}

#loginForm {
	display: none;
	position: fixed;
	z-index: 1000;
	min-width: 300px;
	max-width: 600px;
	min-height: 210px;
	background-color: #FFF;
	border: solid 5px #999;
	color: #000;
	font-size: 12;
	margin: 0;
	padding: 0;
	-moz-border-radius: 10px;
	-webkit-border-radius: 10px;
	border-radius: 10px;
	-moz-box-shadow: 0 1px 5px rgba(0, 0, 0, .3);
	-webkit-box-shadow: 0 1px 5px rgba(0, 0, 0, .3);
	box-shadow: 0 1px 5px rgba(0, 0, 0, .3);
}
#loginForm input[type=text], #loginForm input[type=password] {
	border: 1px solid #C5C6C8;
	border-bottom: 1px solid #8D8D8F;
	font-size: 16px;
	width: 284px;
	height: 25px;
	padding: 4px 4px;
/*
	-webkit-transition: .2s ease-in-out;
	-moz-transition: .2s ease-in-out;
	-o-transition: .2s ease-in-out;	
*/
	-moz-box-shadow: inset 0 1px 5px rgba(0, 0, 0, .3),0 1px 0 0 #FAFBFB;
	-webkit-box-shadow: inset 0 1px 5px rgba(0, 0, 0, .3),0 1px 0 0 #FAFBFB;
	box-shadow: inset 0 1px 5px rgba(0, 0, 0, .3),0 1px 0 0 #FAFBFB;
	-moz-border-radius: 2px;
	-webkit-border-radius: 2px;
	border-radius: 2px;
	border-top-left-radius: 2px 2px;
	border-top-right-radius: 2px 2px;
	border-bottom-right-radius: 2px 2px;
	border-bottom-left-radius: 2px 2px;
}
#loginForm input[type=text]:focus, #loginForm input[type=password]:focus {
	border: 1px solid #228DED;
	-moz-box-shadow: 0 0 5px #228DED;
	-webkit-box-shadow: 0 0 5px #228DED;
	box-shadow: 0 0 5px #228DED;
}
#loginForm label {
	display: block;
	width: 85%;
	font-weight: bold;
	font-size: 12px;
	text-align: left;
	margin-bottom: 10px;
}
#loginForm div.title {
	background-color: #999;
	color: white;
	font-weight: bold;
	height: 25px;
	line-height: 25px;
	margin: 0;
	text-align: left;
	padding-left: 1em;
}
#loginForm div.content {
	padding: 1em;
}
#loginForm div.btn_block {
	float: left;
	margin-right: 5px;
}
#loginForm div.btn_block a.btn_style {
	display: inline-block;
	text-decoration: none;
/*
	background-image: url(http://hootsuite.com/images/themes/blue_steel/maps/trim.png);
	background-position: 0 -1300px;
	background-repeat: repeat-x;
*/
	background-color: #565F6F;
	background: -webkit-gradient(linear, left top, left bottom, from(#7B8596), to(#565F6F));
	background: -moz-linear-gradient(top, #7B8596, #565F6F);	outline: none;
	white-space: nowrap;
	cursor: pointer;
	border-color: #AAB0BB #525A69 #333842;
	background-color: #525B69;
	color: white;
	text-shadow: 0 -1px 0 rgba(0, 0, 0, .4);
	-moz-box-shadow: 0 1px 2px rgba(0, 0, 0, .5);
	-webkit-box-shadow: 0 1px 2px rgba(0, 0, 0, .5);
	box-shadow: 0 1px 2px rgba(0, 0, 0, .5);
	border-width: 1px;
	border-style: solid;
	padding: 3px 10px 4px 9px;
	font-size: 12px;
	font-weight: bold;
	line-height: 15px;
	white-space: nowrap;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;
}
#loginForm div.btn_block a.btn_style:hover {
/* 	background-position: 0 -1200px; */
	background: -webkit-gradient(linear, left top, left bottom, from(#5D94D6), to(#1B58AE));
	background-color: #1B58AE;
	background: -moz-linear-gradient(top, #5D94D6, #1B58AE);	outline: none;
	border-color: #59E #47C #35A;
}
#loginMsg {
	font-weight: bold;
}
#loginOverlay {
	display: none;
	position: fixed;
	z-index: 999;
	top: 0;
	left: 0;
	width: 100%;
	background: #000;
	opacity: 0.15;
}
</style>
';
		
		// !HTML
		$addblock = '
<div id="logoutNotifier">
	'. $qm->m['plg_check_login']['logout']. '
</div>
<div id="loginForm">
	<div class="title">'. $qm->m['plg_check_login']['title']. '</div>
	<div class="content">
		<label>'. $qm->m['username']. ':<br />
			<input type="text" name="username" /></label>
		<label>'. $qm->m['password']. ':<br />
			<input type="password" name="password" /></label>
		<div id="loginMessage"></div>
		<div class="btn_block submit_login" title="'. $qm->m['ss_authform']['btn_login']. '">
			<a href="#" class="btn_style">'. $qm->m['ss_authform']['btn_login']. '</a>
		</div>
		<div class="btn_block cancel_login" title="'. $qm->m['ss_authform']['btn_cancel']. '">
			<a href="#" class="btn_style">'. $qm->m['ss_authform']['btn_cancel']. '</a>
		</div>
		<div style="clear:both;"></div>
	</div>
</div>
<div id="loginOverlay"></div>
';
		$qt->appendv_once('plugin_check_login', 'beforescript', $addstyle. $addjs);
		$qt->appendv_once('plugin_check_login_ls', 'lastscript', $addblock);
	
	
	}

	return;
}



?>