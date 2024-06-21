//------------------------------------------------------------------------
//[For admin window & other plugin] copyright(c) 2007 Hokuken lab.
//------------------------------------------------------------------------
$(document).ready(function(){
	var badBrowser=(/MSIE ((5\.5)|6)/.test(navigator.userAgent) && navigator.platform == "Win32");
	if (badBrowser) {
		$('div.toolbar_upper').wrap('<div class="ie6dummy"></div>');
		$('div.toolbar_upper').css('position','absolute');
		$('div.other_plugin').wrap('<div class="ie6dummy"></div>');
		$('div.other_plugin').css('position','absolute');
	}


	if ($('#msg').length <= 0) {
		$(".go_editbox").hide();

		// プレビュー
		let previewScreens = [];
		var $links = $("#mobile, #mobile_min, #tablet1, #tablet2, #tablet3, #tablet4, #tablet1_min, #tablet2_min, #tablet3_min, #tablet4_min, #desktop1, #desktop2, #desktop3, #desktop4, #desktop1_min, #desktop2_min, #desktop3_min, #desktop4_min");
		$links.closest(".toolbar_submenu").css("width", "max-content");

		$links.on("click", function(){
			const baseFeatures = "menubar=no,location=no,resizable=yes,status=no,toolbar=no,scrollbars=yes,";
			const windowConfs = []
			switch ($(this).attr("id")) {
				case "mobile":
				case "mobile_min":
					windowConfs.push({ features: baseFeatures + "width=375,height=667,top=50,left=-100", title: "📱4.7inch(375x667)"})
					windowConfs.push({ features: baseFeatures + "width=375,height=812,top=50,left=280", title: "📱5.4inch(375x812)" })
					windowConfs.push({ features: baseFeatures + "width=393,height=852,top=50,left=660", title: "📱6.1inch(393x852)" })
					windowConfs.push({ features: baseFeatures + "width=430,height=932,top=50,left=1060", title: "📱6.7inch(430x932)" })
					break;
				case "tablet1":
				case "tablet1_min":
					windowConfs.push({ features: baseFeatures + "width=744,height=1133,top=50,left=-100", title: "📱8.3inch(744x1133)"})
					break;
					case "tablet2":
					case "tablet2_min":
						windowConfs.push({ features: baseFeatures + "width=820,height=1180,top=50,left=-50", title: "📱10.9inch(820x1180)"})
					break;
				case "tablet3":
				case "tablet3_min":
						windowConfs.push({ features: baseFeatures + "width=834,height=1194,top=50,left=0", title: "📱11inch(834x1194)"})
						break;
				case "tablet4":
				case "tablet4_min":
					windowConfs.push({ features: baseFeatures + "width=1024,height=1366,top=50,left=50", title: "📱12.9inch(1024x1366)"})
					break;
				case "desktop1":
				case "desktop1_min":
					windowConfs.push({ features: baseFeatures + "width=1280,height=800,top=50,left=-100", title: "🖥13.3inch(1280x800)"})
					break;
				case "desktop2":
				case "desktop2_min":
					windowConfs.push({ features: baseFeatures + "width=1440,height=932,top=50,left=-50", title: "🖥15.3inch(1440x932)"})
					break;
				case "desktop3":
				case "desktop3_min":
					windowConfs.push({ features: baseFeatures + "width=1512,height=982,top=50,left=0", title: "🖥14.2inch(1512x982)"})
					break;
				case "desktop4":
				case "desktop4_min":
					windowConfs.push({ features: baseFeatures + "width=1920,height=1080,top=50,left=50", title: "🖥FullHD(1920x1080)"})
					break;
			}
			previewScreens = windowConfs.map((conf, index) => {
				return window.open(
					`${location.href}#${encodeURIComponent(conf.title)}`, `devicepreview${index+1}`, conf.features
				)
			});
		});

		$(window).on("unload", function(){
			for (const screen of previewScreens) {
				screen.close();
			}
		});

		if (window.name === "devicepreview") {
			$(".toolbar_upper").hide();
		}
	}
	//編集画面
	else
	{
		if (window == parent) {
			$("#edit_form_main input:submit, #edit_form_cancel input:submit")
			.click(function(){
				// どのボタンが押されたかを保存
				$(this).closest('form').data('clickedButton', $(this).attr('name'));

				var $submit = $(this).prop("disabled", true);
				$submit.before('<input type="hidden" name="'+$submit.attr("name")+'" value="'+ $submit.val()+'" />');
				$submit.closest("form").submit();
				return false;
			});
		}

		$(document).on("affixed.bs.affix", "nav[data-spy=affix]", function(){
			$(this).removeClass("affix").addClass("affix-top");
		});
	}


	$("ul.toolbar_menu > li[class!=nouse]")
		.hover(
			function() {
				$(this).addClass('tool_menuHover');
				$(this).css('background-position','right -25px');
				$(">ul:not(:animated)",this).show();
			},
			function() {
				$(this).removeClass('tool_menuHover');
				$(this).css('background-position','right 0');
			  	$(">ul",this).hide();
			}
		)
		.click(function(e){
			return toolbar_make_link($(this));
		});

	$("ul.toolbar_menu_min > li[class!=nouse]")
		.hover(
			function() {
				$(this).addClass('tool_menuHover');
				$(this).css({'background-position':'0 -25px'});
				$(">ul:not(:animated)",this).css({left:0, top:$(this).parent().height()}).show();
			},
			function() {
				$(this).removeClass('tool_menuHover');
				$(this).css('background-position','0 0');
			  	$(">ul",this).hide();
			}
		)
		.click(function(){
			return toolbar_make_link($(this));
		});
	$("ul.toolbar_submenu li[class!=nouse]")
		.hover(
			function(){
				$(this).css({'background-color':'#999999'});
			},
			function(){
				$(this).css({'background-color':'transparent'});
			}
		)
		.click(function(e){
			return toolbar_make_link($(this));
		});

	$("#prevlink")
	.each(function(){
		var $li = $(this).data("text", $(this).text()).parent();
		$li.data("backgroundColor", $li.css("background-color"));
	})
	.hover(
		function(){
			$(this).text("プレビュー解除")
				.parent().css("background-color", "#f88");
		},
		function(){
			var $li = $(this).text($(this).data("text")).parent();
			$li.css("background-color", $li.data("backgroundColor"));
		}
	);

	$("div.toolkit_switch")
		.click(function(){
			if ($(this).hasClass('expand')) {
				$("#toolbar_upper_max").hide();
				$("#toolbar_upper_min").show();
				document.cookie = 'toolbar_size=min';
			}
			else {
				$("#toolbar_upper_max").show();
				$("#toolbar_upper_min").hide();
				document.cookie = 'toolbar_size=max';
			}
		});

	function toolbar_make_link(obj) {
		if (obj.children('a').length <= 0) {
			return false;
		}
		var menulink = obj.children('a').attr('href');
		var otherwin = obj.children('a').attr('target');
		if (obj.hasClass('swfu')) {
			tb_show('', menulink+'?KeepThis=true&TB_iframe=true');
		}
		else if (obj.hasClass('thickbox')) {
			tb_show('', menulink+'&KeepThis=true&TB_iframe=true&width=800');
		}
		else if (menulink != '#' && menulink != '') {
			if (obj.children('a[target]').length) {
				window.open(menulink, "", "dependent=no, location=yes, menubar=yes, resizable=yes, scrollbars=yes, status=yes, titlebar=yes, toolbar=yes");
			}
			else {
				location.href = menulink;
			}
		}
		return false;
	}

	var isWin = (navigator.platform.indexOf('win') != -1);
	$(document).shortkeys({
		"e": function(){location.href=$("#editlink").attr("href")},
		"p": function(){$("input:submit[name=preview]").click()},
		"z": function(){$("input:submit[name=cancel]").get(0) && history.back()},
		"a": function(){if(typeof window.qhm_has_swfu != "undefined"){$("#keybind_list").modal("hide");tb_show("", $("#reflink").attr("href"));}},
		"i": function(){if(typeof window.qhm_has_swfu != "undefined"){$("#keybind_list").modal("hide");tb_show("", "swfu/index.php?KeepThis=true&TB_iframe=true");}},
		"t": function(){$("html,body").animate({scrollTop:0}, "fast")},
		"q": function(){location.href=$("#searchlink").attr("href")},
		"m": function(){window.open("http://manual.haik-cms.jp/")},
		"Shift+/": function(){$("#keybind_list").modal()},
		"n": function(){location.href=$("#newlink").attr("href")},
		"l": function(){location.href=$("#pagelistlink").attr("href")},
		"c": function(){location.href=$("#configlink").attr("href")},
		"u": function(){$("#keybind_list").modal("hide");$("#shareQHMPage").modal()},
		"h": function(){location.href="index.php"},
		"/": function(){$("#msg").focus()}
	});
	$("#msg").keydown(function(e){
		if (e.keyCode == 27) {
			$(this).blur();
		}
	});
	$(document).keydown(function(e){
		if ($("#msg").length === 0) return;
		//Save [Ctrl + S] [Command + S]
		if (((isWin && e.ctrlKey) || (! isWin && e.metaKey)) && e.keyCode == 83) {
			e.preventDefault();
			$("input:submit[name=write]").click();
		}
	});
	$(document).keydown(function(e){
		if (e.keyCode == 27) {
			$("#keybind_list").modal("hide");
			$("#tinyUrl:visible").fadeOut("fast");
			$("#shareQHMPage").modal("hide");
		}
	});

	//tinyUrl
	$("a#tinyurllink").parent().click(function(){
		$("#tinyUrl").fadeIn("fast");
	});
	$("a#tinyurllink_min").parent().click(function(){
		$("#tinyUrl").fadeIn("fast");
	});
	$("#tinyUrl a.close").click(function(){
		$("#tinyUrl").fadeOut("fast");
		return false;
	});

	//Share
	$("#sharelink, #sharelink_min").parent().click(function(){
		$("#shareQHMPage").modal();
	});
	$("#shareQHMPage")
	.on("click", "input", function(){
		$(this).select().focus();
	})
	.on("keyup click blur", "textarea", function(){
		var tweeturl_fmt = $("#shareQHMPage a.shareTwitter").attr("data-format");
		var url = $("#shareQHMPage a.shareTwitter").attr("data-url");
		var title = $("title").text();
		var text = $(this).val().replace('%URL%', url).replace('%TITLE%', title);
		var tweeturl = tweeturl_fmt.replace('$text', encodeURIComponent(text)).replace('$url', url);
		$("#shareQHMPage a.shareTwitter").attr("href", tweeturl);
	})
    .on("show.bs.modal", function(){
      $(this).find("textarea").click();
    });


	$(".other_plugin_button")
		.click(function(){
	});
	$("ul.other_plugin_menu > li")
		.hover(
			function() {
				$(this).addClass('menuHover');
				$(this).css('background-position','0 -25px');
				$(">ul:not(:animated)",this).show();
			},
			function() {
				$(this).removeClass('menuHover');
				$(this).css('background-position','0 0');
			  	$(">ul",this).hide();
			}
		);

	$("div.other_plugin_box_title")
		.hover(
			function () { $(this).css({'opacity':'0.8','cursor':'pointer'}); },
			function () { $(this).css('opacity','1.0'); }
		)
		.toggle(
			function(){
				$(this)
					.removeClass('expand')
						.children('span.mark').text('＋');
				$("div.other_plugin_box").hide();
			},
			function(){
				$(this)
					.addClass('expand')
						.children('span.mark').text('ー');
				$("div.other_plugin_box").show();
			}
		);

	var sublinks = $("ul.other_plugin_sub li");

	sublinks
		.hover(
			function(e){
				$(this).addClass('menuHover');
				$(this).css({'color':'#FFFFFF'});
			},
			function(e){
				$(this).removeClass('menuHover');
				$(this).css({'color':'#333333'});
			}
		)
		.click(function(e){
				var insert_cmd = $(this).children('span.insert_cmd').text();
				insert_cmd = insert_cmd.replace(/##LF##/g, '\n');
				$.clickpad.cpInsert(insert_cmd);
				return false;
		});

	// !File Upload
	if ($('#file_upload').length > 0) {
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

				var $swfulist = $("#swfulist"),
					$add = $('<span class="swfufile" style="padding:2px;margin-right:5px"></span>'),
					$files = $("span.swfufile", $swfulist);

				$add.append('<a href="./swfu/view.php?id='+file.id+'&page=FrontPage&KeepThis=true&TB_iframe=true&height=450&width=650" url="./swfu/d/" rel="attachref" class="thickbox" title="'+ file.name +'"><img src="image/file.png" width="20" height="20" alt="file" style="border-width:0" />'+ file.name +'</a>');
				var imglink = $("a", $add).get(0);
				var el = 'tooltip'+$files.length;
				var url = "./swfu/" + file.path;
				var title = '<img src="'+url+'" />';
				if ( file.name ) title += '<br />'+ file.name;
				var tp = new YAHOO.widget.Tooltip( el, { context:imglink, text: title, autodismissdelay: 7500 } );

				$add.append('<a href="#" title="'+ file.name +'を挿入"><img src="image/ins-img.png" alt="挿入" /></a><a href="#" title="'+ file.name +'を回り込み貼り付け"><img src="image/ins-img2.png" alt="挿入2" /></a>');
				$("a[href=#]", $add).each(function(i){
					var $$ = $(this);
					if (i == 0) {
						$$.click(function(){
							jQuery.clickpad.cpInsert("&show("+file.name+",,"+file.text+");");
							return false;
						});
					}
					else {
						$$.click(function(){
							jQuery.clickpad.cpInsert("\n#show("+file.name+",aroundl,"+file.text+")\n");
							return false;
						});
					}
				});

				$swfulist.append($add);

				return;
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
    }

    // !QBlog
    $("div.qblog_edit_form").each(function(){
    	var $$ = $(this);

    	if ($("#preview_body").length > 0)
    	{
	    	$$.addClass("well");
    	}

		$("ul.qblog_categories li", this).addClass("label label-default")
	    .click(function(){
		    $("input:text[name=category]", $$).val($(this).text());
		    $("#qblog_cat_trigger").click();
	    });
		$("ul.qblog_categories li:last-child", this).removeClass().css({display:"block",clear:"both"});

	   	$("span.swfu", this).click(function(){
		    toolbar_make_link($(this));
		    return false;
	    });

	    //postdata のタイトル行を消す
	    $("#msg, #original").val($("#msg").val().replace(/^TITLE:(.*)$/m, '').replace(/^\s+/g, ''));

	    // タイトルの指定がなければ、プレビュー、ページの更新はできないようにする
	    $$.on('submit', 'form', function(){
	    	if ($(this).data('clickedButton') != 'cancel') {
			    var $title = $('input:text[name="title"]',this);
			    if ($title.val().replace(/^\s+|\s+$/g, '').length == 0) {
				    alert('タイトルを指定してください');
				    $('input:submit', this).prop('disabled', false);
				    $title.focus().select();
				    return false;
				}
	    	}
	    });
    });
});
