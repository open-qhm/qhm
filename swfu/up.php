<?php

require_once( "config.php" );
require_once( "cheetan/cheetan.php" );

function action( &$c )
{
	set_menu($c);
	$c->set('_page_title','トップ');
		
	$page_name = isset($_GET['page']) ? $_GET['page'] : '';
	$c->set('page_name', $page_name);
	
	$config = $c->admin->getConfig();
	if($config['overwrite']){
		$c->set('overwrite_msg','上書き保存');
		$overwrite = 1;
	}
	else{
		$c->set('overwrite_msg','自動で別名保存');	
		$overwrite = 0;
	}

	
	$_additional_head = <<<EOD
<link rel="stylesheet" type="text/css" href="css/swfu.css" />
<script type="text/javascript" src="swfupload/swfupload.js"></script>
<script type="text/javascript" src="js/swfupload.queue.js"></script>
<script type="text/javascript" src="js/fileprogress.js"></script>
<script type="text/javascript" src="js/handlers.js"></script>
<script type="text/javascript">
		var _ieVersion = (function(_doc, reg) {
		        return _doc.documentMode ? _doc.documentMode :
		        /*@cc_on!@*/false && _nav.userAgent.match(reg) ? RegExp.$1 * 1 : 0;
		    })(document, /MSIE\s([0-9]+[\.0-9]*)/),
		    _isMSIE = _ieVersion > 0 ? true : false;

		var swfu;

		window.onload = function() {
			var up2 = '../';

			if(_isMSIE){ //IE
				up2 = '';
			}

			var settings = {
				flash_url : "./swfupload/swfupload.swf",
				upload_url: up2+"upload.php",	// Relative to the SWF file
				post_params: {
					"PHPSESSID" : "{$ssid}",
					"PAGENAME"  : "{$page_name}",
					"OVERWRITE" : "{$overwrite}"
				},
				file_size_limit : "100 MB",
				file_types : "*.*",
				file_types_description : "All Files",
				file_upload_limit : 100,
				file_queue_limit : 0,
				custom_settings : {
					progressTarget : "fsUploadProgress",
					cancelButtonId : "btnCancel"
				},
				debug: false,

				// Button settings
				button_image_url: "images/TestImageNoText_65x29.png",	// Relative to the Flash file
				button_width: "65",
				button_height: "29",
				button_placeholder_id: "spanButtonPlaceHolder",
				button_text: '<span class="theFont">Click</span>',
				button_text_style: ".theFont { font-size: 16; }",
				button_text_left_padding: 11,
				button_text_top_padding: 5,
				
				// The event handler functions are defined in handlers.js
				file_queued_handler : fileQueued,
				file_queue_error_handler : fileQueueError,
				file_dialog_complete_handler : fileDialogComplete,
				upload_start_handler : uploadStart,
				upload_progress_handler : uploadProgress,
				upload_error_handler : uploadError,
				upload_success_handler : uploadSuccess,
				upload_complete_handler : uploadComplete,
				queue_complete_handler : queueComplete	// Queue plugin event
			};

			swfu = new SWFUpload(settings);
	     };
	</script>


EOD;
	$c->set('_additional_head', $_additional_head);
	
}

?>
