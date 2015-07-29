<?php
/**
 *   QHM Message Class
 *   -------------------------------------------
 *   qhm_massage.php
 *   
 *   Copyright (c) 2010 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 2010-09-15
 *   modified :
 *   
 *   save system messages for localization
 *   
 *   Usage :
 *   
 */

class QHM_Message {

	// Singleton Start: ------------------------------------------
	private static $instance;
	
	public static function get_instance() {
		if (isset( self::$instance )) {
			return self::$instance;
		} else {
			self::$instance = new QHM_Message();
			return self::$instance;
		}
	}
	// Singleton End: --------------------------------------------
	
	//messages
	var $m;
	var $file;
	var $file_ja;
	var $cache;
	var $locales;
	
	private function QHM_Message() {
		$this->m = array();
		$this->file = 'lng.'. LANG. '.txt';
		$this->file_ja = 'lng.ja.txt';
		$this->cache = CACHE_DIR. '/lng.'. LANG. '.qmc';
		$this->readCache();
	}
	
	function replace() {
		$args = func_get_args();
		$name = array_shift($args);
		if (strpos($name, '.')) {
			list($section, $name) = explode('.', $name);
			$str = $this->m[$section][$name];
		} else {
			$str = $this->m[$name];
		}
		
		$srcs = array('$1', '$2', '$3', '$4', '$5');
		$args = array_pad($args, 5, '');
		
		return str_replace($srcs, $args, $str);
	}

	function readCache() {
		if ($this->checkCache()) {
			$this->m = unserialize(file_get_contents($this->cache));
			
		}
	}
	
	function checkCache() {
		//cache OK
		if (file_exists($this->cache) && (filemtime($this->cache) > filemtime($this->file))) {
			return true;
		}
		//make cache
		else {
			$this->buildCache();
			return false;
		}
		
	}
	function buildCache() {
	
		$ini = parse_ini_file($this->file, true);
		if (LANG != 'ja') {
			$ini_ja = parse_ini_file($this->file_ja, true);
			foreach ($ini_ja as $key => $value) {
				if (is_array($value)) {
					$ini[$key] = array_merge($value, $ini[$key]);
				} else {
					$ini[$key] = isset($ini[$key])? $ini[$key]: $value;
				}
			}
		}
		
		//&quot; を" へ変換する
		//##LF## を\n へ変換する
		$src = array('&quot;', '##LF##');
		$rpl = array('"', "\n");
		$ini = str_replace($src, $rpl, $ini);
		foreach ($ini as $section => $values) {
			if (is_array($values)) {
				$ini[$section] = str_replace($src, $rpl, $values);
			}
		}
		
		$this->m = $ini;
		
		//save cache
		$str = serialize($this->m);
		file_put_contents($this->cache, $str);
	
	}
	
}

function get_qm() {
	return QHM_Message::get_instance();
}




?>