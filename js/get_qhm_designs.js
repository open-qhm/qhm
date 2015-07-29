$(function(){

	// qhmsetting かどうか
/* 	if ($("#authDialog").length > 0) */
	if ($("#authModal").length > 0)
	{
		var scripturl = location.href.split('?').shift(), mts, safe_mode;
		
		// !auth dialog
    var $ad = $("#authModal")
    .on("click", "[data-modal-type=login]", function(){

      var data = $("input", $ad).serialize();

      $.post(scripturl, data, function(res){
        if (mts = res.match(/<result>(.*?)<\/result>/)) {
          res = mts[1];
          var $div = $(".error-msg", $ad); 
          if (res.match(/^valid$/)) {
            $div.addClass("alert alert-success").html('認証に成功しました');
            getDesigns();
          } else {
            $div.addClass("alert alert-danger").html(res);
          }
        } else {
            $div.addClass("alert alert-danger").html('QHMからログアウトしています<br /><a href="'+scripturl+'?cmd=qhmauth">こちら</a>からログインしてください</span>');
        }
        $div.prepend('<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>');
      });

    })
    .on("shown.bs.modal", function(){

			var data = {
				phase: 'club',
				mode: 'has_qhm',
				plugin: 'qhmsetting',
				pcmd: 'post',
				from: 'design_form'
			};
      var res = $.ajax({
        type: "GET",
        url: scripturl,
        data: data,
        async: false
      }).responseText;

			var safe_mode = res.match(/<safe_mode>1<\/safe_mode>/)? true: false;//safe mode?
			if (mts = res.match(/<result>(.*?)<\/result>/)) {
				res = mts[1];
				if (res.match(/^valid$/)) {
				  $ad.modal('hide');
					getDesigns();
				} else {
					//$ad.modal('show');
				}
			} else {
				//TODO: logout message
			}
    });

		
		// !FTP Dialog
		var $ftpd = $("#ftpModal").on("click", "[data-modal-type=connect]", function(){
					var data = $("input", $ftpd).serialize();
					$.post(scripturl, data, function(res){
						if (mts = res.match(/<result>(.*?)<\/result>/)) {
							res = mts[1];
              var $div = $(".error-msg", $ftpd); 
							if (res.match(/^OK$/)) {
                $div.addClass("alert alert-success").html('フォルダの作成に成功しました');
								setTimeout(function(){
									$ftpd.modal('hide');
									$div.empty();
								}, 500);
								downloadDesign($("input:hidden[name='design_name']", $ftpd).val());
							} else if (res.match(/^NG_Dir$/)) {
								$("#ftpusedir").val(1);
								$div.addClass("alert alert-danger").html('設置先が特定できません。設置先ディレクトリを指定してください。');
								$("#ftpDirRow").show().focus().select();
							} else {
								$div.addClass("alert alert-danger").html(res);
							}
						} else {
							$div.addClass("alert alert-danger").html('QHMからログアウトしています<br /><a href="'+scripturl+'?cmd=qhmauth">こちら</a>からログインしてください');
						}
					});
    });

    var $dd = $("#designModal")
  		.data("hasDesign", false)
  		.data("isPrem", false)
  		.data("isHaik", false)
  		.on("click", "[data-modal-type=download]", function(){
					var design = $("input:radio[name=design]:checked", $dd).val();
					if (safe_mode) {
						$("input:hidden[name=design_name]", $ftpd).val(design);
						$ftpd.dialog('open');
					} 
					else {
						downloadDesign(design);
					}
  		});
		
		function downloadDesign(design) {
			var data = {
				phase: 'club',
				mode: 'download_design',
				plugin: 'qhmsetting',
				pcmd: 'post',
				from: 'design_form',
				design: design
			};
			$.post(scripturl, data, function(res){
				if (mts = res.match(/<result>(.*?)<\/result>/)) {
					res = mts[1];
					if (res.match(/^OK$/)) {
						var msg = '';
						//持っているデザインをダウンロードした場合は増やさない
						if ($("#designList input:radio[value="+design+"]").length == 0) {
							//デザインを増やす
							var thumb = window.designData[design],
								links = QhmSetting.prev_link_format.replace("%s", design) + QhmSetting.del_link_format.replace("%s", design);
							var $cell = $('<td width="33%"><label><img src="'+thumb+'" width="180" height="200" alt="'+design+'" /><br /><input type="radio" name="design" value="'+design+'" style="margin-right:5px;" />'+design+'</label><br />'+ links +'</td>');
							$("label", $cell)
								.bind("mouseover", function(){
									$(this).closest("td").css({"outline" : "5px solid #CBE86B"});
								})
								.bind("mouseout", function(){
									$(this).closest("td").css({"outline" : ""});
								});
							
							var $tr = $("#designList tr:last");
							if ($("td > label", $tr).length < 3) {
								$("td:eq("+ $("td label", $tr).length +")", $tr).html($cell.html());
							}
							else
							{
								if ($("td", $tr).length == 3) {
									$("#designList").append('<tr></tr>');
									$tr = $("#designList tr:last");
								}
								$tr.append($cell);
							}
							msg = 'デザイン【'+design+'】を取得完了しました';
							
							//delete|preview option を更新
							$("#removeSelector").empty();
							$("#previewSelector").empty();
							var html = '', prhtml = '';
							var $radios = $("#designList input:radio:not(.currentStyle)");
							var $prradios = $("#designList input:radio");
							if ($radios.length > 0) {
								$("#qhmdesignremove_holder").show();
							}
							$radios.each(function(){
								var d = $(this).val();
								html += '<option value="'+d+'">'+d+'</option>';
							});
							$prradios.each(function(){
								var d = $(this).val();
								prhtml += '<option value="'+d+'">'+d+'</option>';
							});
							$("#removeSelector").html(html);
							$("#previewSelector").html(prhtml);
							
							
						} else {
							msg = 'デザイン【'+design+'】のデータを更新しました';
						}
						$("#qhmdesignStatus").html('<span style="font-weight:bold">'+ msg +'</span>');
					} else {
						alert("デザインを取得できません");
					}
				} else {
					//TODO: logout message
				}
				$dd.modal('hide');
			});
		
		}	
		
		function getDesigns() {
			if ($dd.data("hasDesign")) {
				showDesigns();
				return;
			}
			var data = {
				phase: 'club',
				mode: 'get_designs',
				plugin: 'qhmsetting',
				pcmd: 'post',
				from: 'design_form'
			};
			$.ajax({
				url: scripturl,
				type: "POST",
				data: data,
				dataType: "text",
				success: function(res, result){
					if (mts = res.match(/<json>(.*?)<\/json>/)) {
						res = mts[1];
						if (typeof JSON != "undefined") {
							res = JSON.parse(res);
						} else {
							eval("res = " + res + ";");
						}
	
						//designDialog に入れ込む
						$dd.find(".modal-body [data-box=designlist]").html('<div class="container-fluid qhm-design-inner"></div>');
						var $div = $(".qhm-design-inner", $dd);
						var cnt = 0, isPrem = false, isHaik = false;
						for (var d in res) {
							if (d.match(/^i_/)) {
								isPrem = true;
							}
							if (d.match(/^haik_/)) {
								isHaik = true;
							}

							var frag = ' id="'+ d +'"';
							var thumb = res[d];
							
							if (cnt++ % 3 == 0) {
								$div.append('<div class="row"></div>');
							}
							('.row:last', $div).append('\
    <div class="col-sm-4" '+frag+'>\
      <a href="'+scripturl+'?cmd=qhmdesign&style_name='+encodeURIComponent(d)+'" title="クリックしてプレビュー"><img src="'+ thumb +'" class="img-responsive" alt="'+d+'" /></a>\
      <label class="radio"><input type="radio" name="design" value="'+d+'" style="margin-right:5px;" />'+d+'</label>\
		</div>');
						}
            $ad.modal('hide');

						if (cnt == 0) {
							alert("デザインを取得できません。");
						} else {
							//save design data
							window.designData = res;
							$dd.data("hasDesign", true);
							$dd.data("isPrem", isPrem);
							$dd.data("isHaik", isHaik);
							showDesigns();
						}
					} else {
						//TODO: logout message
					}
				},
				error: function(){},
				complete: function(){}
			});
		}

		function showDesigns() {
			$dd.modal('show');
			if ( ! $dd.data("isPrem")) {
		    $dd.find(".modal-body [data-box=menu]").remove();
        $dd.find(".modal-body [data-box=designlist]").removeClass("col-sm-9").addClass("col-sm-12");
			}
			if ( ! $dd.data("isHaik")) {
        $dd.find(".modal-body [data-box=menu] [data-design-type=haik]").remove();  			
			}
		}
		
		// !loading event
		$("body").append('<div id="loading"><img src="image/loadingAnimation.gif" width="208" height="13" alt="Now Loading..." /></div>');
		$("#loading")
		.css({
			zIndex: 99999999,
			width: 208,
			height: 13,
			display: "none",
			position: "fixed",
			left: "50%",
			top: "50%",
			marginLeft: -104,
			marginTop: -7
		})
		.bind('ajaxSend', function(){
			$(this).fadeIn(200);
		})
		.bind('ajaxComplete', function(){
			$(this).fadeOut(200);
		});
	
		
		// !logo radio button
		$("#logoImageButton").click(function(){$("#logoImageRadio").click()});
		$("#logoTextInput").focus(function(){$("#logoTextRadio").click()});
		
		// !Sumit WordPress Theme Preview
		$("#WpPreviewButton").click(function(){
			var $form = $(this).closest("form");
			
			$form
			.append('<input type="hidden" name="preview" value="1" />')
				.find("[name^='qhmsetting[']").each(function(){
					if ($(this).is("textarea")) {
						$(this).attr('name', 'wp_add_css');
					}
					else if ($(this).is("select")) {
						$(this).attr('name', 'design');
					}
				})
			.end()
				.find("input:hidden[name=mode]").attr("value", "form");
			
			$form.submit();
		});
		
		//qhmDesignGetter をクリックする
		if (location.hash === "#qhmDesignGetter")
		{
			$(location.hash).click();
		}
	
	}
	
	// !プレビューバーからの「適用」
	else
	{
		$("input:submit[name='preview_set']:not(.local)")
			.closest("form").submit(function(){
				var $form = $(this),
					design = $("input:hidden[name='qhmsetting[style_name]']", this).val();
				if (typeof $form.data("loading") === "undefined")
				{
					$form.data("loading", false);
				}
				if ($form.data("loading"))
				{
					return false;
				}
				
				$form.data("loading", true);
					
				(function(design) {
					var data = {
						phase: 'club',
						mode: 'download_design',
						plugin: 'qhmsetting',
						pcmd: 'post',
						from: 'design_form',
						design: design
					};
					$.post(scripturl, data, function(res){
						$form.data("loading", false);
						if (mts = res.match(/<result>(.*?)<\/result>/)) {
							res = mts[1];
							if (res.match(/^OK$/)) {
								//submit
								$form.unbind("submit").submit();
							} else {
								alert("デザインを取得できません。一度、ログアウトして、再度お試しください。");
							}
						} else {
							alert("Ensmall クラブに接続できません。一度、ログアウトして、再度お試しください。");
						}
					});
			
				})(design);
				
				return false;
			});
	}
	
});