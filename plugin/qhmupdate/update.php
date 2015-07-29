<style type="text/css">
.loading {
display:none;
width:300px;
line-height: 1.5em;
position:absolute;
top:50%;
left:50%;
margin-left: -150px;
border: 1px solid #ccc;
-moz-border-radius: 10px;
-webkit-border-radius: 10px;
border-radius: 10px;
padding: 10px 10px 10px 16px;
background-color: white;
border-radius: 10px;
-moz-box-shadow: rgba(150, 150, 150, 1) 0 4px 200px;
-webkit-box-shadow: rgba(150, 150, 150, 1) 0 4px 200px;
-khtml-box-shadow: rgba(150, 150, 150, 1) 0 4px 200px;
box-shadow: rgba(150, 150, 150, 1) 3px 4px 200px;
text-align: center;
}

div.layoutbox{
position:relative;
}

</style>

<script type="text/javascript">
//<![CDATA[
$(function(){
	$("input:submit").click(function(){
		var $input = $(this), $form = $(this).closest("form");
		
		$input.attr("disabled", true).addClass("disabled")
		.after('<input type="hidden" name="'+$(this).attr("name")+'" value="1" />');
		$("div.loading").show();

		var data = {mode: "update", cmd: "qhmupdate"};
		$.post("<?php echo h($script)?>", data, function(res){
			if (res === "1")
			{
				$form.find("input[name=mode]").val("complete");
			}
			else
			{
				alert(res);
			}
			$form.submit();
		});

		return false;
	});
	
	agreeCheck(document.getElementById("agree"));
});

function agreeCheck(obj)
{
	if (obj.checked) {
		document.getElementById("execBlock").style.display = 'block';
	}
	else {
		document.getElementById("execBlock").style.display = 'none';
	}
}

//]]>
</script>


<div class="admin qhmupdate">


<div class="layoutbox">

<h2>システムの更新</h2>

<div class="execute">

<?php if (isset($error)) : ?>
<div class="message"><?php echo $error ?></div>
<?php endif; ?>

<div id="descriptionBlock">
<p>
	システムの更新をするには、利用規約に同意して頂く必要があります。 <br />
	利用規約をご一読頂き、同意して頂けましたら「利用規約に同意する」にチェックしてください
</p>
<iframe src="//ensmall.net/club/products/get_description/qhmpro" frameborder="1" style="width:90%;height:180px;"></iframe>
<p style="margin-top:10px;"><label><input type="checkbox" id="agree" onClick="agreeCheck(this)" tabindex="2" />&nbsp;利用規約に同意する</label></p>
</div>

<div id="execBlock" style="display:none">

<form id="ExecuteForm" method="post" action="<?php echo $script ?>?cmd=qhmupdate">
<input type="hidden" name="mode" value="update_confirm" />
<p class="submit"><label><input type="submit" name="update" tabindex="3" value="システムの更新" style="float:none;"/></label></p>
</form>

</div>

<div class="loading">
<p>現在、実行中です・・・<br />もうしばらくお待ちください。</p>
<p><img src="<?php echo IMAGE_DIR ?>loading.gif" alt="実行中" /></p>
</div>

</div> <!-- execBlock -->
</div><!-- execute -->
<!--install end-->

</div><!-- layoutbox -->
</div>