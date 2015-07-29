<div class="admin qhmupdate">

<!--ftp login_form start-->
<form id="UserLoginForm" method="post" action="<?php echo h($script.'?cmd=qhmupdate') ?>">
<h2>サーバー情報</h2>
<?php if (isset($error) && strlen($error) > 0) : ?>
<div class="message"><?php echo $error ?></div>
<?php endif; ?>

<?php if ($ftp_type == 'full') : ?>
<p class="input"><label>FTPサーバー<br />
<input type="text" name="ftp_hostname" size="30" tabindex="1" maxlength="128" value="localhost" id="ftp_hostname" />
</label></p>
<?php endif; ?>

<p class="input"><label>FTPユーザー (FTPアカウント)<br />
<input type="text" name="ftp_username" size="30" tabindex="2" maxlength="128" value="" id="ftp_username" />
</label></p>

<p class="input"><label>FTPパスワード<br />
<input type="password" name="ftp_password" size="30" tabindex="3" value="" id="ftp_password" />
</label></p>

<?php if ($ftp_type == 'full') : ?>
<p class="input"><label>設置先フォルダ（フルパス）<br />
<input type="text" name="install_dir" size="30" tabindex="4" value="" id="install_dir" />
</label></p>
<?php endif; ?>

<br />
<input type="hidden" name="mode" value="ftp_login" />
<p class="submit"><label><input type="submit" tabindex="5" value="次へ" class="submit" /></label></p>
</form>
<!--ftp login_form end-->

</div>