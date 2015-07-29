<?php
/**
 *   QHM Template Class
 *   -------------------------------------------
 *   qhm_template.php
 *
 *   Copyright (c) 2010 hokuken
 *   http://hokuken.com/
 *
 *   created  : 2010-09-09
 *   modified :
 *
 *   QHM Template を読み込むクラス
 *   表示に必要な変数などを保持する
 *   デザインパターンにおける、Singleton を採用している
 *
 *   Usage :
 *     $_context は変数名をキー、値を要素とする連想配列。
 *     $encode 漢字コード変換をする場合は、trueを指定
 *
 *
 *  @see http://anond.hatelabo.jp/20071030034313
 *    SixtyLinesTemplate.php - 60行しかないけどSmartyより速いテンプレートエンジン
 *
 *
 *   Developer Reference :
 *
 *    $this->values['logo_image'] --- logoimageプラグインによって使われる変数。qhm_init_main.phpの挙動を制御
 */

class QHM_Template {

	// Singleton Start: ------------------------------------------
	private static $instance;

	public static function get_instance() {
		if (isset( self::$instance )) {
			return self::$instance;
		} else {
			self::$instance = new QHM_Template();
			return self::$instance;
		}
	}
	// Singleton End: --------------------------------------------


	var $set_page = false;

	/** ページ名 */
	private $page;
	/** ページキャッシュファイルパス */
	private $tmpfile;
	/** ページキャッシュの情報が格納されたファイルパス */
	private $tmprfile;
	/** ページキャッシュの情報 .tmpr をパースしたもの */
	private $cacheinfo;

	/** 表示用連想配列（旧 $_qhm_values） */
	private $values;

	/** prepend_once などで使う、スイッチ配列 */
	private $appended;

	/** dynamic plugin list */
	private $dplugins;
	/** エンコードするかしないか？ */
	private $encode;

	/** キャッシュを有効にするかどうか */
	var $enable_cache;
	/** キャッシュを作成するかどうか */
	var $create_cache;
	/** キャッシュに関連するページのハッシュ配列 */
	private $cache_rel_pages;

	private function QHM_Template() {
		$this->values = array();
		$this->appended = array();
		$this->dplugins = array();
		$this->encode = false;
		$this->cache_rel_pages = array();
	}

	function set_page($page = '') {

		if ($page) {
			$this->page = $page;
			$this->tmpfile = CACHE_DIR. encode($page). '.tmp';
			$this->tmprfile = CACHE_DIR. encode($this->page). '.tmpr';
			$this->set_page = true;
		}

	}

	/**
	 *   テンプレートで置換するための変数をセットする
	 */
	function set_value($key = '', $value = '') {
		if ($key) {
			$this->values[$key] = $value;
			return true;
		}
		return false;
	}
	function setv($key = '', $value = '') {
		return $this->set_value($key, $value);
	}

	function setv_once($key, $value) {
		if ( $this->getv($key) ){
			return false;
		}
		else{ //まだセットされていない
			return $this->setv($key, $value);
		}
	}

    /** javascript 用データをセットする。セットする変数は QHM */
    function setjsv($key, $value = NULL)
    {
        return $this->set_js_value($key, $value);
    }

    function set_js_value($key, $value = NULL)
    {
        if (is_null($value))
        {
            if ( ! is_array($key))
            {
                $data = array($key);
            }
            else
            {
                $data = $key;
            }
        }
        else
        {
            $data = array(
                $key => $value
            );
        }

        $QHM = $this->getv('QHM');
        if ($QHM === FALSE) $QHM = array();
        $QHM = array_merge($QHM, $data);
        $this->setv('QHM', $QHM);
    }

	function get_value($key) {
		if (isset($this->values[$key])) {
			return $this->values[$key];
		} else {
			return false;
		}
	}
	function getv($key) {
		return $this->get_value($key);
	}

	function append_value($key = '', $append_value = '') {
		if ($val = $this->getv($key)) {
			$val .= $append_value;
		} else {
			$val = $append_value;
		}
		return $this->setv($key, $val);
	}
	function appendv($key = '', $append_value = '') {
		return $this->append_value($key, $append_value);
	}
	function appendv_once($hashkey, $valuekey = '', $append_value = '') {
		if ($this->is_appended($hashkey)) {
			return false;
		} else {
			$this->appended[$hashkey] = true;
			$this->appendv($valuekey, $append_value);
		}
	}
	function is_appended($hashkey) {
		return isset($this->appended[$hashkey]);
	}

	function prepend_value($key = '', $prepend_value = '') {
		if ($val = $this->getv($key)) {
			$val = $prepend_value . $val;
		} else {
			$val = $prepend_value;
		}
		return $this->setv($key, $val);
	}
	function prependv($key = '', $prepend_value = '') {
		return $this->prepend_value($key, $prepend_value);
	}
	function prependv_once($hashkey, $valuekey = '', $prepend_value = '') {
		if ($this->is_appended($hashkey)) {
			return false;
		} else {
			$this->appended[$hashkey] = true;
			$this->prependv($valuekey, $prepend_value);
		}
	}

	function get_values() {
		return $this->values;
	}

	// ! 特定の値のセットに特価したメソッド -----------------------------
	/**
	 * convert_html() した中で初めに出てきた画像のURLを保存する。
	 * 対応プラグインは show, slides, slideshow
	 *
	 * @param string $url URL of image
	 * @return boolean success OR already set | save failed
	 */
	function set_first_image($url)
	{
		if ( ! is_url($url, false, true))
		{
			return FALSE;
		}

		if ( ! $this->getv('first_image'))
		{
			$this->setv_once('first_image', $url);

			//管理者の場合、プレビュー用にfirst_image をセットする
			if (edit_auth($this->page, FALSE, FALSE)) {
				$this->setjsv('first_image', $url);
			}
		}
		return TRUE;
	}

	// ! -----------------------------

	/**
	 *   UTF-8以外の文字コードに変換するかしないか。
	 *
	 *   @param
	 *     $encode <boolean>: 変換するかしないか
	 */
	function set_encode($encode) {
		$this->encode = $encode;
		return true;
	}
	/**
	 *   テンプレートがなければ、テンプレートキャッシュを作って読み込む。
	 *   もし、キャッシュが有効なら、ページのキャッシュを生成。
	 *   キャッシュが存在している場合は、lib/pukiwiki.php で出力され、ここにはこない
	 *
	 *   @params
	 *     $filename
	 */
	function read($filename) {
	    $cachename = $this->convert($filename);
	    if ($this->encode) {
		    mb_convert_variables(TEMPLATE_ENCODE, CONTENT_CHARSET, $this->values);
	    }

		//JS variables を json へ変換
		if (isset($this->values['QHM']))
		{
			$js = '<script>
if (typeof QHM === "undefined") QHM = {};
QHM = '. json_encode($this->getv('QHM')) .';
</script>';
			$this->prependv('beforescript', $js);
		}

		extract($this->values);
		mb_internal_encoding(TEMPLATE_ENCODE);

		if($this->create_cache && $this->enable_cache && $this->page) //特定のプラグインで、無効にできる
		{
			//仮出力
			ob_start();
			include($cachename);
			$body = ob_get_contents();
			ob_end_clean();

			//キャッシュ生成と、取り出し
			$body = $this->get_page_out($body);
			echo $body;

		}
		else
		{
			//キャッシュでも実行できるプラグインを実行する
			$this->create_cache = false;
			$body = $this->replace_dynamic_plugin($body);
			include($cachename); //output & exit here.
		}
	}

	/*
	 *  filename を読み込み、convert_php() で置換してから
	 *  filename.cache に書き込む。読み書きのロックは省略。
	 *  (file_{get,put}_contents() はファイルロックできるようにすべきだ。)
	 */
	function convert($filename) {
	    $sep = $this->encode ? '_'. TEMPLATE_ENCODE : '';
	    $tcachename = CACHE_DIR. str_replace('/', '_', $filename). $sep.'.qtc';

	    if (! file_exists($tcachename) || filemtime($tcachename) < filemtime($filename)) {
	        $phpstr = $this->convert_php($filename);
			if ($this->encode) $phpstr = mb_convert_encoding($phpstr, TEMPLATE_ENCODE);
	        file_put_contents($tcachename, $phpstr);
	    }
	    return $tcachename;
	}


	/*
	 *   テンプレートの中身をPHPファイルに変更する。
	 *     - '#{...}' を 'echo ...;' に置換
	 *     - '%{...}' を 'echo htmlspecialchars(...);' に置換
	 *     - ついでにXML宣言も置換
	 */
	function convert_php($filename) {
	    $s = file_get_contents($filename);
	    $s = preg_replace('/#\{(.*?)\}/', '<?php echo $1; ?>', $s);
	    $s = preg_replace('/%\{(.*?)\}/', '<?php echo h($1); ?>', $s);
	    return $s;
	}

	/*
	 *   キャッシュファイルがそのまま使えるかどうかチェックする
	 *
	 *   全ファイルの最終更新日であるqhm_lastmod.dat、仕様変更のためにqhm_template.php、
	 *   デザイン変更に対応するために、pukiwiki.ini.php より新しいことを条件とする
	 *   もちろん、キャッシュファイルがなければ、false
	 *
	 *   旧来のneed_update_cache とは返値が逆になるので注意
	 */
	function cache_is_available()
	{
		if(! file_exists($this->tmpfile))
			return false;
		if(! file_exists($this->tmprfile))
			return false;

		$cache_mtime = filemtime($this->tmpfile);

		//最終更新ファイルと比較
		if( !file_exists(CACHE_DIR. QHM_LASTMOD) || $cache_mtime < filemtime(CACHE_DIR. QHM_LASTMOD) )
			return false;

		//バージョンアップされたら、とにかくキャッシュは再構築
		if( $cache_mtime < filemtime(LIB_DIR. 'init.php') )
			return false;

		//テンプレート構造
		if( $cache_mtime < filemtime(LIB_DIR. 'qhm_template.php') )
			return false;

		//pukiwiki.ini.phpと比較（デザイン変更に対応）
		if ( $cache_mtime < filemtime('qhm.ini.php') )
			return false;

		return true;
	}


	/*
	 *   キャッシュを生成し、保存し、出力できるHTMLコードを返却する
	 *
	 *   @params
	 *     $body <string>: キャッシュする情報
	 *   @return <string>: 動的プラグインを実行した出力できる結果
	 */
	function get_page_out($body) {
		//キャッシュを保存
		$this->save_page_cache($body);
		$this->save_page_cacheinfo();

		//キャッシュでも実行できるプラグインを実行する
		$this->create_cache = false;
		$out = $this->replace_dynamic_plugin($body);

		return $out;
	}

	function save_page_cache($body) {
		//コンテンツ部分のHTMLをキャッシュに出力
		file_put_contents($this->tmpfile, $body);
	}
	function save_page_cacheinfo() {
		global $pkwk_dtd;

		$tmpr = array();
		//キャッシュ更新に関係するページ名を出力[line: 0]
		$tmpr[] = join(',', $this->cache_rel_pages);

		//ダイナミックプラグインを登録[line: 1]
		$funcs_str = $this->get_dynamic_plugin_str();
		$tmpr[] = $funcs_str;

		//DTDと出力文字エンコード [line: 2]
		$tmpr[] = join(',', array($pkwk_dtd, CONTENT_CHARSET, TEMPLATE_ENCODE));

		file_put_contents($this->tmprfile, join("\n", $tmpr));
	}

	function set_rel_page($page) {
		$tmparr = array_flip($this->cache_rel_pages);
		if (!isset($tmparr[$page])) {
			$this->cache_rel_pages[] = $page;
			return true;
		} else {
			return false;
		}
	}
	function get_rel_pages() {
		return $this->cache_rel_pages;
	}

	/**
	 *   $this->tmpr を読み込み、
	 *   利用しやすい形に整形する
	 */
	function read_page_cacheinfo() {

		$lines = explode("\n", file_get_contents($this->tmprfile));

		$ret_arr = array();

		$ret_arr['pages'] = $lines[0];
		$ret_arr['funcs'] = $lines[1];
		$dplgarr = trim($ret_arr['funcs'])? explode('##SEP##', $ret_arr['funcs']): array();
		$len = count($dplgarr);
		for ($i = 0; $i < $len; $i += 2) {
			$this->dplugins[] = array(
				'plg' => $dplgarr[$i],
				'func' => $dplgarr[$i + 1]
			);
		}

		$tmp_arr = explode(',', $lines[2]);
		$ret_arr['pkwk_dtd'] = $tmp_arr[0];
		$ret_arr['c_charset'] = $tmp_arr[1];
		$ret_arr['tmpl_encode'] = $tmp_arr[2];

		$this->cacheinfo = $ret_arr;
	}

	function get_page_cacheinfo($key = '') {
		$qm = get_qm();
		if (!isset($this->cacheinfo)) {
			$this->read_page_cacheinfo();
		}

		if ($key) {
			if (isset($this->cacheinfo[$key])) {
				return $this->cacheinfo[$key];
			} else {
				die($qm->replace('qhm_template.err_no_cacheinfo', $key));
			}
		} else {
			return $this->cacheinfo;
		}
	}

	/**
	 *   動的プラグインを表すマーク（HTMLコメント形式）を返す
	 */
	function get_dynamic_plugin_mark($func_name, $args)
	{
		//関数名からプラグイン名を取得
		if (preg_match('/^plugin_(\w+?)_(?:convert|inline)$/', $func_name, $ms)) {
			$plg_name = $ms[1];
		} else {
			$qm = get_qm();
			die_message($qm->replace('fmt_err_noplgname', $func_name));
		}

		if(count($args)) {
			foreach ($args as $i => $arg) {
				$args[$i] = addcslashes($arg, "\\'");
			}
			$call_func = $func_name."('".implode("','",$args)."');";
		}
		else {
			$call_func = $func_name."();";
		}

		$this->dplugins[] = array(
			'plg'  => $plg_name,
			'func' => $call_func
		);

		return '<!-- #'. $call_func. '# -->';
	}


	function get_dynamic_plugin_list() {
		$ret_arr = array();
		foreach ($this->dplugins as $f) {
			$ret_arr[] = $f['plg'];
			$ret_arr[] = $f['func'];
		}

		return $ret_arr;
	}

	function get_dynamic_plugin_str(){

		$tmp_arr = $this->get_dynamic_plugin_list();

		return join('##SEP##', $tmp_arr);
	}

	/**
	 *
	 */
	function replace_dynamic_plugin($body)
	{

		$rel_funcs = array();
		$srcs = array();
		$rpls = array();

		//pluginの呼び出しが２回されてしまうので、無駄に２度のデータが入っている
		//そのことを吸収しつつ、プラグインを１度だけ実行するように気を付けるために、ややこしい
		$arr = $this->get_dynamic_plugin_list();
		$cnt = count($arr);

		//ダイナミックなプラグインがなければ、何もしない
		if($cnt < 2)
			return $body;

		for($i = 0; $i < $cnt; $i += 2)
		{
			$plg_name = $arr[$i];
			$fnc_name = $arr[$i+1];

			if(! isset($rel_funcs[ $plg_name ]) )
			{
				$rel_funcs[ $plg_name ] = array();
				require_once(PLUGIN_DIR. $plg_name. '.inc.php');
			}

			if(! in_array($fnc_name, $rel_funcs) )
			{
				$rel_funcs[ $plg_name ] = $fnc_name;
				$srcs[] = '<!-- #'.$fnc_name.'# -->';
				$rpls[] = eval('return '.$fnc_name);
			}
		}

		//文字コードを変換する
		$tmpr = $this->get_page_cacheinfo();
		mb_convert_variables($tmpr['tmpl_encode'], $tmpr['c_charset'], $rpls);

		return str_replace($srcs, $rpls, $body);
	}

	function disp()
	{
		//tmpr ファイルの読み込み
		$tmpr = $this->get_page_cacheinfo();

		// ダイナミックプラグインを実行する
		$body = $this->replace_dynamic_plugin(file_get_contents($this->tmpfile));

		// headerの出力
		qhm_output_dtd($tmpr['pkwk_dtd'], $tmpr['c_charset'], $tmpr['tmpl_encode']);

		//HTML出力
		echo $body;
	}



}


function get_qt() {
	return QHM_Template::get_instance();
}



?>
