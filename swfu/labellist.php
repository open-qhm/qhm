<?php
	require_once( "config.php" );
	require_once( "cheetan/cheetan.php" );
	
function action( &$c )
{
	set_menu($c);
	$c->set('_page_title','キーワード一覧');
	
	$imgs = $c->image->find();

	$labels = array();
	foreach($imgs as $k=>$v)
	{
		$tmp_arr = explode(',',$v['label']);
		foreach($tmp_arr as $l)
		{
			$l = trim($l);
			if(! isset($labels[$l]) )
				$labels[$l] = 0;
			$labels[$l]++;
		}
	}

	ksort($labels);
	
	//保存する
	if (count($labels) > 1 OR (count($labels) == 1 && ! isset($labels[''])))
	{
		$labels_ = $labels;
		unset($labels_['']);
		$c->admin->saveLabels('replace', array_keys($labels_));
	}
	
	$c->set('labels', $labels);
}

/* End of file labellist.php */
/* Location: ./labellist.php */