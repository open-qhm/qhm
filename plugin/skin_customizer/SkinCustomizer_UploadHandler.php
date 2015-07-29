<?php
require(dirname(__FILE__).'/UploadHandler.php');

class SkinCustomizer_UploadHandler extends UploadHandler {
	
    function __construct($options = null, $initialize = true) {
    	parent::__construct($options, $initialize);
    }

    protected function get_full_url() {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        return
            ($https ? 'https://' : 'http://').
            (!empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
            (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
            ($https && $_SERVER['SERVER_PORT'] === 443 ||
            $_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
            substr(SCRIPT_NAME,0, strrpos(SCRIPT_NAME, '/'));
    }
	
    public function get_file_name($name, $type, $index, $content_range)
    {
        return $this->upload_dir.'custom_skin.'.$this->options['skin_name'].'.'.$this->options['param_name'].'.'.time().'.'.pathinfo($type, PATHINFO_EXTENSION);
    
/*         return parent::get_file_name($name, $type, $index, $content_range); */
    }

}
