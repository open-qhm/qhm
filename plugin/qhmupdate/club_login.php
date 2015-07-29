<!--login_form start-->
<script type="text/javascript">
$(document).ready(function(){
	var sa = window.location.href.split('/')
	sa.splice(sa.length-1, 1);
	var url = sa.join('/')+'/';
	$('input:hidden[name=install_url]').val(url);

	
	$('input:checkbox[name=use_proxy]').click(function(){
		if ($(this).is(':checked')) {
			$('div.setproxy').show();
		}
		else {
			$('div.setproxy').hide();
			$("#show_proxy").show();
		}
	});
	
	$("#show_proxy").click(function(){
		var $a = $(this);
		$("input:checkbox[name=use_proxy]").prop("checked", true);
		$("div.setproxy").show(0, function(){
			
			$a.hide();
			
		});
	});
});
</script>

<div class="admin qhmupdate">

<form id="UserLoginForm" method="post" action="<?php echo h($script.'?cmd=qhmupdate') ?>">
<h2>Ensmall Club 認証</h2>
<?php if(isset($error) && strlen($error) > 0): ?>
<div class="message"><?php echo $error ?></div>
<?php endif; ?>

<?php if (isset($warning) && strlen($warning) > 0) : ?>
<div class="message"><?php echo $warning ?></div>
<?php endif; ?>

<p class="input"><label>メールアドレス<br />
<input name="email" type="text" size="50" tabindex="1" maxlength="128" value="<?php echo isset($email) ? h($email) : ''?>" id="email" class="form-control">
</label></p>

<p class="input"><label>パスワード<br />
<input type="password" name="password" size="30" tabindex="2" value="" id="password" class="form-control">
</label></p>

<br />

<p><a href="#" id="show_proxy">プロキシー経由でインターネット接続している方はクリック</a></p>

<div class="setproxy" style="display:none;margin-top:10px;margin-bottom:10px;">

<p>
	<input type="hidden" name="use_proxy" value="0" />
	<label><input type="checkbox" name="use_proxy" value="1" /> プロキシー経由で接続</label>
</p>

<p class="input"><label>プロキシーホスト<br />
<input name="proxy_host" type="text" size="30" tabindex="4" maxlength="128" value="" id="proxy_host" /><br />
</p>
<p class="input"><label>ポート番号<br />
<input name="proxy_port" type="text" size="6" tabindex="5" maxlength="5" value="<?php echo $proxy_port?>" id="proxy_port" />
</label></p>
</div>


<br />
<input type="hidden" name="mode" value="club_login" />
<p class="submit"><label><input type="submit" tabindex="5" value="次へ" class="btn btn-primary" /></label></p>

</form>
<!--login_form end-->

</div>