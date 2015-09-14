<?php
class CAdmin extends CModel
{
	var $table			= "data/admin.txt";
	var $validatefunc	= array(
							"name" => "notempty",
							"value" => "notempty"
							);
	var $validatemsg	= array(
							"name" => "正しく情報を入力して下さい<br />",
							"value" => "正しく情報を入力して下さい<br />"
							);

	function getConfig() {
		$config = array();
		$array = $this->find("", "name ASC");
		foreach($array as $key => $val) {
			$config[$val['name']] = $val['value'];
		}
		return $config;
	}

	/*list.phpで表示するページの個数を返す*/
	function getListNum(){
		$res = $this->findone('$name==="list_num"');
		return $res['value'];
	}

	function getListCols(){
		if($res = $this->findone('$name=="list_cols"'))
		{
			return $res['value'];
		}
		else
		{
			//add record
			$res = array(
				"name"=>'list_cols',
				"jname"=>'一覧表示の列数',
				"value"=>'3'
			);
			$this->insert($res);

			return $res['value'];
		}
	}

	/**
	 * APIキーを生成して保存する。
	 * APIキーは {key}.{timestamp} からなる
	 *
	 * @param int $length number of charactors of {key}
	 * @param int $timestamp timestamp of regenerating apikey
	 */
	function regenerateApiKey($length = 40, $timestamp = FALSE)
	{
		$seed = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$seedlen = strlen($seed);
		$apikey = '';
		$length = ($length > 0) ? $length : 40;
		for ($i = 0; $i < $length; $i++)
		{
			$apikey .= $seed{rand(0, $seedlen-1)};
		}

		$timestamp = ($timestamp === FALSE) ? time() : $timestamp;

		$apikey = "{$apikey}.{$timestamp}";

		//save
		$conf = $this->findoneby('name', 'apikey');
		if ($conf)
		{
			$conf['value'] = $apikey;
			$this->update($conf);
		}
		else
		{
			$data = array(
				'name' => 'apikey',
				'jname' => 'APIキー',
				'value' => $apikey
			);
			$this->insert($data);
		}

		return $apikey;
	}

	function apiKeyIsCorrect($apikey = '')
	{
		return FALSE;
	}

	/**
	 * ラベル一覧を返す
	 */
	function getLabels()
	{
		$conf = $this->findoneby('name', 'labels');
		if ($conf)
		{
			return explode($conf['value']);
		}
		else
		{
			return array();
		}
	}

	function saveLabels($mode = 'insert', $labels = array())
	{
		$conf = $this->findoneby('name', 'labels');
		if ($conf)
		{
			$conf['value'] = $value;
			$action = 'update';
		}
		else
		{
			$conf = array(
				'name' => 'labels',
				'jname' => 'ラベル一覧',
				'value' => ''
			);
			$action = 'insert';
		}

		//全て置き換える
		if ($mode === 'replace')
		{
			$labels = join(',', $labels);
		}
		else if ($mode === 'insert')
		{
			$cur_labels = explode(',', $conf['value']);
			$labels = array_merge($cur_labels, $labels);
			$labels = array_unique($labels);
			sort($labels);
			$labels = join(',', $labels);
		}
		else
		{
			return FALSE;
		}

		//変更がなければ保存しない
		if ($conf['value'] === $labels)
		{
			return FALSE;
		}
		$conf['value'] = $labels;

		$result = $this->$action($conf);
		return $result;

	}
}

/* End of file admin.php */
/* Location: ./models/admin.php */
